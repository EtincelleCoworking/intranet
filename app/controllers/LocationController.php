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
        $items = Location::orderBy('enabled', 'DESC')->orderBy('city_id', 'ASC')->get();
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
            $location->name = Input::get('name') ? Input::get('name') : null;
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

    public function show($location_slug)
    {
        $location = Location::where('slug', '=', $location_slug)->first();
        $equipments = Equipment::where('location_id', '=', $location->id)
            ->orderBy('is_critical', 'DESC')
//            ->orderBy('order_index', 'ASC')
            ->get();
        return View::make('location.show', array(
            'location' => $location,
            'equipments' => $equipments,
        ));
    }

}