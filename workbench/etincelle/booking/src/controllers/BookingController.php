<?php


use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;

class BookingController extends Controller
{

    public function index($now = false)
    {
        $params = array();
        if (!$now) {
            $now = date('Y-m-d');
        }
        $params['now'] = $now;
        return View::make('booking::index', $params);
    }

    public function create($start_at = null, $end_at = null)
    {
        if (!$start_at) {
            $start_at = date('Y-m-d H:00');
        }
        if (!$end_at) {
            $end_at = date('Y-m-d H:i', strtotime($end_at) + Config::get('booking::default_meeting_duration', 1) * 3600);
        }

        $item = new BookingItem();
        $item->booking = new Booking();
        $item->booking->user_id = Auth::id();
        $organisations = Auth::user()->organisations;
        $item->booking->title = Auth::user()->orgaFullname;
        if ($organisations) {
            $organisation = $organisations->last();
            if ($organisation) {
                $item->booking->organisation_id = $organisation->id;
            }
        }
        $item->booking->is_private = Config::get('booking::default_is_private', true);
        if (Config::get('booking::default_is_confirmed', true)) {
            $item->confirmed_at = date('Y-m-d H:i:s');
            $item->confirmed_by_user_id = Auth::id();
        }
        $item->start_at = $start_at;
        $item->duration = (strtotime($end_at) - strtotime($start_at)) / 60;

        return View::make('booking::modify', array('booking_item' => $item));
    }

    public function listAjax()
    {
        $result = array();
        $bookings = Booking::whereHas('items', function ($query) {
            $query->join('ressources', 'booking_item.ressource_id', '=', 'ressources.id')
                ->join('locations', 'ressources.location_id', '=', 'locations.id')
                ->where('locations.city_id', '=', Auth::user()->location->city_id)
                ->where('start_at', '<', Input::get('end'))
                ->where(DB::raw('DATE_ADD(start_at, INTERVAL duration MINUTE)'), '>', Input::get('start'));
        })
            ->with('items')->get();

        foreach ($bookings as $booking) {
            //var_dump($booking->items()->count());
            foreach ($booking->items()->get() as $booking_item) {
                /** @var BookingItem $booking_item */
                $result[] = $booking_item->toJsonEvent();

            }
        }
        /*
                $start = strtotime(Input::get('start'));
                $end = strtotime(Input::get('end'));
                $i = $start;
                while ($i < $end) {
                    if (!in_array(date('w', $i), array(0, 6))) {
                        $result[] = $this->createBackgroundEvent($i,
                            Config::get('booking::work_hour_start', '09:00'),
                            Config::get('booking::work_hour_end', '18:00')
                        );
                    }
                    $i += 24 * 3600;
                }
        */
        return Response::json($result);

    }

    protected function createBackgroundEvent($timestamp, $start_time, $end_time)
    {
        return array(
            'start' => sprintf('%sT%s:00', date('Y-m-d', $timestamp), $start_time),
            'end' => sprintf('%sT%s:00', date('Y-m-d', $timestamp), $end_time),
            'rendering' => 'background',
            'editable' => false,
            'id' => sprintf('background-%d', $timestamp)
        );
    }

    public function deleteAjax()
    {
        $booking_id = Input::get('booking_id');
        $booking_item_id = Input::get('id');
        $booking = Booking::find($booking_id);
        if (!$booking) {
            return Response::json(array('status' => 'KO', 'message' => 'Réservation inconnue'));
        }

        $user = $booking->user;
        $booking_item = BookingItem::find($booking_item_id);
        $ressource = $booking_item->ressource;

        try {
            $booking_item->checkBeDeletedBy(Auth::user());
        } catch (\Exception $e) {
            return Response::json(array('status' => 'KO', 'message' => $e->getMessage()));
        }

        if ($booking->items()->count() == 1) {
            BookingItem::destroy($booking_item_id);
            Booking::destroy($booking_id);
        } else {
            BookingItem::destroy($booking_item_id);
        }

        try {
            $this->sendDeletedBookingNotification($booking_item, $ressource, $booking, $user);
        } catch (\Exception $e) {

        }

        if (Request::ajax()) {
            return Response::json(array('status' => 'OK', 'id' => $booking_item_id));
        }
        return Redirect::route('booking_list')->with('mSuccess', 'La réservation a été supprimée');
    }


    protected function extractPublicProperties($booking_item)
    {
        $result = array(
            'title' => $booking_item->booking->title,
            'content' => $booking_item->booking->content,
            'start_at' => $booking_item->start_at,
            'duration' => $booking_item->duration,
//            'is_private' => $booking_item->booking->is_private,
//            'is_open_to_registration' => $booking_item->is_open_to_registration,
//            'ressources' => array(),
        );
//        foreach ($booking_item->booking->items() as $item) {
//            $result['ressources'][$item->ressource->id] = array(
//                'name' => $item->ressource->name,
//                'location' => $item->ressource->location->fullname,
//            );
//        }
        return $result;
    }

    public function updateAjax()
    {
        $booking_item_id = Input::get('id');
        $booking_item = BookingItem::find($booking_item_id);

        $old = $this->extractPublicProperties($booking_item);

        $booking_item->start_at = Input::get('start');
        $booking_item->duration = floor((strtotime(Input::get('end')) - strtotime(Input::get('start'))) / 60);
        if ($booking_item->ressource_id != Input::get('ressource_id')) {
            $booking_item->ressource_id = Input::get('ressource_id');

            $ressource = Ressource::where('id', '=', $booking_item->ressource_id)->first();
            $location = $ressource->location;
            if ($location->voucher_endpoint) {
                $voucher = Booking::generateVoucher($location->voucher_endpoint, $location->voucher_key, $location->voucher_secret, $booking_item->start_at);
                if ($voucher) {
                    $booking = $booking_item->booking;
                    $booking->wifi_login = $voucher['username'];
                    $booking->wifi_password = $voucher['password'];
                    $booking->save();
                } else {
                    // log error ?
                }
            }
        }
        $booking_item->save();

        $new = $this->extractPublicProperties($booking_item);
        try {
            $this->sendUpdatedBookingNotification($booking_item, $old, $new);
        } catch (\Exception $e) {

        }

        return Response::json(array('status' => 'OK', 'data' => $booking_item->toJsonEvent()));
    }


    public function delete($id)
    {
        $booking_item = BookingItem::find($id);
        if (!$booking_item) {
            return Redirect::route('booking_list')->with('mError', 'La réservation est inconnue');
        }

        $booking = $booking_item->booking;

        try {
            $booking_item->checkBeDeletedBy(Auth::user());
        } catch (\Exception $e) {
            return Redirect::route('booking_list')->with('mError', $e->getMessage());
        }

        $user = $booking->user;
        $ressource = $booking_item->ressource;
        $this->sendDeletedBookingNotification($booking_item, $ressource, $booking, $user);

        if ($booking_item->booking->items()->count() == 1) {
            $booking_item->delete();
            $booking->delete();
        } else {
            $booking_item->delete();
        }

        return Redirect::route('booking_list')->with('mSuccess', 'La réservation a été supprimée');
    }

    public function logTimeAjax($id)
    {
        $booking_item = BookingItem::find($id);
        if (!$booking_item) {
            return Response::json(array('status' => 'KO',
                'message' => 'La réservation est inconnue'));
        }

        $time = new PastTime();
        $time->user_id = $booking_item->booking->user_id;
        $time->organisation_id = $booking_item->booking->organisation_id;
        $time->ressource_id = $booking_item->ressource_id;
        $time->location_id = $booking_item->ressource->location_id;
        $time->date_past = date('Y-m-d', strtotime($booking_item->start_at));
        $time->time_start = $booking_item->start_at;
        $time->time_end = date('Y-m-d H:i:s', strtotime($booking_item->start_at) + $booking_item->duration * 60);

        $existing = PastTime::query()
                ->where('user_id', $time->user_id)
                ->where('ressource_id', $time->ressource_id)
                ->where('date_past', $time->date_past)
                ->where('time_start', $time->time_start)
                ->where('time_end', $time->time_end)
                ->count() > 0;


        if ($existing) {
            return Response::json(array('status' => 'KO',
                'message' => sprintf('Un enregistrement similaire à %s est déjà présent', $booking_item->booking->title)));
        }
        $time->save();

        return Response::json(array('status' => 'OK',
            'message' => sprintf('La réunion %s a été comptabilisée', $booking_item->booking->title),
            'event' => $booking_item->toJsonEvent()));
    }


    public function makeGift($id)
    {
        $booking_item = BookingItem::find($id);
        if (!$booking_item) {
            return Response::json(array('status' => 'KO',
                'message' => 'La réservation est inconnue'));
        }

        $booking_item->is_free = true;
        $booking_item->save();

        return Response::json(array('status' => 'OK'));
    }


    public function raw()
    {
        $pagination_count = 15;
        if (Input::has('filtre_submitted')) {

            if (Input::has('filtre_organisation_id')) {
                Session::put('filtre_booking.organisation_id', Input::get('filtre_organisation_id'));
            }
            if (Input::has('filtre_ressource_id')) {
                Session::put('filtre_booking.ressource_id', Input::get('filtre_ressource_id'));
            } else {
                Session::forget('filtre_booking.ressource_id');
            }
            if (Input::has('filtre_start')) {
                $date_start_explode = explode('/', Input::get('filtre_start'));
                if (count($date_start_explode) == 3) {
                    Session::put('filtre_booking.start', $date_start_explode[2] . '-' . $date_start_explode[1] . '-' . $date_start_explode[0]);
                } else {
                    Session::put('filtre_booking.start', false);
                }
                /*
                                 if (!Input::has('filtre_user_id')) {
                                    Session::forget('filtre_booking.user_id');
                                }
                */
//            } else {
//                Session::put('filtre_booking.end', date('Y-m-t'));
            }
            if (Input::has('filtre_end')) {
                $date_end_explode = explode('/', Input::get('filtre_end'));
                if (count($date_end_explode) == 3) {
                    Session::put('filtre_booking.end', $date_end_explode[2] . '-' . $date_end_explode[1] . '-' . $date_end_explode[0]);
                } else {
                    Session::put('filtre_booking.end', false);
                }
            } else {
                Session::put('filtre_booking.end', date('Y-m-t'));
            }
            if (Input::has('filtre_toinvoice')) {
                Session::put('filtre_booking.toinvoice', Input::get('filtre_toinvoice'));
            } else {
                Session::put('filtre_booking.toinvoice', false);
            }
            if (Input::has('filtre_user_id')) {
                Session::put('filtre_booking.user_id', Input::get('filtre_user_id'));
                Session::put('filtre_booking.start', Session::get('filtre_booking.start', date('Y-m-d')));
                Session::put('filtre_booking.end', false);
                $pagination_count = null;
            }
        }


        if (Session::has('filtre_booking.start')) {
            $date_filtre_start = Session::get('filtre_booking.start');
            $date_filtre_end = Session::get('filtre_booking.end');
        } else {
            $date_filtre_start = date('Y-m-d');
            //$date_filtre_end = date('Y-m-t');
            $date_filtre_end = Session::get('filtre_booking.end');
        }


        $q = BookingItem::query();
        if ($date_filtre_start) {
            if ($date_filtre_end) {
                $q->whereBetween('start_at', array($date_filtre_start, $date_filtre_end));
            } else {
                $q->where('start_at', '>', $date_filtre_start);
            }
        }
        if (Session::get('filtre_booking.ressource_id')) {
            $q->whereRessourceId(Session::get('filtre_booking.ressource_id'));
        }
        if (Session::get('filtre_booking.toinvoice')) {
            $q->whereNull('invoice_id');
            $q->where('is_free', false);
        }
        if (Auth::user()->isSuperAdmin()) {
            if ((Session::has('filtre_booking.user_id') && Session::get('filtre_booking.user_id'))
                || (Session::has('filtre_booking.organisation_id') && Session::get('filtre_booking.organisation_id'))) {
                $q->join('booking', function ($j) {
                    $j->on('booking_id', '=', 'booking.id');
                    if (Session::has('filtre_booking.user_id') && Session::get('filtre_booking.user_id')) {
                        $j->where('user_id', '=', Session::get('filtre_booking.user_id'));
                    }
                    if (Session::has('filtre_booking.organisation_id') && Session::get('filtre_booking.organisation_id')) {
                        $j->where('organisation_id', '=', Session::get('filtre_booking.organisation_id'));
                    }
                });
            }
        } else {
            $q->join('booking', function ($j) {
                $j->on('booking_id', '=', 'booking.id')
                    ->where('user_id', '=', Auth::user()->id);
            });
        }


        $q->orderBy('start_at', 'ASC');
        $q->with('booking.user', 'ressource', 'booking');

        $params = array();
        $params['pagination_count'] = $pagination_count;
        if ($pagination_count) {
            $params['items'] = $q->paginate($pagination_count, array('booking_item.*'));
        } else {
            $params['items'] = $q->select('booking_item.*')->get();

        }
        return View::make('booking::raw', $params);
    }

    public function cancelFilter()
    {
        Session::forget('filtre_booking.user_id');
        Session::forget('filtre_booking.organisation_id');
        Session::forget('filtre_booking.start');
        Session::forget('filtre_booking.end');
        Session::forget('filtre_booking.ressource_id');
        Session::forget('filtre_booking.toinvoice');
        return Redirect::route('booking_list');
    }

    protected function sendNewBookingNotification($booking, $is_new)
    {
        Mail::send('booking::emails.created', array('booking' => $booking, 'is_new' => $is_new), function ($m) use ($booking, $is_new) {
            $start_at = $booking->items->first()->start_at;
            if ($start_at instanceof \DateTime) {
            } else {
                $start_at = new \DateTime($start_at);
            }
            if ($is_new) {
                $title = 'Nouvelle réservation';
            } else {
                $title = 'Modification de réservation';
            }

            $m->from($_ENV['organisation_email'], $_ENV['organisation_name'])
                ->bcc($_ENV['organisation_email'], $_ENV['organisation_name'])
                ->to($booking->user->email, $booking->user->fullname)
                ->subject(html_entity_decode(sprintf('%s - %s - %s', $_ENV['organisation_name'], $title, $start_at->format('d/m/Y H:i'))));
        });
    }

    protected function sendUpdatedBookingNotification($booking_item, $old, $new)
    {
        $has_changed = false;
        foreach ($old as $k => $v) {
            if (isset($new[$k])) {
                if (is_array($v)) {
                    $has_changed = $has_changed || (count(array_diff($old[$k], $new[$k])) > 0);
                } elseif ($old[$k] != $new[$k]) {
                    $has_changed = true;
                }
            }
        }
        if (!$has_changed) {
            return false;
        }
        Mail::send('booking::emails.updated', array('booking_item' => $booking_item, 'old' => $old, 'new' => $new), function ($m) use ($booking_item, $old, $new) {
            if ($old['start_at'] == $new['start_at']) {
                $update = sprintf('%s %s > %s', date('d/m/Y H:i', strtotime($old['start_at'])), durationToHuman($old['duration']), durationToHuman($new['duration']));
            } else {
                if ($old['duration'] == $new['duration']) {
                    $update = date('d/m/Y H:i', strtotime($old['start_at']));
                } else {
                    $update = sprintf('%s > %s', date('d/m/Y H:i', strtotime($old['start_at'])), date('d/m/Y H:i', strtotime($new['start_at'])));
                }
            }

            $m->from($_ENV['organisation_email'], $_ENV['organisation_name'])
                ->bcc($_ENV['organisation_email'], $_ENV['organisation_name'])
                ->to($booking_item->booking->user->email, $booking_item->booking->user->fullname)
                ->subject(html_entity_decode(sprintf('%s - Modification de réservation - %s', $_ENV['organisation_name'], $update)));
        });
    }

    protected function sendDeletedBookingNotification($booking_item, $ressource, $booking, $user)
    {
        Mail::send('booking::emails.deleted', array('booking_item' => $booking_item, 'ressource' => $ressource, 'booking' => $booking, 'user' => $user), function ($m) use ($user, $booking_item) {
            $m->from($_ENV['organisation_email'], $_ENV['organisation_name'])
                ->bcc($_ENV['organisation_email'], $_ENV['organisation_name'])
                ->to($user->email, $user->fullname)
                ->subject(html_entity_decode(sprintf('%s - Annulation de réservation - %s', $_ENV['organisation_name'], date('d/m/Y H:i', strtotime($booking_item->start_at)))));
        });
    }

    public function ical($key)
    {

        switch ($key) {
            case 'public':
                $items = BookingItem::where('start_at', '>=', date('Y-m-d'))
                    ->join('booking', 'booking_item.booking_id', '=', 'booking.id')
                    ->where('booking.is_private', '=', false)
                    ->with('booking', 'ressource')->get();
                $description = '';
                break;
            default:
                $filter = true;
                if (preg_match('/^(.+)_(.+)$/', $key, $tokens)) {
                    $key = $tokens[1];
                    if ($tokens[2] != 'all') {
                        App::abort(404);
                        return false;
                    }

                    $filter = false;
                }
                $owner = User::where('booking_key', '=', $key)->first();
                if (!$owner) {
                    App::abort(404);
                    return false;
                }
                $description = $owner->fullname;

                $items = BookingItem::where('start_at', '>=', date('Y-m-d'))
                    ->join('booking', 'booking_item.booking_id', '=', 'booking.id')
                    ->join('users', 'booking.user_id', '=', 'users.id');
                if ($filter) {
                    $items = $items->where('users.booking_key', '=', $key);
                }
                $items = $items->with('booking', 'ressource')->get();

                break;
        }

        $tz = new DateTimeZone(date_default_timezone_get());
        $offset = (new DateTime("now", $tz))->getOffset();

        $vCalendar = new \Eluceo\iCal\Component\Calendar(Request::server('SERVER_NAME'));
        $vCalendar->setDescription($description);
        foreach ($items as $booking_item) {
            $start = new \DateTime($booking_item->start_at);
            $start->setTimezone($tz);
            $start->add(new DateInterval(sprintf('PT%dS', $offset)));
            $end = new \DateTime($booking_item->start_at);
            $start->setTimezone($tz);
            $end->modify(sprintf('+%d minutes', $booking_item->duration));
            $end->add(new DateInterval(sprintf('PT%dS', $offset)));

            $vEvent = new \Eluceo\iCal\Component\Event();
            $vEvent
                ->setDtStart($start)
                ->setDtEnd($end)
                ->setUseTimezone(true)
                ->setSummary(sprintf('%s (%s)', $booking_item->booking->title, $booking_item->ressource->name));
            $vCalendar->addComponent($vEvent);
        }
        $response = Response::make($vCalendar->render());
        $response->header('Content-Type', 'text/calendar; charset=utf-8');
        $response->header('Content-Disposition', 'attachment; filename="cal.ics"');
        return $response;
    }
//
//    public function export(){
//        $response = Response::make($vCalendar->render());
//        $response->header('Content-Type', 'text/calendar; charset=utf-8');
//        $response->header('Content-Disposition', 'attachment; filename="cal.ics"');
//        return $response;
//    }

    /**
     * Verify if exist
     */
    private function dataExist($id)
    {
        if (Auth::user()->isSuperAdmin()) {
            $data = BookingItem::with('booking')->find($id);
        } else {
            $data = BookingItem::with('booking')
                //->where('booking.user_id', '=', Auth::id())
                ->find($id);
        }

        if (!$data) {
            return Redirect::route('booking_list')->with('mError', 'Cette réservation est introuvable !');
        } else {
            return $data;
        }
    }

    public function show($id)
    {
        $item = BookingItem::with('members')->find($id);
        if (!$item) {
            App::abort(404);
        }
        return View::make('booking::show', array('booking_item' => $item));
    }

    public function modify($id)
    {
        $item = $this->dataExist($id);

        return View::make('booking::modify', array('booking_item' => $item));
    }


    public function modify_check($id = null)
    {
        if ($id) {
            $booking_item = $this->dataExist($id);
            $is_new = false;
        } else {
            $booking_item = new BookingItem();
            $booking_item->booking = new Booking();
            $booking_item->booking->user_id = Auth::id();
            $is_new = true;
        }

        $old = $this->extractPublicProperties($booking_item);

        $messages = array();
        if (!preg_match('#^[0-9]{2}/[0-9]{2}/[0-9]{4}$#', Input::get('date'))) {
            $messages['date'] = 'La date doit être renseignée';
        }
        if (!preg_match('#^[0-9]{2}:[0-9]{2}$#', Input::get('start'))) {
            $messages['start'] = 'L\'heure de début doit être renseignée';
        }
        if (!preg_match('#^[0-9]{2}:[0-9]{2}$#', Input::get('end'))) {
            $messages['end'] = 'L\'heure de fin doit être renseignée';
        }
        $rooms = Input::get('rooms');
        if (empty($rooms)) {
            $messages['rooms'] = 'La salle doit être renseignée';
        } else {
            if (!Auth::user()->isSuperAdmin()) {
                $start = newDateTime(Input::get('date'), Input::get('start'));
                $end = newDateTime(Input::get('date'), Input::get('end'));

                $items = BookingItem::where('start_at', '<', $end->format('Y-m-d H:i:s'))
                    ->where(DB::raw('DATE_ADD(start_at, INTERVAL duration MINUTE)'), '>', $start->format('Y-m-d H:i:s'))
                    ->whereIn('ressource_id', Input::get('rooms'))
                    ->where('id', '!=', (int)$id)
                    ->get();
                foreach ($items as $conflict) {
                    if (!isset($messages['start'])) {
                        $messages['start'] = '';
                    }
                    $messages['start'] .= sprintf('La salle %s est déjà réservée sur ce créneau' . "\n", $conflict->ressource->name);
                }
            }
        }
        $start_at = newDateTime(Input::get('date'), Input::get('start'));
        if (!Auth::user()->isSuperAdmin() && ($start_at->format('Y-m-d H:i:s') < (new \DateTime())->format('Y-m-d H:i:s'))) {
            $messages['start'] = 'Vous ne pouvez pas réserver une salle dans le passé';
        }
        if (count($messages)) {
            return Response::json(array(
                'status' => 'KO',
                'messages' => $messages
            ));

        }

        $booking_items = array();
        $booking = $booking_item->booking;
        if (!Auth::user()->isSuperAdmin() && (Auth::id() != $booking->user_id)) {
            App::abort(403);
        }
        if (!$is_new) {
            foreach ($booking->items()->where('start_at', '=', $booking_item->start_at)->get() as $item) {
                $booking_items[$item->ressource_id] = $item;
            }
        }

        $booking->title = Input::get('title');
        $booking->content = Input::get('description');
        if (Auth::user()->isSuperAdmin()) {
            $booking->user_id = Input::get('user_id');
            $booking->organisation_id = Input::get('organisation_id');
            if (empty($booking->user_id)) {
                $booking->user_id = Auth::id();
            }
        } else {
            $booking->user_id = Auth::id();
        }
        $booking->is_private = Input::get('is_private', false);

        if (!$booking->organisation_id) {
            $booking->organisation_id = null;
        }

        $ressources = Input::get('rooms');
        $ressource = Ressource::where('id', '=', array_pop($ressources))->first();
        $location = $ressource->location;
        if ($location->voucher_endpoint) {
            $voucher = Booking::generateVoucher($location->voucher_endpoint, $location->voucher_key, $location->voucher_secret, $start_at->format('Y-m-d H:i'));
            if ($voucher) {
                $booking->wifi_login = $voucher['username'];
                $booking->wifi_password = $voucher['password'];
            } else {
                // log error ?
            }
        }

        $booking->save();

        $doConfirmation = Input::get('is_confirmed', Config::get('booking::default_is_confirmed', true));
        $confirmed_at = date('Y-m-d H:i:s');

        foreach (Input::get('rooms') as $ressource_id) {
            if (isset($booking_items[$ressource_id])) {
                $booking_item_ = $booking_items[$ressource_id];
                unset($booking_items[$ressource_id]);
            } else {
                $booking_item_ = new BookingItem();
                $booking_item_->booking_id = $booking->id;
                $booking_item_->ressource_id = $ressource_id;
            }
            $booking_item_->start_at = $start_at;
            $booking_item_->duration = getDuration(Input::get('start'), Input::get('end'));
            $booking_item_->is_open_to_registration = Input::get('is_open_to_registration', false);
            $booking_item_->is_free = Input::get('is_free', false);
            $booking_item_->invoice_id = Input::get('invoice_id', null);
            if ($doConfirmation) {
                if (!$booking_item_->confirmed_at) {
                    $booking_item_->confirmed_at = $confirmed_at;
                    $booking_item_->confirmed_by_user_id = Auth::id();
                }
            } else {
                if (Auth::user()->isSuperAdmin()) {
                    $booking_item_->confirmed_at = null;
                    $booking_item_->confirmed_by_user_id = null;
                }
            }
            if (!$booking_item_->invoice_id) {
                $booking_item_->invoice_id = null;
            }
            $booking_item_->save();
        }
        foreach ($booking_items as $booking_item_to_delete) {
            $booking_item_to_delete->delete();
        }

        $new = $this->extractPublicProperties($booking_item_);
        try {
            $this->sendUpdatedBookingNotification($booking_item_, $old, $new);
        } catch (\Exception $e) {

        }

//        var_dump($booking_item_);
//        var_dump($booking_item);
        //      var_dump($booking_item->booking);
        //exit;

        return Redirect::route('booking_with_date', array('now' => $booking_item_->start_at->format('Y-m-d')))->with('mSuccess', 'La réservation a été modifiée')->withInput();

    }


    public function createQuoteFromBookingItem($booking_item_id)
    {
        $booking_item = BookingItem::find($booking_item_id);
        if (!$booking_item) {
            return Redirect::route('quote_list')->with('mError', 'Réservation inconnue');
        }
        $invoice = $this->createQuoteFromBookingItems(array($booking_item));

        return Redirect::route('invoice_modify', $invoice->id)->with('mSuccess', 'Le devis a été créé');
    }

    protected function createQuoteFromBookingItems($booking_items)
    {
        $booking_item = $booking_items[0];

        $ressources = array();
        foreach (Ressource::with('location')->get() as $ressource) {
            $ressources[$ressource->id] = $ressource;
        }

        $locations = array();
        foreach (Location::get() as $location) {
            $locations[$location->id] = $location;
        }

        $by_ressources = array();
        $users_ids = array();
        foreach ($booking_items as $booking_item) {
            $ressource = $ressources[$booking_item->ressource_id];
            if (!isset($by_ressources[$ressource->location_id])) {
                $by_ressources[$ressource->location_id] = array();
            }
            if (!isset($by_ressources[$ressource->location_id][$booking_item->ressource_id])) {
                $by_ressources[$ressource->location_id][$booking_item->ressource_id] = array();
            }
            $by_ressources[$ressource->location_id][$booking_item->ressource_id][] = $booking_item;
            $users_ids[$booking_item->booking->user_id] = true;
        }

        if (count($users_ids) > 1) {
            throw new \Exception('Toutes les réservations doivent être associées au même utilisateur pour faire un devis.');
        }

        $organisation = $booking_item->booking->organisation;
        $user = $booking_item->booking->user;

        $invoice = new Invoice();
        $invoice->type = 'D';
        $invoice->user_id = $user->id;
        $invoice->days = date('Ym');
        $invoice->date_invoice = date('Y-m-d');
        $invoice->number = Invoice::next_invoice_number($invoice->type, $invoice->days);
        if ($organisation) {
            $invoice->organisation_id = $organisation->id;
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

        $vat = VatType::where('value', 20)->first();


        $line_index = 1;
        foreach ($by_ressources as $location_id => $ressources_map) {
            $invoice_line = new InvoiceItem();
            $invoice_line->order_index = $line_index++;
            $invoice_line->invoice_id = $invoice->id;
//            $invoice_line->ressource_id = $ressource_id;
            $invoice_line->vat_types_id = null;
            $invoice_line->text = $locations[$location_id]->sales_presentation;
            $invoice_line->vat_types_id = $vat->id;
            $invoice_line->save();

            foreach ($ressources_map as $ressource_id => $booking_items) {
                $booking_item = $booking_items[0];
                $ressource = $ressources[$booking_item->ressource_id];
                $invoice_line = new InvoiceItem();

                if (empty($invoice->business_terms)) {
                    if (!empty($ressource->location->default_business_terms)) {
                        $invoice->business_terms = $ressource->location->default_business_terms;
                        $invoice->save();
                    }
                }

                $invoice_line->order_index = $line_index++;
                $invoice_line->invoice_id = $invoice->id;
                $invoice_line->ressource_id = $ressource_id;


                $booking_text = '';
                if (count($booking_items) == 1) {
                    $booking_item = array_shift($booking_items);
                    $start = new DateTime($booking_item->start_at);
                    $end = new DateTime($booking_item->start_at);
                    $end->modify(sprintf('+%d minutes', $booking_item->duration));
                    $booking_text = sprintf("<p><b>Réservation le %s de %s à %s.</b>", $start->format('d/m/Y'), $start->format('H:i'), $end->format('H:i'));
                    $invoice_line->amount += min(7, $booking_item->duration / 60) * $ressource->amount;
                } else {
                    $booking_text = '<p><b>Réservation des créneaux suivants :<ul>';
                    foreach ($booking_items as $booking_item) {
                        $start = new DateTime($booking_item->start_at);
                        $end = new DateTime($booking_item->start_at);
                        $end->modify(sprintf('+%d minutes', $booking_item->duration));
                        $booking_text .= sprintf("<li>%s de %s à %s</li>", $start->format('d/m/Y'), $start->format('H:i'), $end->format('H:i'));
                        $invoice_line->amount += min(7, $booking_item->duration / 60) * $ressource->amount;
                    }
                    $booking_text .= '</ul></b></p>';
                }

                $map = array();
                $map['%ressource.name%'] = $ressource->name;
                $map['%ressource.description%'] = $ressource->description;
                $map['%ressource.sales_presentation%'] = $ressource->sales_presentation;
                $map['%booking_text%'] = $booking_text;

                $template = '<b>%ressource.name%</b> %ressource.description%%booking_text%<p>%ressource.sales_presentation%</p>';

                $invoice_line->text = str_replace(array_keys($map), array_values($map), $template);
                $invoice_line->vat_types_id = $vat->id;
                $invoice_line->save();
            }
        }

        return $invoice;
    }

    public function confirm($id)
    {
        $booking_item = $this->dataExist($id);

        if (!Auth::user()->isSuperAdmin() && (Auth::id() != $booking_item->booking->user_id)) {
            App::abort(403);
        }

        $booking_item->confirmed_at = date('Y-m-d H:i');
        $booking_item->confirmed_by_user_id = Auth::id();
        $booking_item->save();
        return Redirect::route('booking_with_date', array('now' => date('Y-m-d', strtotime($booking_item->start_at))))->with('mSuccess', 'La réservation a été confirmée');
    }

    public function dailyPdf($location, $day = null)
    {
        if (null == $day) {
            $day = date('Y-m-d');
        }

//region salles
        $datas = DB::select(DB::raw(sprintf('SELECT ressources.name AS room, concat(date_format( booking_item.start_at, "%%H:%%i" ) , " - ", date_format( booking_item.start_at + INTERVAL booking_item.duration
MINUTE , "%%H:%%i" )) AS timerange, booking.title, 
concat( users.firstname, " ", users.lastname ) AS contact,
organisations.name as organisation, booking.wifi_login, booking.wifi_password
FROM `booking_item`
JOIN ressources ON booking_item.ressource_id = ressources.id
JOIN locations ON ressources.location_id = locations.id
JOIN booking ON booking_item.booking_id = booking.id
JOIN users ON users.id = booking.user_id
LEFT OUTER JOIN organisations on organisations.id = booking.organisation_id 
WHERE booking_item.start_at > "%s 00:00:00"
AND booking_item.start_at <= "%s 23:59:59"
AND locations.slug = "%s"
AND ressources.has_paper_summary = true
ORDER BY room ASC , booking_item.start_at ASC ', $day, $day, $location)));

        $bookings = array();
        //var_dump($datas); exit;

        foreach ($datas as $data) {

            $title = trim($data->title);
            if (empty($title)) {
                $title = $data->organisation;
                if (!empty($title) && trim($data->contact) != '') {
                    $title .= sprintf(' (%s)', $data->contact);
                }
            }
            if (empty($title)) {
                $title = $data->contact;
            }
            $bookings[$data->room][$data->timerange] = array(
                'title' => $title,
                'wifi_login' => $data->wifi_login,
                'wifi_password' => $data->wifi_password,
            );
        }
        $mapping = array();
        $pageNo = 1;
        $pages = array();
        foreach ($bookings as $room => $meetings) {
            $html = '
        <html>
            <head>
                <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head>
                <title>' . $location . ' - ' . $room . ' - ' . $day . '</title>
            </head>
            <body>
            ';
            $html .= '<div class="page">';
            $html .= sprintf('<table width="100%%"><tr><td width="50%%">%s</td><td width="50%%" align="right">%s</td></tr></table>', $room, date('d/m/Y', strtotime($day)));
            $html .= '<table width="100%"><tbody>';
            foreach ($meetings as $timerange => $meeting_data) {
                //$html .= sprintf('<tr><td width="1%%" nowrap="nowrap"><span style="color: #999999; font-size:30px;">%s&nbsp;</span></td><td><span style="font-size:60px;">%s</span></td></tr>', $timerange, $title);
                $html .= sprintf('<tr><td><div style="color: #999999; font-size:30px; ">%s</div><div style="font-size:55px;text-overflow: ellipsis;">%s</div>', $timerange, $meeting_data['title']);
                if ($meeting_data['wifi_login']) {
                    //$html .= sprintf('<p><b>WIFI</b> Identifiant: %s Mot de passe: %s</p>', $meeting_data['wifi_login'], $meeting_data['wifi_password']);
                }
                $html .= '<hr style="border-top: dashed 1px;" /></td></tr>';
                //valign="top"
            }
            $html .= '</tbody></table>';
            $html .= '</div>';
            $html .= '</body>';
            $html .= '</html>';
            $pages[] = $html;
            $mapping[$room] = array('index' => count($pages), 'wifi' => array());
        }
        $pdf = App::make('snappy.pdf');
        $output = $pdf->getOutputFromHtml($pages,
            array('orientation' => 'Landscape',
                'default-header' => false));
        $pdf1filename = tempnam(sys_get_temp_dir(), 'intranet_pdf_') . '.pdf';
        file_put_contents($pdf1filename, $output);
        //endregion

        //region WIFI
        $bookings = array();
        //var_dump($datas); exit;

        foreach ($datas as $data) {

            $title = trim($data->title);
            if (empty($title)) {
                $title = $data->organisation;
                if (!empty($title) && trim($data->contact) != '') {
                    $title .= sprintf(' (%s)', $data->contact);
                }
            }
            if (empty($title)) {
                $title = $data->contact;
            }
            $bookings[$data->room][$data->timerange] = array(
                'title' => $title,
                'wifi_login' => $data->wifi_login,
                'wifi_password' => $data->wifi_password,
            );
        }
        $pages = array();

        foreach ($bookings as $room => $meetings) {
            foreach ($meetings as $timerange => $meeting_data) {
                if ($meeting_data['wifi_login']) {
                    $html = <<<EOS
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head>
    <title>%location% - %room% - %day%</title>
    <style type="text/css">
    .header {
/*
        position: absolute;
        right: 0;
        top: 0;
        left: 0;
*/
        padding: 1rem;
        background-color: #efefef;
        text-align: left;
    }
    .footer {
        position: absolute;
        right: 0;
        bottom: 0;
        left: 0;
        padding: 1rem;
        background-color: #efefef;
        text-align: right;
    }
</style>
</head>
<body>
<div class="header">
    <img src="http://www.coworking-toulouse.com/wp-content/uploads/2015/04/etincelle-coworking-400x400.gif" height="85" width="85" style="float: right" />
    <h1>%title%</h1>
</div>
<div class="page">
    <h2>Bienvenue chez Etincelle Coworking</h2>
    <p>Pour vous connecter au WIFI:
    <ol>
        <li>Sélectionnez le réseau "EtincelleCoworking" (réseau ouvert, sans mot de passe) </li>
        <li>Une page d’identification devrait s’afficher avec le logo Etincelle Coworking. Si ce n’est pas le cas, ouvrez un navigateur internet et allez à l’adresse http://192.168.2.1:8000/</li>
        <li>Utilisez les informations de connexion ci-dessous en respectant les majuscules et les minuscules.</li>
    </ol></p>
    <table>
        <tr>
            <td style="font-size: 18pt">Identifiant&nbsp;:&nbsp;</td>
            <td style="font-size: 18pt">%wifi_login%</td>
        </tr>
        <tr>
            <td style="font-size: 18pt">Mot de passe&nbsp;:&nbsp;</td>
            <td style="font-size: 18pt">%wifi_password%</td>
        </tr>
    </table>
    
    <p>NB: Cet accès est valable aujourd'hui uniquement (%day%).</p>
    <p>Si vous avez besoin d'aide, contactez un membre de l'équipe dans la zone d’accueil ou au 05 64 88 01 30 (renvoyé sur nos téléphones portables).</p>
    
    <p>&nbsp;</p>
    <p>Si tout fonctionne correctement, vous pouvez : 
    <ul>
        <li>Aimer notre page Facebook : http://fb.me/EtincelleCoworking</li>
        <li>Nous suivre sur Twitter : https://twitter.com/etincelle_tls</li>
        <li>Laisser un avis sur Google : https://goo.gl/vzeXYy</li>
    </ul>
    </p>
    <p>&nbsp;</p>
    <p>Nous vous souhaitons une excellente réunion!</p>
</div>
<div class="footer">
    <small>%room% - %day% %timeslot%</small>
</div>
<div class="page-break"></div>
</body></html>
EOS;
                    $macros = array(
                        '%location%' => $location,
                        '%room%' => $room,
                        '%day%' => date('d/m/Y', strtotime($day)),
                        '%title%' => $meeting_data['title'],
                        '%wifi_login%' => $meeting_data['wifi_login'],
                        '%wifi_password%' => $meeting_data['wifi_password'],
                        '%timeslot%' => $timerange,
                    );
                    $html = str_replace(array_keys($macros), array_values($macros), $html);
                    $pages[] = $html;

                    $mapping[$room]['wifi'][] = count($pages);
                }
            }
        }
        $pdf = App::make('snappy.pdf');
        $output = $pdf->getOutputFromHtml($pages,
            array(
                //'orientation' => 'Landscape',
                'default-header' => false));
        $pdf2filename = tempnam(sys_get_temp_dir(), 'intranet_pdf_') . '.pdf';
        file_put_contents($pdf2filename, $output);

        //endregion

        $pdf = new \Clegginabox\PDFMerger\PDFMerger;
        foreach ($mapping as $room => $data) {
            $pdf->addPDF($pdf1filename, $data['index'], 'L');
            if (count($data['wifi'])) {
                $pdf->addPDF($pdf2filename, implode(',', $data['wifi']), 'P');
            }
        }
        $result = new \Illuminate\Http\Response($pdf->merge('string'), 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('filename="%s_%s.pdf"', $day, $location)));
        unlink($pdf1filename);
        unlink($pdf2filename);
        return $result;
        //attachment;
    }

    public function status($id)
    {
        $ressource = Ressource::find($id);
        if (!$ressource) {
            App::abort(404, 'Ressource inconnue');
        }
        if (!$ressource->is_bookable) {
            App::abort(404, 'La ressource n\'est pas réservable');
        }

        $bookings = DB::select(DB::raw('SELECT booking.title, booking_item.start_at, booking_item.duration, DATE_ADD(booking_item.start_at, INTERVAL booking_item.duration MINUTE) as end_at
        FROM booking join booking_item ON booking.id = booking_item.booking_id
        WHERE (SELECT count(*) from booking_item WHERE booking_item.booking_id = booking.id and start_at < "' . date('Y-m-d', strtotime('+1 day')) . '" and DATE_ADD(start_at, INTERVAL duration MINUTE) > "' . date('Y-m-d H:i:s') . '" and ressource_id = ' . $id . ') >= 1 
        ORDER BY booking_item.start_at ASC, booking_item.duration DESC 
        '));

        $free_duration = null;
        $current_booking = array_shift($bookings);

        if (!empty($current_booking)
            && ($current_booking->start_at < date('Y-m-d H:i:s'))
        ) {
            $spent_time = (time() - strtotime($current_booking->start_at)) / 60;
            $current_booking_progress = round(100 * $spent_time / $current_booking->duration);
            //var_dump($current_booking_progress);
            if ($current_booking_progress > 100) {
                $current_booking_progress = 100;
            }

            $next_booking = array_shift($bookings);
        } else {
            $next_booking = $current_booking;
            $current_booking = null;
            $current_booking_item = null;
            $current_booking_progress = 0;

            if ($next_booking) {
                $free_duration_items = $this->secondsToTime(strtotime($next_booking->start_at) - time());
                $tokens = array();
                if ($free_duration_items['h']) {
                    if ($free_duration_items['h'] > 1) {
                        $tokens[] = sprintf('%d heures', $free_duration_items['h']);
                    } else {
                        $tokens[] = sprintf('%d heure', $free_duration_items['h']);
                    }
                }
                if ($free_duration_items['m']) {
                    if ($free_duration_items['m'] > 1) {
                        $tokens[] = sprintf('%d minutes', $free_duration_items['m']);
                    } else {
                        $tokens[] = sprintf('%d minute', $free_duration_items['m']);
                    }
                }
                $free_duration = implode(', ', $tokens);
            }
        }

        $attrs = array(
            'ressource' => $ressource,
            'current_booking' => $current_booking,
            'current_booking_progress' => $current_booking_progress,
            'next_booking' => $next_booking,
            'free_duration' => $free_duration,
            'bookings' => $bookings
        );

        return View::make('booking::status', $attrs);
    }

    protected function secondsToTime($inputSeconds)
    {

        $secondsInAMinute = 60;
        $secondsInAnHour = 60 * $secondsInAMinute;
        $secondsInADay = 24 * $secondsInAnHour;

        // extract days
        $days = floor($inputSeconds / $secondsInADay);

        // extract hours
        $hourSeconds = $inputSeconds % $secondsInADay;
        $hours = floor($hourSeconds / $secondsInAnHour);

        // extract minutes
        $minuteSeconds = $hourSeconds % $secondsInAnHour;
        $minutes = floor($minuteSeconds / $secondsInAMinute);

        // extract the remaining seconds
        $remainingSeconds = $minuteSeconds % $secondsInAMinute;
        $seconds = ceil($remainingSeconds);

        // return the final array
        $obj = array(
            'd' => (int)$days,
            'h' => (int)$hours,
            'm' => (int)$minutes,
            's' => (int)$seconds,
        );
        return $obj;
    }


    public function globalAction()
    {
        if (Input::has('quote')) {
            return $this->quoteMultiple();
        }
//        if (Input::has('confirm')) {
//            return $this->confirmMultiple();
//        }

    }

    public function quoteMultiple()
    {
        if (count(Input::get('items')) == 0) {
            return Redirect::route('booking_list');
        }
        $items = BookingItem::query()
            ->whereIn('id', Input::get('items'))
            ->with('booking')
            ->orderBy('start_at', 'ASC')
            ->get();
        try {
            $invoice = $this->createQuoteFromBookingItems($items);
        } catch (\Exception $e) {
            return Redirect::route('booking_list')->with('mError', $e->getMessage());
        }

        return Redirect::route('invoice_modify', $invoice->id)->with('mSuccess', 'Le devis a été créé');
    }

    public function ressource_ical($id)
    {

        $ressource = Ressource::where('id', '=', $id);
                $items = BookingItem::where('start_at', '>=', date('Y-m-d'))
                    ->where('ressource_id', '=', $ressource->id)
                    ->get();

        $tz = new DateTimeZone(date_default_timezone_get());
        $offset = (new DateTime("now", $tz))->getOffset();

        $vCalendar = new \Eluceo\iCal\Component\Calendar($ressource->name);
        foreach ($items as $booking_item) {
            $start = new \DateTime($booking_item->start_at);
            $start->setTimezone($tz);
            $start->add(new DateInterval(sprintf('PT%dS', $offset)));
            $end = new \DateTime($booking_item->start_at);
            $start->setTimezone($tz);
            $end->modify(sprintf('+%d minutes', $booking_item->duration));
            $end->add(new DateInterval(sprintf('PT%dS', $offset)));

            $vEvent = new \Eluceo\iCal\Component\Event();
            $vEvent
                ->setDtStart($start)
                ->setDtEnd($end)
                ->setUseTimezone(true)
                ->setSummary('Réservé');
            $vCalendar->addComponent($vEvent);
        }
        $response = Response::make($vCalendar->render());
        $response->header('Content-Type', 'text/calendar; charset=utf-8');
        $response->header('Content-Disposition', 'attachment; filename="cal.ics"');
        return $response;
    }

}