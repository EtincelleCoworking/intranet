<?php
/**
* Ressource Controller
*/
class RessourceController extends BaseController
{
    /**
     * Default template
     */
    protected $layout = "layouts.master";

    /**
     * List of ressources
     */
    public function liste()
    {
        $ressources = Ressource::paginate(15);

        $this->layout->content = View::make('ressource.liste', array('ressources' => $ressources));
    }

    /**
     * Add a ressource
     */
    public function add()
    {
        $this->layout->content = View::make('ressource.add');
    }

    /**
     * Add Ressource check
     */
    public function add_check()
    {
        $validator = Validator::make(Input::all(), Ressource::$rulesAdd);
        if (!$validator->fails()) {
            $ressource = new Ressource;
            $ressource->name = Input::get('name');

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
        $ressource = Ressource::find($id);
        if (!$ressource) {
            return Redirect::route('ressource_list')->with('mError', 'Cette ressource est introuvable !');
        }

        $this->layout->content = View::make('ressource.modify', array('ressource' => $ressource));
    }

    /**
     * Modify ressource (form)
     */
    public function modify_check($id)
    {
        $ressource = Ressource::find($id);
        if (!$ressource) {
            return Redirect::route('ressource_list')->with('mError', 'Cette ressource est introuvable !');
        }

        $validator = Validator::make(Input::all(), Ressource::$rules);
        if (!$validator->fails()) {
            $ressource->name = Input::get('name');
            if ($ressource->save()) {
                return Redirect::route('ressource_modify', $ressource->id)->with('mSuccess', 'Cette ressource a bien été modifiée');
            } else {
                return Redirect::route('ressource_modify', $ressource->id)->with('mError', 'Impossible de modifier cette ressource')->withInput();
            }
        } else {
            return Redirect::route('ressource_modify', $ressource->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }
}