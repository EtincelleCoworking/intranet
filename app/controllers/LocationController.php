<?php

/**
 * Ressource Controller
 */
class LocationController extends BaseController
{
    /**
     * Verify if exist
     */
    private function dataExist($id)
    {
        $data = Location::find($id);
        if (!$data) {
            return Redirect::route('location_list')->with('mError', 'Ce site est introuvable !');
        } else {
            return $data;
        }
    }

    public function liste()
    {
        $items = Location::get();
        return View::make('location.liste', array('items' => $items));
    }

    /**
     * Modify ressource
     */
    public function modify($id)
    {
        $item = $this->dataExist($id);

        return View::make('location.modify', array('location' => $item));
    }

    /**
     * Modify ressource (form)
     */
    public function modify_check($id)
    {
        $location = $this->dataExist($id);

        $validator = Validator::make(Input::all(), Location::$rules);
        if (!$validator->fails()) {
            $location->name = Input::get('name');
            $location->city_id = Input::get('city_id');
            $location->coworking_capacity = Input::get('coworking_capacity');
            $location->default_business_terms = Input::get('default_business_terms');
            $location->sales_presentation = Input::get('sales_presentation');
            $location->enabled = (bool)Input::get('enabled');

            if ($location->save()) {
                return Redirect::route('location_list')->with('mSuccess', 'Ce site a bien été modifiée');
            } else {
                return Redirect::route('location_modify', $location->id)->with('mError', 'Impossible de modifier ce site')->withInput();
            }
        } else {
            return Redirect::route('location_modify', $location->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

}