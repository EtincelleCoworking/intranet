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

    /**
     * List countries
     */
    public function liste()
    {
        $devices = Device::orderBy('last_seen_at', 'DESC')->paginate(15);

        return View::make('device.liste', array('devices' => $devices));
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

    public function enableTracking($id){
        $device = Device::findOrFail($id);
        $device->tracking_enabled  = true;
        $device->save();
        return Redirect::route('device_list', 'all')->with('mSuccess', 'Le périphérique a bien été activé');
    }

    public function disableTracking($id){
        $device = Device::findOrFail($id);
        $device->tracking_enabled  = false;
        $device->save();
        return Redirect::route('device_list', 'all')->with('mSuccess', 'Le périphérique a bien été désactivé');
    }
}