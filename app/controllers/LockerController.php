<?php

class LockerController extends \BaseController
{
    public function index()
    {
        $lockers = Locker::join('locker_history', 'locker.current_usage_id', '=', 'locker_history.id')
            ->where('locker_history.user_id', '=', Auth::id())
            ->with('current_usage')
            ->orderBy('locker_cabinet_id', 'ASC')
            ->orderBy('name', 'ASC')
            ->select('locker.*')
            ->get();
        return View::make('locker.index', array(
                'lockers' => $lockers
            )
        );
    }

    public function admin($location_id)
    {
//        $cabinets = LockerCabinet::join('locations', 'locations.id', '=', 'locker_cabinet.locker_id')
//            ->where('locations.city_id', '=', Auth::user()->location->city_id)->orderBy('name', 'asc')->with('lockers')->get();
        $cabinets = LockerCabinet::where('location_id', '=', $location_id)
            ->orderBy('name', 'asc')->with('lockers', 'lockers.current_usage')->get();

        return View::make('locker.admin', array(
                'location' => Location::find($location_id),
                'cabinets' => $cabinets
            )
        );
    }

    public function pdf($location_id)
    {
        $pages = array();

        $cabinets = LockerCabinet::where('location_id', '=', $location_id)
            ->orderBy('name', 'asc')->with('lockers', 'lockers.current_usage')->get();

        foreach ($cabinets as $cabinet) {
            foreach ($cabinet->lockers as $locker) {
                $html = <<<EOS
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/></head>
    <title>%cabinet% - %locker%</title>
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
    
    p{
    font-size: 20pt;
    }
</style>
</head>
<body>
<div class="header">
    <img src="http://www.coworking-toulouse.com/wp-content/uploads/2015/04/etincelle-coworking-400x400.gif" height="85" width="85" style="float: right" />
    <h1>%locker%</h1>
</div>
<div class="page">
    <p>Ce casier est à ta disposition pour laisser tes affaires.</p>
    <p>Afin de nous permettre de mieux comprendre comment il est utilisé et de mieux gérer les besoins d’ajouts ou de réguler les usages, merci de nous signaler que tu viens de le prendre en activant le QR code ci-dessous (la plupart des smartphones le détectent si tu essayes de le prendre en photo).</p>

    <img src="%locker_qrcode_url%" height="500" width="500" style=" display: block; margin-left: auto; margin-right: auto; width: 50%;" />
    
    <p>Quand tu n’en as plus besoin, merci de signaler que tu viens de le libérer en scannant à nouveau ce QRcode ou directement depuis ton intranet dans la section "Casier".</p>
    <p>Si tu as besoin d'aide, contacte un membre de l'équipe dans la zone d’accueil ou au 05 64 88 01 30 (renvoyé sur nos téléphones portables).</p>
    
</div>
<div class="footer">
    <small>%cabinet% - %locker%</small>
</div>
<div class="page-break"></div>
</body></html>
EOS;
                $macros = array(
                    '%locker%' => $locker->name,
                    '%cabinet%' => $cabinet->name,
                    '%locker_qrcode_url%' => public_path(sprintf('lockers/%d_%s.png', $locker->id, $locker->secret)) ,
                );

                $filename = sprintf('%s/lockers/%d_%s.png', public_path(), $locker->id, $locker->secret);
                $folder = dirname($filename);
                if(!file_exists($folder)){
                    mkdir($folder, 0777);
                }
                QRcode::png(URL::route('locker_toggle', array('id' => $locker->id, 'secret' => $locker->secret)), $filename, QR_ECLEVEL_H, 10, 0);

                $html = str_replace(array_keys($macros), array_values($macros), $html);
                $pages[] = $html;
            }
        }

        $pdf = App::make('snappy.pdf');
        $output = $pdf->getOutputFromHtml($pages,
            array(
                //'orientation' => 'Landscape',
                'default-header' => false));
        $result = new \Illuminate\Http\Response($output, 200, array(
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => sprintf('filename="%s_%s.pdf"', $cabinet->name, $locker->name)));
        return $result;
    }

    public function history($id)
    {
        $locker = Locker::find($id);

        $history = LockerHistory::where('locker_id', '=', $locker->id)
            ->orderBy('taken_at', 'DESC')
            ->with('user')
            ->get();

        return View::make('locker.history', array(
                'locker' => $locker,
                'history' => $history
            )
        );
    }


    public function release($id)
    {
        $locker = Locker::where('id', '=', $id)->with('current_usage')->first();
        if (!$locker) {
            return Redirect::route('locker')->with('mError', 'Impossible de trouver le casier correspondant');
        }

        if (!$locker->current_usage) {
            return Redirect::route('locker')->with('mError', 'Ce casier est déjà libre');
        }

        if ($locker->current_usage->user_id != Auth::id()) {
            return Redirect::route('locker')->with('mError', 'Vous ne pouvez pas libérer le casier d\'un autre utilisateur');
        }
        $locker->current_usage->released_at = date('Y-m-d H:i:s');
        $locker->current_usage->save();
        $locker->current_usage_id = null;
        $locker->save();

        return Redirect::route('locker')->with('mSuccess', sprintf('Le casier %s a été libéré', $locker->name));

    }


    public function toggle($id, $secret)
    {
        $locker = Locker::where('id', '=', $id)
            ->where('secret', '=', $secret)
            ->with('current_usage')->first();

        if (!$locker) {
            return Redirect::route('locker')->with('mError', 'Impossible de trouver le casier correspondant');
        }
        if ($locker->current_usage) {
            $locker->current_usage->released_at = date('Y-m-d H:i:s');
            $locker->current_usage->save();
            $locker->current_usage_id = null;
            $locker->save();

            return Redirect::route('locker')->with('mSuccess', sprintf('Le casier %s a été libéré', $locker->name));
        }

        $locker_history = new LockerHistory();
        $locker_history->taken_at = date('Y-m-d H:i:s');
        $locker_history->user_id = Auth::id();
        $locker_history->locker_id = $locker->id;
        $locker_history->save();

        $locker->current_usage_id = $locker_history->id;
        $locker->save();

        return Redirect::route('locker')->with('mSuccess', sprintf('Le casier %s vous a été assigné', $locker->name));
    }
}
