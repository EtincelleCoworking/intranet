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
        $ressources = Ressource::orderBy('ressource_kind_id', 'ASC')->orderBy('location_id', 'ASC')->orderBy('order_index', 'ASC')->paginate(20);
        $getLast = Ressource::orderBy('order_index', 'DESC')->first();
        if ($getLast) {
            $last = $getLast->order_index;
        } else {
            $last = 0;
        }

        return View::make('ressource.liste', array('ressources' => $ressources, 'last' => $last));
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
            $ressource->location_id = $location_id?$location_id:null;
            $ressource->ressource_kind_id = Input::get('ressource_kind_id');

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
            $ressource->booking_background_color = Input::get('booking_background_color');            $ressource->location_id = Input::get('location_id');
            $ressource->location_id = $location_id?$location_id:null;
            $ressource->ressource_kind_id = Input::get('ressource_kind_id');
            $ressource->subscription_id = Input::get('subscription_id')?Input::get('subscription_id'):null;

            if ($ressource->save()) {
                return Redirect::route('ressource_list')->with('mSuccess', 'Cette ressource a bien été modifiée');
            } else {
                return Redirect::route('ressource_modify', $ressource->id)->with('mError', 'Impossible de modifier cette ressource')->withInput();
            }
        } else {
            return Redirect::route('ressource_modify', $ressource->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    /**
     * Order UP
     */
    public function order_up($ressource)
    {
        $ressource = $this->dataExist($ressource);

        $ressource->order_index -= 1;
        $precedent = Ressource::whereOrderIndex($ressource->order_index)->first();

        if ($ressource->order_index > 0) {
            if ($ressource->save()) {
                $precedent->order_index += 1;
                $precedent->save();
                return Redirect::route('ressource_list');
            } else {
                return Redirect::route('ressource_list')->with('mError', 'Impossible de monter cette ressource');
            }
        } else {
            return Redirect::route('ressource_list')->with('mError', 'Impossible de monter cette ressource');
        }
    }

    /**
     * Order DOWN
     */
    public function order_down($ressource)
    {
        $ressource = $this->dataExist($ressource);

        $ressource->order_index += 1;
        $next = Ressource::whereOrderIndex($ressource->order_index)->first();

        if ($ressource->save()) {
            $next->order_index -= 1;
            $next->save();
            return Redirect::route('ressource_list');
        } else {
            return Redirect::route('ressource_list')->with('mError', 'Impossible de descendre cette ressource');
        }
    }
}