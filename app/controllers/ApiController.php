<?php

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;


class ApiController extends BaseController
{

    public function test()
    {
        Event::fire('user.shown', array(Auth::user(), null, null));

    }

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

        //region Update location public IP
        \Illuminate\Support\Facades\DB::transaction(function () use ($location) {
            LocationIp::where('id', '=', $location->id)->delete();

            $locationIp = new LocationIp();
            $locationIp->id = $location->id;
            $locationIp->name = $_SERVER['REMOTE_ADDR'];
            $locationIp->save();
        });
        //endregion

        $json = json_decode(Request::getContent(), true);
        $macs = array();
        $emails = array();
        if (is_array($json)) {
            foreach ($json as $item) {
                if ($item['lastSeen'] > date('Y-m-d')) {
                    $macs[] = strtolower($item['mac']);
                    if (isset($item['email'])) {
                        $emails[] = strtolower($item['email']);
                    }
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

        $users = array();
        foreach (User::whereIn('email', $emails)->get() as $user) {
            $users[strtolower($user->email)] = $user;
        }
        // var_dump(array_keys($devices));
        $notified_users = array();
        $updated_users = array();

        $query = null;
        foreach ($json as $item) {
            if (!empty($item['mac']) && ($item['lastSeen'] > date('Y-m-d'))) {
                if (isset($devices[$item['mac']])) {
                    $device = $devices[$item['mac']];
                    if (null == $query) {
                        $query = DeviceSeen::where(function ($q) use ($device, $item) {
                            $q->where('device_id', '=', $device->id)
                                ->where('last_seen_at', '=', date('Y-m-d H:i:s', strtotime($item['lastSeen'])));
                        });
                    } else {
                        $query->orwhere(function ($q) use ($device, $item) {
                            $q->where('device_id', '=', $device->id)
                                ->where('last_seen_at', '=', date('Y-m-d H:i:s', strtotime($item['lastSeen'])));
                        });
                    }
                }
            }
        }

        $devices_seen = array();
        if ($query) {
            foreach ($query->get() as $ds) {
                $devices_seen[$ds->device_id] = $ds;
            }
        }

        foreach ($json as $item) {
            if (!empty($item['mac']) && ($item['lastSeen'] > date('Y-m-d'))) {
                $item['mac'] = strtolower($item['mac']);
                if (!isset($devices[$item['mac']])) {
                    // Create the device because it is connected to the WIFI
                    $device = new Device();
                    $device->mac = $item['mac'];
                    if (isset($item['name'])) {
                        $device->name = $item['name'];
                    }
                    if (isset($item['brand']) && ($item['brand'] !== 'Unknown')) {
                        $device->brand = $item['brand'];
                    }
                    if (isset($item['ip'])) {
                        $device->ip = $item['ip'];
                    }
                    if (isset($item['email'])) {
                        $item['email'] = strtolower($item['email']);
                        if (isset($users[$item['email']])) {
                            $device->user_id = $users[$item['email']]->id;
                        } else {
                            // user has been logged but do not exist in the database, Voucher?
                        }
                    }
                    $device->save();
                    $devices[$item['mac']] = $device;
                }
                if (isset($devices[$item['mac']])) {
                    $device = $devices[$item['mac']];
                    if (isset($item['email'])/* && empty($device->user_id)*/) {
                        $item['email'] = strtolower($item['email']);
                        if (isset($users[$item['email']])) {
                            $device->user_id = $users[$item['email']]->id;
                            $device->save();
                        }
                    }
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
                        $device_seen = isset($devices_seen[$device->id]) ? $devices_seen[$device->id] : null;
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
            } else {
                $user = new User();
                if (Input::get('firstname') && empty($user->firstname)) {
                    $user->firstname = Input::get('firstname');
                }
                if (Input::get('lastname') && empty($user->lastname)) {
                    $user->lastname = Input::get('lastname');
                }
                $user->populateFromEmail($email);
                $user->password = Hash::make(Config::get('etincelle.default_user_password'));
                $user->save();
            }
        }
        $result = new Response();
        $result->headers->set('Content-Type', 'application/json');
        $result->setContent(json_encode($data));
        return $result;
    }

    public function invoice($reference)
    {
        $data = array();
        if (preg_match('/^F([0-9]{6})-([0-9]{4})/', $reference, $tokens)) {
            $invoice = Invoice::where('type', 'F')
                ->where('days', $tokens[1])
                ->where('number', $tokens[2])
                ->with('items')
                ->first();
            $data = $this->formatJson($invoice);
        }
        $result = new Response();
        $result->headers->set('Content-Type', 'application/json');
        $result->setContent(json_encode($data));
        return $result;
    }

    protected function formatJson($invoice)
    {
        return array(
            'reference' => $invoice->getIdentAttribute(),
            'amount' => (float)$invoice->getTotalWithTaxesAttribute(),
            'taxes' => $invoice->getTotalWithTaxesAttribute() - $invoice->getTotalAttribute(),
//            'created_at' => $invoice->date_invoice,
            'occurs_at' => $invoice->date_invoice,
            'paid_at' => $invoice->date_payment,
            'update_url' => route('invoice_modify', $invoice->id),
            'pdf_url' => route('invoice_print_pdf', $invoice->id),
            'customer' =>
                $invoice->organisation_id ? $this->getOrganisationJson($invoice->organisation, $invoice->user) : $this->getOrganisationJsonFromInvoice($invoice),
            'lines' => $this->formatInvoiceLines($invoice->items)
        );
    }

    protected function formatInvoiceLines($items)
    {
        $result = [];
        foreach ($items as $item) {
            $result[] = [
                'product_id' => $item->ressource_id,
                'caption' => $item->text,
                'amount' => (float)$item->amount,
                'taxes' => $item->vat_types_id ? $item->vat->value / 100 * $item->amount : 0
            ];
        }
        return $result;
    }

    public function invoices()
    {
        $data = array();
        $invoices = Invoice::where('type', 'F')
            ->orderBy('date_invoice', 'DESC');
        $customer_id = Input::get('customer_id');
        if ($customer_id) {
            $invoices = $invoices->where('organisation_id', $customer_id);
        }
        $operators = array('lt' => '<', 'lte' => '<=', 'eq' => '=', 'gt' => '>', 'gte' => '>=');
        $date_invoice = Input::get('date_invoice');
        if (is_array($date_invoice)) {
            foreach ($operators as $op1 => $op2) {
                if (isset($date_invoice[$op1])) {
                    if ('eq' == $op1 && empty($date_invoice[$op1])) {
                        $invoices = $invoices->whereNull('date_invoice');
                    } else {
                        $invoices = $invoices->where('date_invoice', $op2, $date_invoice[$op1]);
                    }
                }
            }
        }
        $date_payment = Input::get('date_payment');
        if (is_array($date_payment)) {
            foreach ($operators as $op1 => $op2) {
                if (isset($date_payment[$op1])) {
                    if (('eq' == $op1) && empty($date_payment[$op1])) {
                        $invoices = $invoices->whereNull('date_payment');
                    } else {
                        $invoices = $invoices->where('date_payment', $op2, $date_payment[$op1]);
                    }
                }
            }
        }
        $references = Input::get('reference');
        if (is_array($references)) {
            $invoices = $invoices->where(function ($query) use ($references) {
                foreach ($references as $reference) {
                    if (preg_match('/^F(\d{6})-(\d{4})$/', $reference, $tokens)) {
                        $query->orWhere(function ($query) use ($tokens) {
                            $query->where('days', $tokens[1]);
                            $query->where('number', (int)$tokens[2]);
                        });
                    }
                }
            });
        }
        $amount = Input::get('amount');
        if (is_array($amount)) {
            $invoices = $invoices
                ->join('invoices_items', 'invoices.id', '=', 'invoices_items.invoice_id')
                ->join('vat_types', 'vat_types.id', '=', 'invoices_items.vat_types_id')
                ->groupby('invoices.id')
                ->select('invoices.*');
            $field = DB::raw('sum(invoices_items.amount * (1 + vat_types.value / 100))');
            foreach ($operators as $op1 => $op2) {
                if (isset($amount[$op1])) {
                    $invoices = $invoices->having($field, $op2, $amount[$op1]);
                }
            }
        }
        $invoices = $invoices
            ->with('items')
            ->with('organisation')
            ->with('organisation.country')
            ->with('organisation.accountant')
            ->get();
        foreach ($invoices as $invoice) {
            $data[] = $this->formatJson($invoice);
        }
        $result = new Response();
        $result->headers->set('Content-Type', 'application/json');
        $result->setContent(json_encode($data));
        return $result;
    }


    public function customers()
    {
        $data = array(
            'results' => array(),
            'pagination' => array(
                'more' => false
            ),
        );

        foreach (Organisation::orderBy('name', 'ASC')
                     ->where('name', 'LIKE', sprintf('%%%s%%', Input::get('q')))
                     ->take(10)
                     ->skip(Input::get('page', 1) - 1)
                     ->get() as $organisation) {
            $data['results'][] = array(
                'id' => $organisation->id,
                'text' => $organisation->name,
            );
        }

        $result = new Response();
        $result->headers->set('Content-Type', 'application/json');
        $result->headers->set('Access-Control-Allow-Origin', '*');
        $result->headers->set('Access-Control-Allow-Methods', 'GET');
        $result->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, X-Auth-Token');
        $result->setContent(json_encode($data));
        return $result;
    }

    protected function getOrganisationJson($organisation, $user = null)
    {
        $address = explode("\n", $organisation->address);
        foreach ($address as $index => $line) {
            $address[$index] = trim($line);
        }
        $line1 = array_shift($address);
        $line2 = implode("\n", $address);

        return array(
            'id' => $organisation->id,
            'name' => $organisation->name,
            'address_line1' => $line1,
            'address_line2' => $line2,
            'address_postalcode' => $organisation->zipcode,
            'address_city' => $organisation->city,
            'address_country' => $organisation->country->name,
            'contact_name' => $organisation->accountant ? $organisation->accountant->fullname : ($user ? $user->fullname : ''),
            'contact_email' => $organisation->accountant ? $organisation->accountant->email : ($user ? $user->email : ''),
        );
    }

    protected function getOrganisationJsonFromInvoice($invoice)
    {
        $address = explode("\n", $invoice->address);
        foreach ($address as $index => $line) {
            $address[$index] = trim($line);
        }

        return array(
            'id' => null,
            'name' => array_shift($address),
            'address_line1' => array_shift($address),
            'address_line2' => array_shift($address),
            'address_postalcode' => null,
            'address_city' => array_shift($address),
            'address_country' => null,
            'contact_name' => $invoice->user_id ? $invoice->user->fullname : '',
            'contact_email' => $invoice->user_id ? $invoice->user->email : '',
        );
    }

    public function customer($id)
    {
        $organisation = Organisation::find($id);

        $result = new Response();
        $result->headers->set('Content-Type', 'application/json');
        //$result->headers->set('Access-Control-Allow-Origin', '*');
        $result->setContent(json_encode($this->getOrganisationJson($organisation, null)));
        return $result;
    }

    public function products()
    {
        $items = Ressource::with('kind')->with('location')->orderBy('order_index', 'ASC')->get();
        $data = [];
        foreach ($items as $item) {
            $data[$item->kind->name][$item->id] = $item->fullname;
        }

        $result = new Response();
        $result->headers->set('Content-Type', 'application/json');
        //$result->headers->set('Access-Control-Allow-Origin', '*');
        $result->setContent(json_encode($data));
        return $result;
    }

    public function phonebox()
    {
        if (Request::get('api_key') != $_ENV['PHONEBOX_API_KEY']) {
            $result = new Response();
            $result->headers->set('Content-Type', 'application/json');
            $result->setContent(json_encode(['status' => 'error']));
            return $result;
        }

        $user = User::where('personnal_code', Request::get('code'))->first();
        if (!$user) {
            $result = new Response();
            $result->headers->set('Content-Type', 'application/json');
            $result->setContent(json_encode(['status' => 'error']));
            return $result;
        }

        $result = new Response();
        $result->headers->set('Content-Type', 'application/json');
        $result->setContent(json_encode([
            'status' => 'success',
            'user' => [
                'id' => $user->id,
                'name' => $user->fullname,
                'profile_url' => $user->getAvatarUrl(300),
                'phone' => CronRunCommand::getPhoneNumberFormattedForSms($user->phone)
            ]]));
        return $result;
    }

    public function phonebox_pick()
    {
        $redirect = Request::get('redirect');
        if (Auth::id()) {
            $ch = curl_init();

            curl_setopt($ch, CURLOPT_URL, $redirect);
            curl_setopt($ch, CURLOPT_POST, 1);

            $user = Auth::user();
            curl_setopt($ch, CURLOPT_POSTFIELDS,
                http_build_query(array(
                    'api_key' => $_ENV['PHONEBOX_API_KEY'],
                    'user_id' => $user->id,
                    'user_name' => $user->fullname,
                    'user_picture' => $user->getAvatarUrl(300),
                    'user_phone' => CronRunCommand::getPhoneNumberFormattedForSms($user->phone)
                )));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $response_content = curl_exec($ch);
            curl_close($ch);

            return new Response((string)$response_content);
        }

        Session::put('url.intended', $redirect);
        return Redirect::route('user_login');
    }

    public function make_invoice()
    {
        $json = json_decode(Request::getContent());

        //dump($json);

        $user_id = $json->user_id;
        $user = User::findOrFail($user_id);

        $caption = $user->fullname;
        $organisation_id = $json->organisation_id;
        if ($organisation_id) {
            $organisation = Organisation::find($organisation_id);
        } else {
            $organisation = null;
        }

        $invoice = new Invoice();
        $invoice->type = 'F';
        $invoice->user_id = $user_id;
        $invoice->days = date('Ym');
        $invoice->date_invoice = date('Y-m-d');
        $invoice->number = Invoice::next_invoice_number($invoice->type, $invoice->days);
        if ($organisation) {
            $invoice->organisation_id = $organisation_id;
        } else {
            $organisation = Organisation::where('name', '=', $user->fullname)->first();
            if (!$organisation) {
                $organisation = new Organisation();
                $organisation->name = $user->fullname;
                $organisation->country_id = Country::where('name', '=', 'France')->first()->id;
                $organisation->save();

                $link = new OrganisationUser();
                $link->organisation_id = $organisation->id;
                $link->user_id = $user->id;
                $link->save();
            }

            $invoice->organisation_id = $organisation->id;

        }
        $invoice->address = $organisation->fulladdress;

        $date = new DateTime($invoice->date_invoice);
        $date->modify('+1 month');
        $invoice->deadline = $date->format('Y-m-d');
        $invoice->expected_payment_at = $invoice->deadline;
        $invoice->save();

        $line_index = 1;
        foreach ($json->items as $item) {
            $invoice_line = new InvoiceItem();
            $invoice_line->invoice_id = $invoice->id;
            $invoice_line->order_index = $line_index++;
            $invoice_line->vat_types_id = VatType::where('value', $item->tax_rate)->first()->id;
            $invoice_line->text = $item->description;
            $invoice_line->amount = $item->amount;
            if (empty($item->user_id) && ($item->tax_rate == 0) && ($item->kind !== 'coworking')) {
                $invoice_line->ressource_id = Ressource::TYPE_DEPOSIT;
            } else {
                $invoice_line->ressource_id = Ressource::TYPE_COWORKING;
                if ($item->user_id) {
                    $invoice_line->subscription_user_id = $item->user_id;
                }
                if ($item->start_at) {
                    $invoice_line->subscription_from = $item->start_at;
                }
                if ($item->ends_at) {
                    $invoice_line->subscription_to = $item->ends_at;
                }

                switch ($item->kind) {
                    case 'coworking.v2021.opale':
                        $invoice_line->subscription_hours_quota = 0;
                        break;
                    case 'coworking.v2021.saphir':
                        $invoice_line->subscription_hours_quota = 40;
                        break;
                    case 'coworking.v2021.rubis':
                        $invoice_line->subscription_hours_quota = 80;
                        break;
                    case 'coworking.v2021.diamant':
                        $invoice_line->subscription_hours_quota = 80;
                        break;
                    case 'coworking.v2021.unlimited':
                    case 'coworking.v2021.reserved':
                        $invoice_line->subscription_hours_quota = -1;
                        break;
                }
            }

            $invoice_line->save();
            //$invoice_lines[] = $invoice_line;
        }

        $result = array(
            'status' => 'success',
            'data' => array(
                'id' => $invoice->id,
                'reference' => $invoice->ident,
                'modify_url' => URL::route('invoice_modify', array('id' => $invoice->id)),
                'pdf_url' => URL::route('invoice_print_pdf', array('id' => $invoice->id))
            )
        );

        $response = new \Illuminate\Http\Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET');
        $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, X-Auth-Token');
        $response->setContent(json_encode($result));
        return $response;
    }
}