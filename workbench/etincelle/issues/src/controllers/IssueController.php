<?php


use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;

class IssueController extends Controller
{
    public function index()
    {

        if (Input::has('filtre_submitted')) {
            if (Input::has('filtre_user_id')) {
                Session::put('filtre_issues.user_id', Input::get('filtre_user_id'));
            }
//            if (Input::has('filtre_ressource_id')) {
//                Session::put('filtre_booking.ressource_id', Input::get('filtre_ressource_id'));
//            } else {
//                Session::forget('filtre_booking.ressource_id');
//            }
//            if (Input::has('filtre_start')) {
//                $date_start_explode = explode('/', Input::get('filtre_start'));
//                if (count($date_start_explode) == 3) {
//                    Session::put('filtre_booking.start', $date_start_explode[2] . '-' . $date_start_explode[1] . '-' . $date_start_explode[0]);
//                } else {
//                    Session::put('filtre_booking.start', false);
//                }
//                if (!Input::has('filtre_user_id')) {
//                    Session::forget('filtre_booking.user_id');
//                }
//            } else {
//                Session::put('filtre_booking.end', date('Y-m-t'));
//            }
//            if (Input::has('filtre_end')) {
//                $date_end_explode = explode('/', Input::get('filtre_end'));
//                if (count($date_end_explode) == 3) {
//                    Session::put('filtre_booking.end', $date_end_explode[2] . '-' . $date_end_explode[1] . '-' . $date_end_explode[0]);
//                } else {
//                    Session::put('filtre_booking.end', false);
//                }
//            } else {
//                Session::put('filtre_booking.end', date('Y-m-t'));
//            }
//            if (Input::has('filtre_toinvoice')) {
//                Session::put('filtre_booking.toinvoice', Input::get('filtre_toinvoice'));
//            } else {
//                Session::put('filtre_booking.toinvoice', false);
//            }
        }


        $q = Issue::query();
//        if ($date_filtre_start && $date_filtre_end) {
//            $q->whereBetween('start_at', array($date_filtre_start, $date_filtre_end));
//        }
//        if (Session::get('filtre_booking.ressource_id')) {
//            $q->whereRessourceId(Session::get('filtre_booking.ressource_id'));
//        }
//        if (Session::get('filtre_booking.toinvoice')) {
//            $q->whereNull('invoice_id');
//            $q->where('is_free', false);
//        }
        if (Auth::user()->isSuperAdmin()) {
            if (Session::has('filtre_issue.user_id')) {
                $q->where('user_id', '=', Session::get('filtre_issues.user_id'));
            }
        } else {
            $q->where('user_id', '=', Auth::user()->id);
        }

        $q->orderBy('created_at', 'ASC');
        $q->with('user', 'organisation', 'location');

        $params = array();
        $params['items'] = $q->paginate(15, array('issues.*'));
        return View::make('issues::index', $params);
    }

    public function cancelFilter()
    {
        Session::forget('filtre_issues.user_id');
//        Session::forget('filtre_booking.start');
//        Session::forget('filtre_booking.end');
//        Session::forget('filtre_booking.ressource_id');
//        Session::forget('filtre_booking.toinvoice');
        return Redirect::route('issues');
    }

    public function create()
    {
        $issue = new Issue();
        $issue->location_id = Auth::user()->default_location_id;
        $issue->user_id = Auth::id();
        return View::make('issues::modify', array('item' => $issue));
    }

    private function dataExist($id)
    {
        if (Auth::user()->isSuperAdmin()) {
            $data = Issue::find($id);
        } else {
            $data = Issue::whereUserId(Auth::id())->find($id);
        }

        if (!$data) {
            return Redirect::route('issues')->with('mError', 'Cette tâche est introuvable !');
        } else {
            return $data;
        }
    }

    public function modify_check($id = null)
    {
        if (empty($id)) {
            // new issue
            $issue = new Issue();
        } else {

            $issue = $this->dataExist($id);
        }
        $issue->save();

        return Redirect::route('issues')->with('mSuccess', 'La tâche a été créée');
//
//        $old = $this->extractPublicProperties($booking_item);
//
//        $messages = array();
//        if (!preg_match('#^[0-9]{2}/[0-9]{2}/[0-9]{4}$#', Input::get('date'))) {
//            $messages['date'] = 'La date doit être renseignée';
//        }
//        if (!preg_match('#^[0-9]{2}:[0-9]{2}$#', Input::get('start'))) {
//            $messages['start'] = 'L\'heure de début doit être renseignée';
//        }
//        if (!preg_match('#^[0-9]{2}:[0-9]{2}$#', Input::get('end'))) {
//            $messages['end'] = 'L\'heure de fin doit être renseignée';
//        }
//        $rooms = Input::get('rooms');
//        if (empty($rooms)) {
//            $messages['rooms'] = 'La salle doit être renseignée';
//        } else {
//            if (!Auth::user()->isSuperAdmin()) {
//                $start = newDateTime(Input::get('date'), Input::get('start'));
//                $end = newDateTime(Input::get('date'), Input::get('end'));
//
//                $items = BookingItem::where('start_at', '<', $end->format('Y-m-d H:i:s'))
//                    ->where(DB::raw('DATE_ADD(start_at, INTERVAL duration MINUTE)'), '>', $start->format('Y-m-d H:i:s'))
//                    ->whereIn('ressource_id', Input::get('rooms'))
//                    ->where('id', '!=', $id)
//                    ->get();
//                foreach ($items as $conflict) {
//                    if (!isset($messages['start'])) {
//                        $messages['start'] = '';
//                    }
//                    $messages['start'] .= sprintf('La salle %s est déjà réservée sur ce créneau' . "\n", $conflict->ressource->name);
//                }
//            }
//        }
//        $start_at = newDateTime(Input::get('date'), Input::get('start'));
//        if (!Auth::user()->isSuperAdmin() && ($start_at->format('Y-m-d H:i:s') < (new \DateTime())->format('Y-m-d H:i:s'))) {
//            $messages['start'] = 'Vous ne pouvez pas réserver une salle dans le passé';
//        }
//        if (count($messages)) {
//            return Response::json(array(
//                'status' => 'KO',
//                'messages' => $messages
//            ));
//
//        }
//
//        $booking_items = array();
//        $booking = $booking_item->booking;
//        if (!Auth::user()->isSuperAdmin() && (Auth::id() != $booking->user_id)) {
//            App::abort(403);
//        }
//        foreach ($booking->items()->where('start_at', '=', $booking_item->start_at)->get() as $item) {
//            $booking_items[$item->ressource_id] = $item;
//        }
//        $is_new = false;
//
//        $booking->title = Input::get('title');
//        $booking->content = Input::get('description');
//        if (Auth::user()->isSuperAdmin()) {
//            $booking->user_id = Input::get('user_id');
//            $booking->organisation_id = Input::get('organisation_id');
//            if (empty($booking->user_id)) {
//                $booking->user_id = Auth::id();
//            }
//        } else {
//            $booking->user_id = Auth::id();
//        }
//        $booking->is_private = Input::get('is_private', false);
//
//        if (!$booking->organisation_id) {
//            $booking->organisation_id = null;
//        }
//        $booking->save();
//
//        foreach (Input::get('rooms') as $ressource_id) {
//            if (isset($booking_items[$ressource_id])) {
//                $booking_item_ = $booking_items[$ressource_id];
//                unset($booking_items[$ressource_id]);
//            } else {
//                $booking_item_ = new BookingItem();
//                $booking_item_->booking_id = $booking->id;
//                $booking_item_->ressource_id = $ressource_id;
//            }
//            $booking_item_->start_at = $start_at;
//            $booking_item_->duration = getDuration(Input::get('start'), Input::get('end'));
//            $booking_item_->is_open_to_registration = Input::get('is_open_to_registration', false);
//            $booking_item_->is_free = Input::get('is_free', false);
//            $booking_item_->invoice_id = Input::get('invoice_id', null);
//            if (!$booking_item_->invoice_id) {
//                $booking_item_->invoice_id = null;
//            }
//            $booking_item_->save();
//        }
//        foreach ($booking_items as $booking_item_to_delete) {
//            $booking_item_to_delete->delete();
//        }
//
//        $new = $this->extractPublicProperties($booking_item);
//        try {
//            $this->sendUpdatedBookingNotification($booking_item, $old, $new);
//        } catch (\Exception $e) {
//
//        }
//
//        return Redirect::route('booking_with_date', array('now' => date('Y-m-d', strtotime($booking_item->start_at))))->with('mSuccess', 'La réservation a été modifiée')->withInput();
    }


}