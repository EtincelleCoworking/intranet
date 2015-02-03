<?php
/**
* Charge Controller
*/
class ChargeController extends BaseController
{

    /**
     * List of charge
     */
    public function liste($filtre)
    {
        $setDate = new DateTime();
        $date_now = $setDate->format('Y-m-d');
        $setDate->modify('+7 days');
        $date_deadline = $setDate->format('Y-m-d');
        switch ($filtre) {
            case 'all':
                $charges = Charge::orderBy('date_charge', 'DESC')->paginate(15);
                break;

            case 'deadline_close':
                $charges = Charge::whereBetween('deadline', array($date_now, $date_deadline))->whereNotNull('deadline')->whereNull('date_payment')->orderBy('date_charge', 'DESC')->paginate(15);
                break;

            case 'deadline_exceeded':
                $charges = Charge::where('deadline', '<', $date_now)->whereNotNull('deadline')->whereNull('date_payment')->orderBy('date_charge', 'DESC')->paginate(15);
                break;
        }

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
            $date_payment_explode = explode('/', Input::get('date_payment'));
            $deadline_explode = explode('/', Input::get('deadline'));

            $charge = new Charge;
            $charge->date_charge = $date_explode[2].'-'.$date_explode[1].'-'.$date_explode[0];
            if (Input::get('date_payment')) { $charge->date_payment = $date_payment_explode[2].'-'.$date_payment_explode[1].'-'.$date_payment_explode[0]; }
            if (Input::get('deadline')) { $charge->deadline = $deadline_explode[2].'-'.$deadline_explode[1].'-'.$deadline_explode[0]; }

            if (Input::file('document')) {
                $document = time(true).'.'.Input::file('document')->guessClientExtension();
                if (Input::file('document')->move('uploads/charges', $document)) {
                    $charge->document = $document;
                }
            }

            if ($charge->save()) {
                if (Input::get('tags')) {
                    $tags = Input::get('tags');

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
                            } else if (trim($tag) != '') {
                                $new_tag = new Tag;
                                $new_tag->name = $tag;
                                if ($new_tag->save()) {
                                    $chargeTag = new ChargeTag;
                                    $chargeTag->charge_id = $charge->id;
                                    $chargeTag->tag_id = $new_tag->id;

                                    if ($chargeTag->save()) {
                                        $tags_keys[$tag] = $tag;
                                    }
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
            return Redirect::route('charge_list', 'all')->with('mError', 'Cette charge est introuvable !');
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
            return Redirect::route('charge_list', 'all')->with('mError', 'Cette charge est introuvable !');
        }

        $validator = Validator::make(Input::all(), Charge::$rules);
        if (!$validator->fails()) {
            $date_explode = explode('/', Input::get('date_charge'));
            $date_payment_explode = explode('/', Input::get('date_payment'));
            $deadline_explode = explode('/', Input::get('deadline'));

            $charge->date_charge = $date_explode[2].'-'.$date_explode[1].'-'.$date_explode[0];
            if (Input::get('date_payment')) { $charge->date_payment = $date_payment_explode[2].'-'.$date_payment_explode[1].'-'.$date_payment_explode[0]; }
            if (Input::get('deadline')) { $charge->deadline = $deadline_explode[2].'-'.$deadline_explode[1].'-'.$deadline_explode[0]; }

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
                    $tags = Input::get('tags');

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
                            } else if (trim($tag) != '') {
                                $new_tag = new Tag;
                                $new_tag->name = $tag;
                                if ($new_tag->save()) {
                                    $chargeTag = new ChargeTag;
                                    $chargeTag->charge_id = $charge->id;
                                    $chargeTag->tag_id = $new_tag->id;

                                    if ($chargeTag->save()) {
                                        $tags_keys[$tag] = $tag;
                                    }
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
            return Redirect::route('charge_list', 'all')->with('mError', 'Cette charge est introuvable !');
        }

        ChargeTag::where('charge_id', '=', $id)->delete();
        ChargeItem::where('charge_id', '=', $id)->delete();
        if (Charge::destroy($id)) {
            if ($charge->document) {
                unlink(public_path().'/uploads/charges/'.$charge->document);
            }
            return Redirect::route('charge_list', 'all')->with('mSuccess', 'La charge a bien été supprimée');
        } else {
            return Redirect::route('charge_modify', $id)->with('mError', 'Impossible de supprimer cette charge');
        }
    }
}