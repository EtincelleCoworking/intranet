<?php
/**
* Country Controller
*/
class CountryController extends BaseController
{
    /**
     * Default template
     */
    protected $layout = "layouts.master";

    /**
     * List countries
     */
    public function liste()
    {
        $countries = Country::paginate(15);

        $this->layout->content = View::make('country.liste', array('countries' => $countries));
    }

    /**
     * Modify country
     */
    public function modify($id)
    {
        $country = Country::find($id);
        if (!$country) {
            return Redirect::route('country_list')->with('mError', 'Ce pays est introuvable !');
        }

        $this->layout->content = View::make('country.modify', array('country' => $country));
    }

    /**
     * Modify country (form)
     */
    public function modify_check($id)
    {
        $country = Country::find($id);
        if (!$country) {
            return Redirect::route('organisation_list')->with('mError', 'Ce pays est introuvable !');
        }

        $validator = Validator::make(Input::all(), Country::$rules);
        if (!$validator->fails()) {
            $country->name = Input::get('name');

            if ($country->save()) {
                return Redirect::route('country_modify', $country->id)->with('mSuccess', 'Ce pays a bien été modifié');
            } else {
                return Redirect::route('country_modify', $country->id)->with('mError', 'Impossible de modifier ce pays')->withInput();
            }
        } else {
            return Redirect::route('country_modify', $country->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    /**
     * Add country
     */
    public function add()
    {
        $this->layout->content = View::make('country.add');
    }

    /**
     * Add Country check
     */
    public function add_check()
    {
        $validator = Validator::make(Input::all(), Country::$rulesAdd);
        if (!$validator->fails()) {
            $country = new Country(Input::all());

            if ($country->save()) {
                return Redirect::route('country_modify', $country->id)->with('mSuccess', 'Le pays a bien été ajouté');
            } else {
                return Redirect::route('country_add')->with('mError', 'Impossible de créer ce pays')->withInput();
            }
        } else {
            return Redirect::route('country_add')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }
}