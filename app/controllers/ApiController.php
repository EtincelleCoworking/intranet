<?php

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;

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

        $json = json_decode(Request::getContent(), true);
        $macs = array();
        foreach ($json as $item) {
            if ($item['lastSeen'] > date('Y-m-d')) {
                $macs[] = strtolower($item['mac']);
            }
        }
        //var_dump($macs);
        $devices = array();
        foreach (Device::whereIn('mac', $macs)->get() as $device) {
            $devices[$device->mac] = $device;
        }
        // var_dump(array_keys($devices));

        foreach ($json as $item) {
            $item['mac'] = strtolower($item['mac']);
            if (!isset($devices[$item['mac']])) {
                if (isset($item['name'])) {
                    // Create the device because it is connected to the WIFI
                    $device = new Device();
                    $device->mac = $item['mac'];
                    $device->name = $item['name'];
                    $device->save();
                    $devices[$item['mac']] = $device;
                }
            }
            if (isset($devices[$item['mac']])) {
                $device = $devices[$item['mac']];
                if ($device->tracking_enabled) {
                    $timeslot = null;
                    if ($device->user_id) {
                        $timeslot = PastTime::where('user_id', '=', $device->user_id)
                            ->where('date_past', '=', date('Y-m-d', strtotime($item['lastSeen'])))
                            ->where('time_start', '<', date('Y-m-d H:i:s', strtotime($item['lastSeen'])))
                            ->where(function ($query) use ($item) {
                                $query->where('time_end', '>', date('Y-m-d H:i:s', strtotime('-60 minutes', strtotime($item['lastSeen']))))
                                    ->orWhereNull('time_end');
                            })
                            ->orderBy('time_start', 'DESC')
                            ->first();
                    }
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
                    $timeslot->time_end = date('Y-m-d H:i:s', $this->ceilTime($item['lastSeen']) + 10 * 60);
                    //$timeslot->auto_updated = true;
                    $timeslot->save();

                    if ($triggerUserShown) {
                        Event::fire('user.shown', array($timeslot, $location));
                    }

                    $device_seen = DeviceSeen::where('device_id', '=', $device->id)
                        ->where('last_seen_at', '=', date('Y-m-d H:i:s', strtotime($item['lastSeen'])))
                        ->first();
                    if (!$device_seen) {
                        $device_seen = new DeviceSeen();
                        $device_seen->device_id = $device->id;
                        $device_seen->last_seen_at = date('Y-m-d H:i:s', strtotime($item['lastSeen']));
                        $device_seen->save();
                    }
                }
                $device->last_seen_at = date('Y-m-d H:i:s', strtotime($item['lastSeen']));
                if (isset($item['name'])) {
                    $device->name = $item['name'];
                }
                $device->save();
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
}
