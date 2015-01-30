<?php
/**
* Charge Controller
*/
class ChargeController extends BaseController
{

    /**
     * List of charge
     */
    public function liste()
    {
        $charges = Charge::paginate(6);

        return View::make('charge.liste', array('charges' => $charges));
    }

    /**
     * Add charge
     */
    public function add()
    {
        return View::make('charge.add');
    }

    /**
     * Add charge check
     */
    public function add_check()
    {
        $validator = Validator::make(Input::all(), Charge::$rulesAdd);
        if (!$validator->fails()) {
            $date_explode = explode('/', Input::get('date_charge'));

            $charge = new Charge;
            $charge->date_charge = $date_explode[2].'-'.$date_explode[1].'-'.$date_explode[0];

            if (Input::file('document')) {
                $document = time(true).'.'.Input::file('document')->guessClientExtension();
                if (Input::file('document')->move('uploads/charges', $document)) {
                    $charge->document = $document;
                }
            }

            if ($charge->save()) {
                if (Input::get('tags')) {
                    $tags = explode(', ', Input::get('tags'));

                    $tags_keys = array();
                    foreach ($tags as $tag) {
                        if (!array_key_exists($tag, $tags_keys)) {
                            $checkTag = Tag::where('name', '=', $tag)->first();
                            if ($checkTag) {
                                $chargeTag = new ChargeTag;
                                $chargeTag->charge_id = $charge->id;
                                $chargeTag->tag_id = $checkTag->id;

                                if ($chargeTag->save()) {
                                    $tags_keys[$tag] = $tag;
                                }
                            }
                        }
                    }
                }
                return Redirect::route('charge_modify', $charge->id)->with('mSuccess', 'La charge a bien été ajoutée');
            } else {
                return Redirect::route('charge_add')->with('mError', 'Impossible de créer cette charge')->withInput();
            }
        } else {
            return Redirect::route('charge_add')->with('mError', 'Il y a des erreurs')->withInput()->withErrors($validator->messages());
        }
    }

    /**
     * Modify charge
     */
    public function modify($id)
    {
        $charge = Charge::find($id);
        if (!$charge) {
            return Redirect::route('charge_list')->with('mError', 'Cette charge est introuvable !');
        }

        $tags = '';
        foreach ($charge->tags as $k => $tag) {
            if ($k > 0) { $tags .= ','; }
            $tags .= $tag->id;
        }

        return View::make('charge.modify', array('charge' => $charge, 'tags' => $tags));
    }

    /**
     * Modify charge (form)
     */
    public function modify_check($id)
    {
        $charge = Charge::find($id);
        if (!$charge) {
            return Redirect::route('charge_list')->with('mError', 'Cette charge est introuvable !');
        }

        $validator = Validator::make(Input::all(), Charge::$rules);
        if (!$validator->fails()) {
            $date_explode = explode('/', Input::get('date_charge'));

            $charge->date_charge = $date_explode[2].'-'.$date_explode[1].'-'.$date_explode[0];

            if (Input::file('document')) {
                $document = time(true).'.'.Input::file('document')->guessClientExtension();
                if (Input::file('document')->move('uploads/charges', $document)) {
                    if ($charge->document) {
                        unlink(public_path().'/uploads/charges/'.$charge->document);
                    }
                    $charge->document = $document;
                }
            }

            if ($charge->save()) {
                if (Input::get('tags')) {
                    $tags = explode(', ', Input::get('tags'));

                    $tags_keys = array();
                    foreach ($tags as $tag) {
                        if (!array_key_exists($tag, $tags_keys)) {
                            $checkTag = Tag::where('name', '=', $tag)->first();
                            if ($checkTag) {
                                if (!ChargeTag::where('charge_id', '=', $charge->id)->where('tag_id', '=', $checkTag->id)->first()) {
                                    $chargeTag = new ChargeTag;
                                    $chargeTag->charge_id = $charge->id;
                                    $chargeTag->tag_id = $checkTag->id;

                                    if ($chargeTag->save()) {
                                        $tags_keys[$tag] = $tag;
                                    }
                                } else {
                                    $tags_keys[$tag] = $tag;
                                }
                            }
                        }
                    }
                }
                return Redirect::route('charge_modify', $charge->id)->with('mSuccess', 'Cette charge a bien été modifiée');
            } else {
                return Redirect::route('charge_modify', $charge->id)->with('mError', 'Impossible de modifier cette charge')->withInput();
            }
        } else {
            return Redirect::route('charge_modify', $charge->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
        }
    }

    /**
     * Delete a charge
     */
    public function delete($id)
    {
        $charge = Charge::find($id);
        if (!$charge) {
            return Redirect::route('charge_list')->with('mError', 'Cette charge est introuvable !');
        }

        ChargeTag::where('charge_id', '=', $id)->delete();
        ChargeItem::where('charge_id', '=', $id)->delete();
        if (Charge::destroy($id)) {
            if ($charge->document) {
                unlink(public_path().'/uploads/charges/'.$charge->document);
            }
            return Redirect::route('charge_list')->with('mSuccess', 'La charge a bien été supprimée');
        } else {
            return Redirect::route('charge_modify', $id)->with('mError', 'Impossible de supprimer cette charge');
        }
    }
}