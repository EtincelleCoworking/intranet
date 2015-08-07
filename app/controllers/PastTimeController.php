<?php

/**
 * Past Time Controller
 */
class PastTimeController extends BaseController
{
    /**
     * Verify if exist
     */
    private function dataExist($id)
    {
        if (Auth::user()->role == 'superadmin') {
            $data = PastTime::find($id);
        } else {
            $data = PastTime::whereUserId(Auth::user()->id)->find($id);
        }

        if (!$data) {
            return Redirect::route('pasttime_list')->with('mError', 'Ce temps passé est introuvable !');
        } else {
            return $data;
        }
    }

    public function liste($month = null)
    {
//        if (Input::has('filtre_month') && Input::has('filtre_year')) {
//            Session::put('filtre_pasttime.month', Input::get('filtre_month'));
//            Session::put('filtre_pasttime.year', Input::get('filtre_year'));
//        }
        if (Input::has('filtre_submitted')) {
            if (Input::has('filtre_user_id')) {
                Session::put('filtre_pasttime.user_id', Input::get('filtre_user_id'));
            }
            if (Input::has('filtre_start')) {
                $date_start_explode = explode('/', Input::get('filtre_start'));
                Session::put('filtre_pasttime.start', $date_start_explode[2] . '-' . $date_start_explode[1] . '-' . $date_start_explode[0]);
                if (!Input::has('filtre_user_id')) {
                    Session::forget('filtre_pasttime.user_id');
                }
            }
            if (Input::has('filtre_end')) {
                $date_end_explode = explode('/', Input::get('filtre_end'));
                Session::put('filtre_pasttime.end', $date_end_explode[2] . '-' . $date_end_explode[1] . '-' . $date_end_explode[0]);
            } else {
                Session::put('filtre_pasttime.end', date('Y-m-d'));
            }
            if (Input::has('filtre_toinvoice')) {
                Session::put('filtre_pasttime.filtre_toinvoice', Input::get('filtre_toinvoice'));
            } else {
                Session::put('filtre_pasttime.filtre_toinvoice', false);
            }
        }
        if (Session::has('filtre_pasttime.start')) {
            $date_filtre_start = Session::get('filtre_pasttime.start');
            $date_filtre_end = Session::get('filtre_pasttime.end');
        } else {
            $date_filtre_start = date('Y-m') . '-01';
            $date_filtre_end = date('Y-m') . '-' . date('t', date('m'));
        }

//        if (Session::has('filtre_pasttime.month')) {
//            $date_filtre_start = Session::get('filtre_pasttime.year').'-'.Session::get('filtre_pasttime.month').'-01';
//            $date_filtre_end = Session::get('filtre_pasttime.year').'-'.Session::get('filtre_pasttime.month').'-'.date('t', Session::get('filtre_pasttime.month'));
//        } else {
//            $date_filtre_start = date('Y-m').'-01';
//            $date_filtre_end = date('Y-m').'-'.date('t', Session::get('filtre_pasttime.month'));
//        }

        $recapFilter = false;
        $q = PastTime::whereBetween('date_past', array($date_filtre_start, $date_filtre_end));
        if (Session::get('filtre_pasttime.filtre_toinvoice')) {
            $q->where('invoice_id', 0);
            $q->where('is_free', false);
        }
        if (Auth::user()->role == 'superadmin') {
            if (Session::has('filtre_pasttime.user_id')) {
                $recapFilter = Session::get('filtre_pasttime.user_id');
                $q->whereUserId($recapFilter);
            }
        } else {
            $recapFilter = Auth::user()->id;
            $q->whereUserId(Auth::user()->id);
        }
        $recap = PastTime::Recap($recapFilter, $date_filtre_start, $date_filtre_end);
        $pending_invoice_amount = 0;
        foreach ($recap as $recap_item) {
            $pending_invoice_amount += $recap_item->amount;
        }

        $params = array();
        $params['times'] = $q->orderBy('date_past', 'DESC')->paginate(15);
        $params['recap'] = $recap;
        $params['pending_invoice_amount'] = $pending_invoice_amount;

        $params = array_merge($params, Subscription::getActiveSubscriptionInfos());

        return View::make('pasttime.liste', $params);
    }

    public function add()
    {
        return View::make('pasttime.add');
    }

    public function add_check()
    {
        $validator = Validator::make(Input::all(), PastTime::$rules);
        if (!$validator->fails()) {
            $time = new PastTime;
            $date_past_explode = explode('/', Input::get('date_past'));
            $time->date_past = $date_past_explode[2] . '-' . $date_past_explode[1] . '-' . $date_past_explode[0];
            $dateTime_start = new DateTime($time->date_past);
            $time->time_start = $dateTime_start->format('Y-m-d') . ' ' . Input::get('time_start') . ':00';
            if (Input::get('time_end')) {
                if (Input::get('time_end') <= Input::get('time_start')) {
                    $dateTime_start->modify('+1 day');
                }
                $time->time_end = $dateTime_start->format('Y-m-d') . ' ' . Input::get('time_end') . ':00';
            }
            if (Auth::user()->role == 'superadmin') {
                $time->user_id = Input::get('user_id');
                $time->invoice_id = Input::get('invoice_id');
                $time->is_free = Input::get('is_free');
            } else {
                $time->user_id = Auth::user()->id;
            }
            $time->ressource_id = Input::get('ressource_id');
            $time->comment = Input::get('comment');

            if ($time->save()) {
                return Redirect::route('pasttime_list', $time->id)->with('mSuccess', 'Le temps passé a bien été ajouté');
            } else {
                return Redirect::route('pasttime_add')->with('mError', 'Impossible de créer ce temps passé')->withInput();
            }
        } else {
            return Redirect::route('pasttime_add')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    public function modify($id)
    {
        $time = $this->dataExist($id);

        return View::make('pasttime.modify', array('time' => $time));
    }

    public function modify_check($id)
    {
        $time = $this->dataExist($id);

        $validator = Validator::make(Input::all(), PastTime::$rules);
        if (!$validator->fails()) {
            $date_past_explode = explode('/', Input::get('date_past'));
            $time->date_past = $date_past_explode[2] . '-' . $date_past_explode[1] . '-' . $date_past_explode[0];
            $time->time_start = $time->date_past . ' ' . Input::get('time_start') . ':00';
            if (Input::get('time_end')) {
                $time->time_end = $time->date_past . ' ' . Input::get('time_end') . ':00';
            }
            if (Auth::user()->role == 'superadmin') {
                $time->user_id = Input::get('user_id');
                $time->invoice_id = Input::get('invoice_id');
                $time->is_free = Input::get('is_free');
            } else {
                $time->user_id = Auth::user()->id;
            }
            $time->ressource_id = Input::get('ressource_id');
            $time->comment = Input::get('comment');

            if ($time->save()) {
                return Redirect::route('pasttime_list', $time->id)->with('mSuccess', 'Le temps passé a bien été modifié');
            } else {
                return Redirect::route('pasttime_modify', $time->id)->with('mError', 'Impossible de modifier ce temps passé')->withInput();
            }
        } else {
            return Redirect::route('pasttime_modify', $time->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    public function delete($id)
    {
        if (PastTime::destroy($id)) {
            return Redirect::route('pasttime_list')->with('mSuccess', 'Cette ligne a bien été supprimée');
        } else {
            return Redirect::route('pasttime_list')->with('mError', 'Impossible de supprimer cette ligne !');
        }
    }

    public function cancelFilter(){
        Session::forget('filtre_pasttime.user_id');
        Session::forget('filtre_pasttime.start');
        Session::forget('filtre_pasttime.end');
        Session::forget('filtre_pasttime.filtre_toinvoice');
        return Redirect::route('pasttime_list');
    }
}