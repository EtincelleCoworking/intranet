<?php


use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;

class BookingApiController extends Controller
{
    public function members($booking_item_id)
    {
        $result = array();
        $members = array();
        $users = User::join('booking_item_user', 'users.id', '=', 'booking_item_user.users_id')
            ->join('booking_item', 'booking_item_user.booking_item_id', '=', 'booking_item.id')
            ->join('booking', 'booking_item.booking_id', '=', 'booking.id')
            ->where('booking_item.id', '=', $booking_item_id)
            ->orderBy('booking_item_user.created_at')
            ->get(array('users.*'));
        foreach ($users as $user) {
            $members[] = $user->id;
            $result[] = $this->getMember($user);
        }
//        $queries = DB::getQueryLog();
//        echo '<pre>';
//        print_r($queries); exit;
        return Response::json(array('is_member' => in_array(Auth::id(), $members), 'members' => $result));
    }

    protected function getMember($user)
    {
        return array(
            'id' => $user->id,
            'fullname' => $user->fullname,
            'profile_url' => URL::route('user_profile', $user->id),
            'avatar_url' => $user->getAvatarUrl(48)
        );
    }

    public function register($booking_item_id, $user_id = null)
    {
        if (empty($user_id)) {
            $user_id = Auth::id();
        }
        if (!Auth::user()->isSuperAdmin() && (Auth::id() != $user_id)) {
            App::abort(403);
        }
        $item = new BookingItemUser();
        $item->users_id = $user_id;
        $item->booking_item_id = $booking_item_id;
        $item->save();

        $user = User::find($user_id);
        $booking_item = BookingItem::find($booking_item_id);
        Mail::send('booking::emails.register', array('booking_item' => $booking_item, 'user' => $user), function ($m) use ($user, $booking_item) {
            $m->from($_ENV['mail_address'], $_ENV['mail_name'])
                ->bcc($_ENV['mail_bcc'])
                ->to($booking_item->booking->user->email, $booking_item->booking->user->fullname)
                ->subject(sprintf('%s - Inscription - %s', $_ENV['organisation_name'], $booking_item->booking->title));
        });
        if (Request::ajax()) {
            return Response::json(array('status' => 'OK', 'member' => $this->getMember(User::find($user_id))));
        }
        return Redirect::route('booking_item_show', array('id' => $booking_item_id));
    }

    public function unregister($booking_item_id, $user_id = null)
    {
        if (empty($user_id)) {
            $user_id = Auth::id();
        }
        if (!Auth::user()->isSuperAdmin() && (Auth::id() != $user_id)) {
            App::abort(403);
        }
        BookingItemUser::where('users_id', '=', $user_id)
            ->where('booking_item_id', '=', $booking_item_id)
            ->delete();

        $user = User::find($user_id);
        $booking_item = BookingItem::find($booking_item_id);
        Mail::send('booking::emails.unregister', array('booking_item' => $booking_item, 'user' => $user), function ($m) use ($user, $booking_item) {
            $m->from($_ENV['mail_address'], $_ENV['mail_name'])
                ->bcc($_ENV['mail_bcc'])
                ->to($booking_item->booking->user->email, $booking_item->booking->user->fullname)
                ->subject(sprintf('%s - Désinscription - %s', $_ENV['organisation_name'], $booking_item->booking->title));
        });

        if (Request::ajax()) {
            return Response::json(array('status' => 'OK', 'user_id' => $user_id));
        }
        return Redirect::route('booking_item_show', array('id' => $booking_item_id));
    }

    public function intercom($location_slug, $key)
    {
        $result = DB::selectOne(DB::raw(str_replace(
            array(':location_slug', ':key'), array($location_slug, $key),
            'SELECT COUNT(booking_item.id) as cnt
          FROM booking_item
            JOIN ressources on ressources.id = booking_item.ressource_id
            JOIN locations on locations.id = ressources.location_id
          WHERE locations.slug = ":location_slug"
            AND locations.key = ":key"
            AND ressources.intercom_enabled = true
            AND DATE_SUB(booking_item.start_at, INTERVAL 15 MINUTE) < now()
            AND DATE_ADD(booking_item.start_at, INTERVAL booking_item.duration MINUTE) > now()')));
        return $result->cnt ? 'Yes' : 'No';
    }


    public function bookings($location_slug, $key, $occurs_at)
    {
        $result = DB::select(DB::raw(str_replace(
            array(':location_slug', ':key', ':occurs_at'), array($location_slug, $key, $occurs_at),
            'SELECT ressources.name as room, booking.title as title, booking_item.start_at as start_at, DATE_ADD(booking_item.start_at, INTERVAL booking_item.duration MINUTE) as ends_at,
booking_item.participant_count, concat(users.firstname, " ", users.lastname) as customer_name, users.phone as customer_phone
          FROM booking_item
            JOIN booking on booking_item.booking_id = booking.id
            JOIN ressources on ressources.id = booking_item.ressource_id
            JOIN locations on locations.id = ressources.location_id
            JOIN users on users.id = booking.user_id
          WHERE locations.slug = ":location_slug"
            AND locations.key = ":key"
            AND DATE(booking_item.start_at) = ":occurs_at"')));
        return Response::json($result);
    }

    public function ressources($city_slug)
    {
        $result = array();
        $data = DB::select(DB::raw(str_replace(
            array(':city_slug'), array($city_slug),
            'SELECT ressources.id, ressources.name, ressources.amount, locations.name as location
          FROM ressources 
            JOIN locations on locations.id = ressources.location_id
            JOIN cities on locations.city_id = cities.id
          WHERE LOWER(cities.name) = ":city_slug"
          AND ressources.is_bookable = 1
          ORDER BY locations.name, ressources.order_index')));
        foreach ($data as $item) {
            $result[$item->location][] = array(
                'id' => $item->id,
                'name' => $item->name,
                'hourly_pricing' => $item->amount,
            );
        }
        $response = new \Illuminate\Http\Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET');
        $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, X-Auth-Token');
        $response->setContent(json_encode(array('status' => 'success', 'data' => $result)));
        return $response;
    }

    /**
     * GET Parameters :
     * - from : datetime
     * - to : datetime
     *
     * Returns if ressource is available in the given range, with status and booking count
     */
    public function ressource_status($city_slug, $ressource_id)
    {
        if (!$from = Input::get('from')) {
            return Response::json(array('status' => 'error', 'message' => 'Missing from parameter'));
        }
        if (!$to = Input::get('to')) {
            return Response::json(array('status' => 'error', 'message' => 'Missing to parameter'));
        }
        $status = -1;
        $booking_count = 0;

        $result = DB::select(DB::raw(str_replace(
            array(':from', ':to', ':ressource_id'), array($from, $to, $ressource_id),
            'SELECT booking_item.confirmed_at
          FROM booking_item
          WHERE booking_item.ressource_id = :ressource_id
            AND booking_item.start_at <= ":to"
            AND DATE_ADD(booking_item.start_at, INTERVAL duration MINUTE) >= ":from"')));
        foreach ($result as $item) {
            $booking_count++;
            $status = max($status, (int)($item->confirmed_at != null));
        }
        switch ($status) {
            case -1 :
                $status = 'available';
                break;
            case 0 :
                $status = 'confirmation_pending';
                break;
            case 1 :
                $status = 'booked';
                break;
        }

        $result = array(
            'status' => 'success',
            'data' => array(
                'from' => $from,
                'to' => $to,
                'status' => $status,
                'booking_count' => $booking_count
            ));

        $response = new \Illuminate\Http\Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET');
        $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, X-Auth-Token');
        $response->setContent(json_encode($result));
        return $response;
    }

    public function bookings_book()
    {
        $json = json_decode(Request::getContent());

        $user_id = $json->customer_id;
        $user = User::findOrFail($user_id);

        $caption = $user->fullname;
        $organisation_id = $json->organisation_id;
        if ($organisation_id) {
            $organisation = Organisation::find($organisation_id);
            $caption = sprintf('%s (%s)', $organisation->name, $caption);
        } else {
            $organisation = null;
        }
        $booking = new Booking();
        $booking->user_id = $user->id;
        $booking->title = $caption;
        if ($organisation) {
            $booking->organisation_id = $organisation->id;
        }
        $booking->is_private = Config::get('booking::default_is_private', true);
        $booking->save();
        $booking_items = array();
        foreach ($json->bookings as $booking_json) {
            $start_at = sprintf('%s %s:00', $booking->day, $booking->from);
            $end_at = sprintf('%s %s:00', $booking->day, $booking->to);

            $item = new BookingItem();
            $item->booking = $booking;
            $item->ressource_id = $booking_json->ressource_id;
            if (Config::get('booking::default_is_confirmed', true)) {
                $item->confirmed_at = date('Y-m-d H:i:s');
                $item->confirmed_by_user_id = $user->id;
            }
            $item->start_at = $start_at;
            $item->duration = (strtotime($end_at) - strtotime($start_at)) / 60;
            $item->save();
            $booking_items[$item->id] = $item;
        }

        $items = BookingItem::query()
            ->whereIn('id', array_keys($booking_items))
            ->with('booking')
            ->orderBy('start_at', 'ASC')
            ->get();
        try {
            $invoice = $this->createQuoteFromBookingItems($items);
        } catch (\Exception $e) {
            return Redirect::route('booking_list')->with('mError', $e->getMessage());
        }

        $result = array(
            'status' => 'success',
            'data' => array(
                'id' => $invoice->id,
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

        // récupérer le client
        // récupérer l'organisation si elle est spécifiée
        // créer les bookings et garder les ids
        // créer le devis
        // renvoyer l'url vers le devis + le pdf du devis

    }
}