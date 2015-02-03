<?php
/**
* Organisation Controller
*/
class OrganisationController extends BaseController
{
	/**
	 * Default template
	 */
	protected $layout = "layouts.master";

	/**
	 * List organisations
	 */
	public function liste()
	{
		$organisations = Organisation::paginate(15);

		$this->layout->content = View::make('organisation.liste', array('organisations' => $organisations));
	}

	/**
	 * Modify organisation
	 */
	public function modify($id)
	{
		$organisation = Organisation::find($id);
		if (!$organisation) {
			return Redirect::route('organisation_list')->with('mError', 'Cet organisme est introuvable !');
		}

		$this->layout->content = View::make('organisation.modify', array('organisation' => $organisation));
	}

	/**
	 * Modify organisation (form)
	 */
	public function modify_check($id)
	{
		$organisation = Organisation::find($id);
		if (!$organisation) {
			return Redirect::route('organisation_list')->with('mError', 'Cet organisme est introuvable !');
		}

		$validator = Validator::make(Input::all(), Organisation::$rules);
		if (!$validator->fails()) {
            $organisation->name = Input::get('name');
            $organisation->address = Input::get('address');
            $organisation->zipcode = Input::get('zipcode');
            $organisation->city = Input::get('city');
            $organisation->country_id = Input::get('country_id');
            $organisation->tva_number = Input::get('tva_number');
            $organisation->code_purchase = Input::get('code_purchase');
			$organisation->code_sale = Input::get('code_sale');

			if ($organisation->save()) {
				return Redirect::route('organisation_modify', $organisation->id)->with('mSuccess', 'Cet organisme a bien été modifié');
			} else {
				return Redirect::route('organisation_modify', $organisation->id)->with('mError', 'Impossible de modifier cet organisme')->withInput();
			}
		} else {
			return Redirect::route('organisation_modify', $organisation->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
		}
	}

	/**
	 * Add organisation
	 */
	public function add()
	{
		$this->layout->content = View::make('organisation.add');
	}

	/**
	 * Add Organisation check
	 */
	public function add_check()
	{
		$validator = Validator::make(Input::all(), Organisation::$rulesAdd);
		if (!$validator->fails()) {
			$organisation = new Organisation(Input::all());

			if ($organisation->save()) {
				return Redirect::route('organisation_modify', $organisation->id)->with('mSuccess', 'L\'organisme a bien été ajouté');
			} else {
				return Redirect::route('organisation_add')->with('mError', 'Impossible de créer cet organisme')->withInput();
			}
		} else {
			return Redirect::route('organisation_add')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
		}
	}

	/**
	 * Add user
	 */
	public function add_user($id)
	{
		$organisation = Organisation::find($id);
		if (!$organisation) {
			return Redirect::route('organisation_list')->with('mError', 'Cet organisme est introuvable !');
		}

		if (Input::get('user_id')) {
			if (!is_array(OrganisationUser::where('user_id', Input::get('user_id'))->where('organisation_id', $organisation->id)->get())) {
				$add = new OrganisationUser;
				$add->user_id = Input::get('user_id');
				$add->organisation_id = $organisation->id;

				if ($add->save()) {
					return Redirect::route('organisation_modify', $organisation->id)->with('mSuccess', 'Cet utilisateur a bien été ajouté dans l\'organisme');
				} else {
					return Redirect::route('organisation_modify', $organisation->id)->with('mError', 'Il y a des erreurs')->withErrors('Impossible d\'ajouter cet utilisateur dans l\'organisme')->withInput();
				}
			} else {
				return Redirect::route('organisation_modify', $organisation->id)->with('mError', 'Il y a des erreurs')->withErrors('Cet utilisateur est déjà présent dans l\'organisme')->withInput();
			}
		} else {
			return Redirect::route('organisation_modify', $organisation->id)->with('mError', 'Il y a des erreurs')->withErrors('Merci de renseigner un utilisateur')->withInput();
		}
	}

    /**
     * User add
     */
    public function user_add($id)
    {
        if (Input::get('organisation_id')) {
            if (!is_array(OrganisationUser::where('user_id', $id)->where('organisation_id', Input::get('organisation_id'))->get())) {
                $add = new OrganisationUser;
                $add->user_id = $id;
                $add->organisation_id = Input::get('organisation_id');

                if ($add->save()) {
                    return Redirect::route('user_modify', $id)->with('mSuccess', 'Cet utilisateur a bien été ajouté dans l\'organisme');
                } else {
                    return Redirect::route('user_modify', $id)->with('mError', 'Il y a des erreurs')->withErrors('Impossible d\'ajouter cet utilisateur dans l\'organisme')->withInput();
                }
            } else {
                return Redirect::route('user_modify', $id)->with('mError', 'Il y a des erreurs')->withErrors('Cet utilisateur est déjà présent dans l\'organisme')->withInput();
            }
        } else {
            return Redirect::route('user_modify', $id)->with('mError', 'Il y a des erreurs')->withErrors('Merci de renseigner un utilisateur')->withInput();
        }
    }

	/**
	 * Delete user
	 */
	public function delete_user($organisation, $id)
	{
		if (OrganisationUser::where('organisation_id', $organisation)->where('user_id', $id)->delete()) {
			return Redirect::route('organisation_modify', $organisation)->with('mSuccess', 'Cet utilisateur a bien été retiré de cet organisme');
		} else {
			return Redirect::route('organisation_modify', $organisation)->with('mError', 'Impossible de retirer cet utilisateur');
		}
	}

    /**
     * Get infos from an organisation (JSON)
     */
    public function json_infos($id)
    {
        $organisation = Organisation::where('id', $id)->get()->lists('fulladdress', 'id');
        return Response::json($organisation);
    }

    /**
     * Json list
     */
    public function json_list()
    {
        if (strlen(Input::get('term')) >= 2) {
            $list = Organisation::where('name', 'LIKE', '%'.Input::get('term').'%')->lists('name', 'id');
        } else {
            $list = array();
        }

        $ajaxArray = array();
        foreach ($list as $key => $value) {
            $ajaxArray[] = array(
                "id" => $key,
                "name" => $value
            );
        }

        return Response::json($ajaxArray);
    }
}