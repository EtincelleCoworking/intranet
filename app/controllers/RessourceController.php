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
        $ressources = Ressource::orderBy('order_index', 'ASC')->paginate(15);
        $getLast = Ressource::orderBy('order_index', 'DESC')->first();
        if ($getLast) {
            $last = $getLast->order_index;
        } else {
            $last = 0;
        }

        $this->layout->content = View::make('ressource.liste', array('ressources' => $ressources, 'last' => $last));
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

        $this->layout->content = View::make('ressource.add', array('last' => $last));
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
            $ressource->order_index = Input::get('order_index');

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
            $ressource->order_index = Input::get('order_index');
            if ($ressource->save()) {
                return Redirect::route('ressource_modify', $ressource->id)->with('mSuccess', 'Cette ressource a bien été modifiée');
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
        $ressource = Ressource::find($ressource);

        $ressource->order_index -= 1;

        if ($ressource->order_index > 0) {
            if ($ressource->save()) {
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
        $ressource = Ressource::find($ressource);

        $ressource->order_index += 1;

        if ($ressource->save()) {
            return Redirect::route('ressource_list');
        } else {
            return Redirect::route('ressource_list')->with('mError', 'Impossible de descendre cette ressource');
        }
    }
}