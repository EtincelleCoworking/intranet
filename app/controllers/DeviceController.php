<?php

/**
 * Country Controller
 */
class DeviceController extends BaseController
{
    /**
     * Verify if exist
     */
    private function dataExist($id)
    {
        $data = Device::find($id);
        if (!$data) {
            return Redirect::route('device_list')->with('mError', 'Ce périphérique est introuvable !');
        } else {
            return $data;
        }
    }

    public function cancelFilter()
    {
        Session::forget('filtre_device.user_id');
        Session::forget('filtre_device.organisation_id');
        Session::forget('filtre_device.city_id');
        return Redirect::route('device_list');
    }

    /**
     * List countries
     */
    public function liste()
    {
        if (Input::has('filtre_submitted')) {
            if (Input::has('filtre_location_id')) {
                Session::put('filtre_device.location_id', Input::get('filtre_location_id'));
            } else {
                Session::forget('filtre_device.city_id');
            }
            if (Input::has('filtre_user_id')) {
                Session::put('filtre_device.user_id', Input::get('filtre_user_id'));
            } else {
                Session::forget('filtre_device.user_id');
            }
            if (Input::has('filtre_ip')) {
                Session::put('filtre_device.ip', Input::get('filtre_ip'));
            } else {
                Session::forget('filtre_device.ip');
            }
        }
            $sql = '';
        if (Session::has('filtre_device.user_id')) {
            $sql .= sprintf(' AND devices.user_id = %d', Session::get('filtre_device.user_id'));
        }
        if (Session::has('filtre_device.location_id')) {
            $sql .= sprintf(' AND locations.id = %d', Session::get('filtre_device.location_id'));
        }
        if (Session::has('filtre_device.ip')) {
            $sql .= sprintf(' AND ((devices.ip LIKE "%%%s%%") OR (devices.mac LIKE "%%%s%%"))', Session::get('filtre_device.ip'), Session::get('filtre_device.ip'));
        }


        $pageNo = max(0, Input::get('page') - 1);
        $itemPerPage = 15;
        $devices = DB::select(DB::raw('select 
devices.id,
devices.tracking_enabled, 
users.id as user_id, 
concat(users.firstname, \' \', users.lastname) as username,
devices.mac,
devices.ip,
devices.name,
devices.brand,
cities.name as city,
locations.name as location,
devices.last_seen_at
 
from devices 
LEFT OUTER join users on devices.user_id = users.id
LEFT OUTER join devices_seen on devices.id = devices_seen.device_id AND devices.last_seen_at = devices_seen.last_seen_at
LEFT OUTER join locations on devices_seen.location_id = locations.id
LEFT OUTER join cities on locations.city_id = cities.id WHERE 1 '
            .$sql
            . sprintf('
group by devices.id
order by devices_seen.last_seen_at DESC
LIMIT %d, %d
', $itemPerPage * $pageNo, $itemPerPage)));

        $devicesCount = DB::selectOne(DB::raw('select count(devices.id) as cnt from devices  
LEFT OUTER join devices_seen on devices.id = devices_seen.device_id AND devices.last_seen_at = devices_seen.last_seen_at
LEFT OUTER join locations on devices_seen.location_id = locations.id
WHERE 1 '
            .$sql));

        //var_dump($devicesCount); exit;
        $pager = Paginator::make($devices, $devicesCount->cnt, $itemPerPage);

        return View::make('device.liste', array('devices' => $pager));
    }

    /**
     * Modify country
     */
    public function modify($id)
    {
        $device = $this->dataExist($id);

        return View::make('device.add', array('device' => $device));
    }

    /**
     * Modify country (form)
     */
    public function modify_check($id)
    {
        $device = $this->dataExist($id);

        $validator = Validator::make(Input::all(), Country::$rules);
        if (!$validator->fails()) {
            $update_previous_timeslots = !$device->user_id;
            if (Input::get('user_id')) {
                $device->user_id = Input::get('user_id');
            } else {
                $device->user_id = null;
            }
            $device->mac = strtolower(Input::get('mac'));
            $device->name = Input::get('name');

            if ($device->save()) {
                if ($update_previous_timeslots) {
                    DB::table('past_times')->where('device_id', '=', $device->id)->where('user_id', '=', 0)->update(array('user_id' => $device->user_id));
                }

                return Redirect::route('device_list', $device->id)->with('mSuccess', 'Ce périphérique a bien été modifié');
            } else {
                return Redirect::route('device_modify', $device->id)->with('mError', 'Impossible de modifier ce périphérique')->withInput();
            }
        } else {
            return Redirect::route('device_modify', $device->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    /**
     * Add country
     */
    public function add()
    {
        return View::make('device.add');
    }

    /**
     * Add Country check
     */
    public function add_check()
    {
        $validator = Validator::make(Input::all(), Country::$rulesAdd);
        if (!$validator->fails()) {
            $device = new Device(Input::all());
            $device->mac = strtolower($device->mac);

            if ($device->save()) {
                return Redirect::route('device_list', $device->id)->with('mSuccess', 'Le périphérique a bien été modifié');
            } else {
                return Redirect::route('device_add')->with('mError', 'Impossible de créer ce périphérique')->withInput();
            }
        } else {
            return Redirect::route('device_add')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    /**
     * Delete a charge
     */
    public function delete($id)
    {
        if (Device::destroy($id)) {
            return Redirect::route('device_list', 'all')->with('mSuccess', 'Le périphérique a bien été supprimé');
        } else {
            return Redirect::route('device_list', 'all')->with('mError', 'Impossible de supprimer ce périphérique');
        }
    }

    public function enableTracking($id)
    {
        $device = Device::findOrFail($id);
        $device->tracking_enabled = true;
        $device->save();
        return Redirect::route('device_list', 'all')->with('mSuccess', 'Le périphérique a bien été activé');
    }

    public function disableTracking($id)
    {
        $device = Device::findOrFail($id);
        $device->tracking_enabled = false;
        $device->save();
        return Redirect::route('device_list', 'all')->with('mSuccess', 'Le périphérique a bien été désactivé');
    }
}