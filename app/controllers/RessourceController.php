<?php

/**
 * Ressource Controller
 */
class RessourceController extends BaseController
{
    /**
     * Verify if exist
     */
    private function dataExist($id)
    {
        $data = Ressource::find($id);
        if (!$data) {
            return Redirect::route('ressource_list')->with('mError', 'Cette ressource est introuvable !');
        } else {
            return $data;
        }
    }

    public function liste()
    {
        $ressources = Ressource::join('locations', 'location_id', '=', 'locations.id')
            ->join('cities', 'city_id', '=', 'cities.id')
            ->join('ressource_kind', 'ressource_kind_id', '=', 'ressource_kind.id')
            ->orderBy('ressource_kind.order_index', 'ASC')
            ->orderBy('cities.name', 'ASC')
            ->orderBy('locations.name', 'ASC')
            ->orderBy('order_index', 'ASC')
            ->select('ressources.*')
            ->get();
        $data = array();
        foreach ($ressources as $ressource) {
            $data[$ressource->kind->name][] = $ressource;
        }
        return View::make('ressource.liste', array('data' => $data));
    }

    /**
     * Add a ressource
     */
    public function add()
    {
        $ressource = Ressource::orderBy('order_index', 'DESC')->first();
        if ($ressource) {
            $last = $ressource->order_index + 1;
        } else {
            $last = 1;
        }

        return View::make('ressource.add', array('last' => $last));
    }

    /**
     * Add Ressource check
     */
    public function add_check()
    {
        $validator = Validator::make(Input::all(), Ressource::$rulesAdd);
        if (!$validator->fails()) {
            $location_id = Input::get('location_id');
            $ressource = new Ressource;
            $ressource->name = Input::get('name');
            $ressource->order_index = Input::get('order_index');
            $ressource->amount = Input::get('amount');
            $ressource->description = Input::get('description');
            $ressource->url = Input::get('url');
            $ressource->is_bookable = (bool)Input::get('is_bookable');
            $ressource->booking_background_color = Input::get('booking_background_color');
            $ressource->location_id = $location_id ? $location_id : null;
            $ressource->ressource_kind_id = Input::get('ressource_kind_id');
            $ressource->sales_presentation = Input::get('sales_presentation');

            if ($ressource->save()) {
                return Redirect::route('ressource_modify', $ressource->id)->with('mSuccess', 'La ressource a bien été ajoutée');
            } else {
                return Redirect::route('ressource_add')->with('mError', 'Impossible de créer cette ressource')->withInput();
            }
        } else {
            return Redirect::route('ressource_add')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    /**
     * Modify ressource
     */
    public function modify($id)
    {
        $ressource = $this->dataExist($id);

        return View::make('ressource.modify', array('ressource' => $ressource));
    }

    /**
     * Modify ressource (form)
     */
    public function modify_check($id)
    {
        $ressource = $this->dataExist($id);

        $validator = Validator::make(Input::all(), Ressource::$rules);
        if (!$validator->fails()) {
            $location_id = Input::get('location_id');
            $ressource->name = Input::get('name');
            $ressource->order_index = Input::get('order_index');
            $ressource->amount = Input::get('amount');
            $ressource->description = Input::get('description');
            $ressource->url = Input::get('url');
            $ressource->is_bookable = (bool)Input::get('is_bookable');
            $ressource->booking_background_color = Input::get('booking_background_color');
            $ressource->location_id = Input::get('location_id');
            $ressource->location_id = $location_id ? $location_id : null;
            $ressource->ressource_kind_id = Input::get('ressource_kind_id');
            $ressource->subscription_id = Input::get('subscription_id') ? Input::get('subscription_id') : null;
            $ressource->sales_presentation = Input::get('sales_presentation') ;
            $ressource->has_paper_summary = (bool)Input::get('has_paper_summary');
            $ressource->intercom_enabled = (bool)Input::get('intercom_enabled');

            if ($ressource->save()) {
                return Redirect::route('ressource_list')->with('mSuccess', 'Cette ressource a bien été modifiée');
            } else {
                return Redirect::route('ressource_modify', $ressource->id)->with('mError', 'Impossible de modifier cette ressource')->withInput();
            }
        } else {
            return Redirect::route('ressource_modify', $ressource->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

}