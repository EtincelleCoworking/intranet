<?php
/**
* Past Time Controller
*/
class PastTimeController extends BaseController
{
    public function liste($month=null)
    {
        if (Input::has('filtre_month') && Input::has('filtre_year')) {
            Session::put('filtre_pasttime.month', Input::get('filtre_month'));
            Session::put('filtre_pasttime.year', Input::get('filtre_year'));
        }

        if (Session::has('filtre_pasttime.month')) {
            $date_filtre_start = Session::get('filtre_pasttime.year').'-'.Session::get('filtre_pasttime.month').'-01';
            $date_filtre_end = Session::get('filtre_pasttime.year').'-'.Session::get('filtre_pasttime.month').'-'.date('t', Session::get('filtre_pasttime.month'));
        } else {
            $date_filtre_start = date('Y-m').'-01';
            $date_filtre_end = date('Y-m').'-'.date('t', Session::get('filtre_pasttime.month'));
        }

        if (Auth::user()->role == 'member') {
            $times = PastTime::whereBetween('date_past', array($date_filtre_start, $date_filtre_end))->where('user_id', '=', Auth::user()->id)->orderBy('date_past', 'DESC')->paginate(15);
        } else {
            $times = PastTime::whereBetween('date_past', array($date_filtre_start, $date_filtre_end))->orderBy('date_past', 'DESC')->paginate(15);
        }

        return View::make('pasttime.liste', array('times' => $times));
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
            $time->date_past = $date_past_explode[2].'-'.$date_past_explode[1].'-'.$date_past_explode[0];
            $dateTime_start = new DateTime($time->date_past);
            $time->time_start = $dateTime_start->format('Y-m-d').' '.Input::get('time_start').':00';
            if (Input::get('time_end')) { 
                if (Input::get('time_end') <= Input::get('time_start')) {
                    $dateTime_start->modify('+1 day');
                }
                $time->time_end = $dateTime_start->format('Y-m-d').' '.Input::get('time_end').':00'; 
            }
            if (Auth::user()->role == 'superadmin') {
                $time->user_id = Input::get('user_id');
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
        if (Auth::user()->role == 'superadmin') {
            $time = PastTime::find($id);
        } else {
            $time = PastTime::where('user_id', '=', Auth::user()->id)->find($id);
        }
        if (!$time) {
            return Redirect::route('pasttime_list')->with('mError', 'Ce temps passé est introuvable !');
        }

        return View::make('pasttime.modify', array('time' => $time));
    }

    public function modify_check($id)
    {
        if (Auth::user()->role == 'superadmin') {
            $time = PastTime::find($id);
        } else {
            $time = PastTime::where('user_id', '=', Auth::user()->id)->find($id);
        }
        if (!$time) {
            return Redirect::route('pasttime_list')->with('mError', 'Ce temps passé est introuvable !');
        }

        $validator = Validator::make(Input::all(), PastTime::$rules);
        if (!$validator->fails()) {
            $date_past_explode = explode('/', Input::get('date_past'));
            $time->date_past = $date_past_explode[2].'-'.$date_past_explode[1].'-'.$date_past_explode[0];
            $time->time_start = $time->date_past.' '.Input::get('time_start').':00';
            if (Input::get('time_end')) { $time->time_end = $time->date_past.' '.Input::get('time_end').':00'; }
            if (Auth::user()->role == 'superadmin') {
                $time->user_id = Input::get('user_id');
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
}