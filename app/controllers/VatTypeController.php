<?php
/**
* Vat Type Controller
*/
class VatTypeController extends BaseController
{
    /**
     * Default template
     */
    protected $layout = "layouts.master";

    /**
     * List of vats
     */
    public function liste()
    {
        $vats = VatType::paginate(15);

        $this->layout->content = View::make('vat.liste', array('vats' => $vats));
    }

    /**
     * Add a vat
     */
    public function add()
    {
        $this->layout->content = View::make('vat.add');
    }

    /**
     * Add Vat check
     */
    public function add_check()
    {
        $validator = Validator::make(Input::all(), VatType::$rulesAdd);
        if (!$validator->fails()) {
            $vat = new VatType;
            $vat->value = Input::get('value');

            if ($vat->save()) {
                return Redirect::route('vat_modify', $vat->id)->with('mSuccess', 'La vat a bien été ajoutée');
            } else {
                return Redirect::route('vat_add')->with('mError', 'Impossible de créer cette vat')->withInput();
            }
        } else {
            return Redirect::route('vat_add')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    /**
     * Modify vat
     */
    public function modify($id)
    {
        $vat = VatType::find($id);
        if (!$vat) {
            return Redirect::route('vat_list')->with('mError', 'Cette vat est introuvable !');
        }

        $this->layout->content = View::make('vat.modify', array('vat' => $vat));
    }

    /**
     * Modify vat (form)
     */
    public function modify_check($id)
    {
        $vat = VatType::find($id);
        if (!$vat) {
            return Redirect::route('vat_list')->with('mError', 'Cette vat est introuvable !');
        }

        $validator = Validator::make(Input::all(), VatType::$rules);
        if (!$validator->fails()) {
            $vat->value = Input::get('value');
            if ($vat->save()) {
                return Redirect::route('vat_modify', $vat->id)->with('mSuccess', 'Cette vat a bien été modifiée');
            } else {
                return Redirect::route('vat_modify', $vat->id)->with('mError', 'Impossible de modifier cette vat')->withInput();
            }
        } else {
            return Redirect::route('vat_modify', $vat->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }
}