<?php

use Illuminate\Http\Response;

class CheckinController extends BaseController
{
    const CACHE_KEY_AVAILABILITY = 'checkin_availability';
    const CACHE_KEY_STATUS = 'checkin_status';

    public function start()
    {
        $timesheet = Auth::user()->getActiveTimesheet();
        if ($timesheet) {
            return Redirect::route('dashboard')->with('mError', 'Une session est déjà commencée');
        }

        $timesheet = new PastTime();
        $timesheet->user_id = Auth::id();
        $timesheet->ressource_id = Ressource::TYPE_COWORKING;
        $timesheet->date_past = new DateTime();
        $timesheet->time_start = new DateTime(date('Y-m-d H:i:00', floor(time() / 300) * 300));
        $timesheet->location_id = Auth::user()->default_location_id;
        $timesheet->save();

        return Redirect::route('dashboard')->with('mSuccess', 'Le compteur a été démarré');

    }

    public function stop()
    {
        $timesheet = Auth::user()->getActiveTimesheet();
        if (!$timesheet) {
            return Redirect::route('dashboard')->with('mError', 'Aucune session n\'a commencée');
        }

        $timesheet->time_end = new DateTime(date('Y-m-d H:i:00', floor(time() / 300) * 300));
        $timesheet->save();

        return Redirect::route('dashboard')->with('mSuccess', 'Le compteur a été arrêté');

        //return Redirect::route('pasttime_modify', $timesheet->id);
    }


    public function status()
    {
        $timesheet = Auth::user()->getActiveTimesheet();
        if (!$timesheet) {
            App::abort(404);
        }

        return new Response($timesheet->getCurrentDuration());
    }


}
