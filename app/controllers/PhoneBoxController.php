<?php

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;


class PhoneBoxController extends BaseController
{
    public function index($location_slug, $key, $box_id)
    {
        return View::make('phonebox.index',
            array(
                'location_slug' => $location_slug,
                'key' => $key,
                'box_id' => $box_id,
            ));
    }

    public function auth($location_slug, $key, $box_id)
    {
        //$user = User::where('personnal_code', Input::get('code'))->first();
        $user = User::where('id', '=', 1)->first();
        if (false && empty($user->personnal_code)) {
            $data = array(
                'status' => 'error',
                'message' => 'Utilisaeur inconnu'
            );
        } else {
            $data = array();
            $data['username'] = $user->fullname;
            $data['picture'] = $user->getAvatarUrl(300);
        }
        $result = new Response();
        $result->headers->set('Content-Type', 'application/json');
        $result->setContent(json_encode($data));
        return $result;
    }
}
