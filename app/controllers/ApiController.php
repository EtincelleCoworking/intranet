<?php

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;

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
}
