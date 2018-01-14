<?php

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;

class ApiController extends BaseController
{
    public function updateLocationIp($location_slug, $key)
    {
        $location = Location::where('slug', '=', $location_slug)
            ->where('key', '=', $key)
            ->firstOrFail();

        LocationIp::where('id', '=', $location->id)->delete();

        $locationIp = new LocationIp();
        $locationIp->id = $location->id;
        $locationIp->name = $_SERVER['REMOTE_ADDR'];
        $locationIp->save();

        if (Request::ajax()) {
            return new Response('OK');
        }
        return Redirect::route('dashboard');
    }


    public function updateMetric($location_slug, $key, $metric_slug, $metric_value)
    {
        $location = Location::where('slug', '=', $location_slug)
            ->where('key', '=', $key)
            ->firstOrFail();

        $metric = Metric::where('slug', '=', $metric_slug)
            ->firstOrFail();

        $data = new MetricValue();
        $data->location_id = $location->id;
        $data->metric_id = $metric->id;
        $data->value = $metric_value;
        $data->save();

        if (Request::ajax()) {
            return new Response('OK');
        }
        return Redirect::route('dashboard');
    }

    protected function floorTime($value)
    {
        return floor(strtotime($value) / (5 * 60)) * 5 * 60;
    }

    protected function ceilTime($value)
    {
        return ceil(strtotime($value) / (5 * 60)) * 5 * 60;
    }


    public function offixUpload($location_slug, $key)
    {
        $location = Location::where('slug', '=', $location_slug)
            ->where('key', '=', $key)
            ->firstOrFail();

        $json = json_decode(Request::getContent(), true);
        $macs = array();
        if (is_array($json)) {
            foreach ($json as $item) {
                if ($item['lastSeen'] > date('Y-m-d')) {
                    $macs[] = strtolower($item['mac']);
                }
            }
        } else {
            error_log(sprintf('offixUpload: JSON is not an array [%s]', Request::getContent()), E_USER_WARNING);
        }
        //var_dump($macs);
        $devices = array();
        foreach (Device::whereIn('mac', $macs)->get() as $device) {
            $devices[$device->mac] = $device;
        }
        // var_dump(array_keys($devices));
        $notified_users = array();
        $updated_users = array();

        foreach ($json as $item) {
            $item['mac'] = strtolower($item['mac']);
            if (!isset($devices[$item['mac']])) {
                if (isset($item['name'])) {
                    // Create the device because it is connected to the WIFI
                    $device = new Device();
                    $device->mac = $item['mac'];
                    if (isset($item['name'])) {
                        $device->name = $item['name'];
                    }
                    if (isset($item['brand']) && ($item['brand'] != 'Unknown')) {
                        $device->brand = $item['brand'];
                    }
                    if (isset($item['ip'])) {
                        $device->ip = $item['ip'];
                    }
                    $device->save();
                    $devices[$item['mac']] = $device;
                }
            }
            if (isset($devices[$item['mac']])) {
                $device = $devices[$item['mac']];
                if ($device->tracking_enabled) {
                    if (!isset($updated_users[(int)$device->user_id])) {
                        $updated_users[(int)$device->user_id] = true;
                        $timeslot = PastTime::where('user_id', '=', (int)$device->user_id)
                            ->where('date_past', '=', date('Y-m-d', strtotime($item['lastSeen'])))
                            ->where('time_start', '<', date('Y-m-d H:i:s', strtotime($item['lastSeen'])))
                            ->where('location_id', '=', $location->id)
                            ->where(function ($query) use ($item) {
                                $query->where('time_end', '>', date('Y-m-d H:i:s', strtotime('-60 minutes', strtotime($item['lastSeen']))))
                                    ->orWhereNull('time_end');
                            })
                            ->orderBy('time_start', 'DESC')
                            ->first();
                        $triggerUserShown = !$timeslot;
                        if (!$timeslot) {
                            $timeslot = new PastTime();
                            $timeslot->user_id = $device->user_id ? $device->user_id : 0;
                            $timeslot->ressource_id = Ressource::TYPE_COWORKING;
                            $timeslot->location_id = $location->id;
                            $timeslot->date_past = date('Y-m-d');
                            $timeslot->time_start = date('Y-m-d H:i:s', $this->floorTime($item['lastSeen']));
                        }
                        $timeslot->device_id = $device->id;
                        $date_end = date('Y-m-d H:i:s', $this->ceilTime($item['lastSeen']) + 10 * 60);
                        if ($timeslot->time_end < $date_end) {
                            $timeslot->time_end = $date_end;
                        }
                        $timeslot->save();

                        if ($timeslot->user_id && $triggerUserShown && !isset($notified_users[$timeslot->user_id])) {
                            $notified_users[$timeslot->user_id] = true;
                            Log::info(sprintf('%s est là', $timeslot->user->fullname), array('context' => 'user.shown'));
                            Event::fire('user.shown', array($timeslot->user, $timeslot, $location));
                        }
                    }
                    $device_seen = DeviceSeen::where('device_id', '=', $device->id)
                        ->where('last_seen_at', '=', date('Y-m-d H:i:s', strtotime($item['lastSeen'])))
                        ->first();
                    if (!$device_seen) {
                        $device_seen = new DeviceSeen();
                        $device_seen->device_id = $device->id;
                        $device_seen->location_id = $location->id;
                        $device_seen->last_seen_at = date('Y-m-d H:i:s', strtotime($item['lastSeen']));
                        $device_seen->save();
                    }
                }
                $device->last_seen_at = max($device->last_seen_at, date('Y-m-d H:i:s', strtotime($item['lastSeen'])));
                if (isset($item['name'])) {
                    $device->name = $item['name'];
                }
                if (isset($item['brand']) && ($item['brand'] != 'Unknown')) {
                    $device->brand = $item['brand'];
                }
                if (isset($item['ip'])) {
                    $device->ip = $item['ip'];
                }

                $device->save();
                if ($device->user_id) {
                    $user = $device->user;
                    $user->last_seen_at = date('Y-m-d H:i:s', strtotime($item['lastSeen']));
                    $user->save();
                }
            }
        }

        return new Response('OK');
    }

    public function offixDownload($secure_key)
    {
        if ($secure_key != $_ENV['key_secure']) {
            return new Response('Access denied', 403);
        }
        $result = array();
        foreach (Device::with('user')->orderBy('user_id', 'ASC')->get() as $device) {
            /** @var User $user */
            $user = $device->user;
            if ($user) {
                $result[$user->id]['isAdmin'] = $user->role == 'superadmin';
                $result[$user->id]['lastSeen'] = null;
                if (!isset($result[$user->id]['macAddresses'])) {
                    $result[$user->id]['macAddresses'] = array();
                }
                $result[$user->id]['macAddresses'][] = $device->mac;
                $result[$user->id]['password'] = '$2a$10$C.6rBMZm4viJ2q.ia1xbZudXb4PMPvfnfE3GsXQH4DwZc62nbNpT2';
                $result[$user->id]['realName'] = $user->fullname;
                $result[$user->id]['shouldBroadcast'] = true;
                $result[$user->id]['username'] = $user->email;
            }
        }
        $mongoCode = 'db.users.remove‌​({});' . "\n";
        foreach ($result as $user) {
            $mongoCode .= sprintf('db.users.insert(%s);', json_encode($user)) . "\n";
        }
        return new Response($mongoCode);
    }

    public function user($secure_key, $email)
    {
        if ((Config::get('etincelle.api_secret') == '') || ($secure_key != Config::get('etincelle.api_secret'))) {
            App::abort(403, 'Unauthorized action.');
        }

        $data = array('email' => $email);

        if (!empty($email)) {
            $user = User::where('email', strtolower($email))->first();
            if ($user) {
                $data['id'] = $user->id;
                $data['firstname'] = $user->firstname;
                $data['lastname'] = $user->lastname;
                $data['fullname'] = implode(' ', array($user->firstname, $user->lastname));
                $data['birthday'] = $user->birthday;
                $data['location'] = (string)$user->location;
                $data['phone'] = $user->phoneFmt;
                $data['organisations'] = array();
                foreach ($user->organisations as $organisation) {
                    $data['organisations'][] = array(
                        'id' => $organisation->id,
                        'address' => implode("\n", array($organisation->name, $organisation->address, implode(' ', array($organisation->zipcode, $organisation->city)))),
                        'name' => $organisation->name,
                        'street' => $organisation->address,
                        'zipcode' => $organisation->zipcode,
                        'city' => $organisation->city,
                        'domiciliation' => ($organisation->domiciliation_start_at != null)
                            && (($organisation->domiciliation_end_at == null) || ($organisation->domiciliation_end_at > date('Y-m-d'))),
                        'domiciliation_start_at' => $organisation->domiciliation_start_at,
                        'domiciliation_end_at' => $organisation->domiciliation_end_at,
                    );
                }

                $data['due'] = 0;
                $data['invoices'] = array();
                foreach (Invoice::InvoiceOnly()->invoicesDesc($user) as $invoice) {
                    $item = array(
                        'id' => $invoice->id,
                        'reference' => $invoice->ident,
                        'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                        'paid_at' => is_null($invoice->date_payment) ? null : $invoice->date_payment,
                        'raw_amount' => Invoice::TotalInvoice($invoice->items),
                        'amount' => Invoice::TotalInvoiceWithTaxes($invoice->items),
                        'url_edit' => URL::route('invoice_modify', $invoice->id),
                        'url_pdf' => URL::route('invoice_print_pdf', $invoice->id),
                    );
                    $data['invoices'][] = $item;
                    if (null == $invoice->date_payment) {
                        $data['due'] += $item['amount'];
                    }
                }

                $data['quotes'] = array();
                foreach (Invoice::QuoteOnly('valid')->invoicesDesc($user) as $invoice) {
                    $data['quotes'][] = array(
                        'id' => $invoice->id,
                        'reference' => $invoice->ident,
                        'created_at' => $invoice->created_at->format('Y-m-d H:i:s'),
                        'raw_amount' => Invoice::TotalInvoice($invoice->items),
                        'amount' => Invoice::TotalInvoiceWithTaxes($invoice->items),
                        'url_edit' => URL::route('invoice_modify', $invoice->id),
                        'url_pdf' => URL::route('invoice_print_pdf', $invoice->id),
                    );
                }


                $data['bookings'] = array();
                $map = array(
                    'past' => 'booking_item.start_at BETWEEN DATE_SUB(now(), INTERVAL 3 MONTH) AND now()',
                    'upcoming' => 'booking_item.start_at > now()',
                );
                foreach ($map as $key => $criteria) {
                    $data['bookings'][$key] = array();
                    $bookings = DB::select(DB::raw('SELECT booking_item.id, ressources.name as ressource, booking.title, booking_item.start_at, booking_item.duration, DATE_ADD(booking_item.start_at, INTERVAL booking_item.duration MINUTE) as end_at
                      , concat(cities.name, " > ", IF(locations.name IS NULL, "", locations.name)) as location
                    FROM booking 
                      JOIN booking_item ON booking.id = booking_item.booking_id
                      JOIN ressources ON ressources.id = booking_item.ressource_id
                      JOIN locations ON locations.id = ressources.location_id
                      JOIN cities ON cities.id = locations.city_id
                    WHERE booking.user_id = ' . $user->id . ' AND ' . $criteria . '
                    GROUP BY booking.id
                    ORDER BY booking_item.start_at ASC, booking_item.duration DESC 
                    '));
                    foreach ($bookings as $booking) {
                        $booking->duration = durationToHuman($booking->duration);
                        $data['bookings'][$key][] = $booking;
                    }
                }

                //region subscription
                $data['subscription'] = array();
                $subscription = InvoiceItem::where('subscription_from', '<>', '0000-00-00 00:00:00')
                    ->where('subscription_user_id', $user->id)
                    ->orderBy('subscription_to', 'DESC')
                    ->select('subscription_from', 'subscription_to', 'subscription_hours_quota', 'invoice_id')
                    ->first();
                if ($subscription) {
                    $data['subscription']['active'] = true;
                    $data['subscription']['invoice_id'] = $subscription['invoice_id'];
                    $data['subscription']['from'] = $subscription['subscription_from'];
                    $data['subscription']['to'] = $subscription['subscription_to'];
                    $data['subscription']['quota'] = $subscription['subscription_hours_quota'];
                    $data['subscription']['used'] = durationToHuman($user->getCoworkingTimeSpent($subscription['subscription_from'], $subscription['subscription_to']));
                    if ($data['subscription']['quota'] >= 0) {
                        $data['subscription']['ratio'] = round(100 * $data['subscription']['used'] / $data['subscription']['quota']);
                        $data['subscription']['quota'] = sprintf('%d heures', $data['subscription']['quota']);
                    } else {
                        $data['subscription']['ratio'] = 0;
                        $data['subscription']['quota'] = 'illimité';
                    }
                    $data['subscription']['status'] = sprintf('%s / %s', $data['subscription']['used'], $data['subscription']['quota']);
                } else {
                    $data['subscription']['active'] = false;
                }
                //endregion
            }else{
                $user = new User();
                $user->email = $email;
                $user->firstname = Input::get('firstname');
                $user->lastname = Input::get('lastname');
                $user->password = Hash::make(Config::get('etincelle.default_user_password'));
                $user->save();
            }
        }
        $result = new Response();
        $result->headers->set('Content-Type', 'application/json');
        $result->setContent(json_encode($data));
        return $result;
    }
}
