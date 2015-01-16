<?php
/**
* UserController Class
*/
class UserController extends BaseController
{
	/**
	 * Default template
	 */
	protected $layout = "layouts.master";

	/**
	 * Login page for users
	 */
	public function login()
	{
		$this->layout->content = View::make('user.login');
	}

	/**
	 * Login check after post form
	 */
	public function login_check()
	{
		$user = array(
			'email' => Input::get('email'),
			'password' => Input::get('password')
		);

		if (Auth::attempt($user, Input::get('remember'))) {
			return Redirect::intended(URL::route('dashboard'));
		} else {
			return Redirect::route('user_login')->with('mError', 'Connexion impossible, merci de vérifier vos informations')->withInput();
		}
	}

	/**
	 * Disconnect user
	 */
	public function logout()
	{
		Auth::logout();
		return Redirect::route('user_login')->with('mSuccess', 'Votre déconnexion a bien été effectuée !');
	}

	/**
	 * Dashboard
	 */
	public function dashboard()
	{
		if (!Auth::user()) {
			return Redirect::route('user_login');
		}

		$this->layout->content = View::make('user.dashboard');
	}

	/**
	 * List users
	 */
	public function liste()
	{
		$users = User::paginate(15);

		$this->layout->content = View::make('user.liste', array('users' => $users));
	}

	/**
	 * Modify user
	 */
	public function modify($id)
	{
		$user = User::find($id);
		if (!$user) {
			return Redirect::route('user_list')->with('mError', 'Cet utilisateur est introuvable !');
		}

		$this->layout->content = View::make('user.modify', array('user' => $user));
	}

	/**
	 * Modify user (form)
	 */
	public function modify_check($id)
	{
		$user = User::find($id);
		if (!$user) {
			return Redirect::route('user_list')->with('mError', 'Cet utilisateur est introuvable !');
		}

		$validator = Validator::make(Input::all(), User::$rules);
		if (!$validator->fails()) {
			// Vérifier que l'adresse email soit unique (peut-être à améliorer... directement dans l'entity ?)
			$check = User::where('email', '=', $user->email)->where('id', '!=', $user->id)->first();
			if (!$check) {
				$user->email = Input::get('email');
                $user->firstname = Input::get('firstname');
				$user->lastname = Input::get('lastname');
				if (Input::get('password')) {
					$user->password = Hash::make(Input::get('password'));
				}
                $user->bio_short = Input::get('bio_short');
                $user->bio_long = Input::get('bio_long');
                $user->is_member = Input::get('is_member');
                $user->twitter = Input::get('twitter');
                $user->website = Input::get('website');

				if ($user->save()) {
					return Redirect::route('user_modify', $user->id)->with('mSuccess', 'Cet utilisateur a bien été modifié');
				} else {
					return Redirect::route('user_modify', $user->id)->with('mError', 'Impossible de modifier cet utilisateur');
				}
			}
		} else {
			return Redirect::route('user_modify', $user->id)->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
		}
	}

	/**
	 * Add User
	 */
	public function add()
	{
		$this->layout->content = View::make('user.add');
	}

	/**
	 * Add User check
	 */
	public function add_check()
	{
		$validator = Validator::make(Input::all(), User::$rulesAdd);
		if (!$validator->fails()) {
            Input::merge(array('password' => Hash::make(Input::get('password'))));
			$user = new User(Input::all());

			if ($user->save()) {
				return Redirect::route('user_list')->with('mSuccess', 'Cet utilisateur a bien été ajouté');
			} else {
				return Redirect::route('user_list')->with('mError', 'Impossible d\'ajouter cet utilisateur');
			}
		} else {
			return Redirect::route('user_add')->with('mError', 'Il y a des erreurs')->withErrors($validator->messages())->withInput();
		}
	}

	/**
	 * User Profile
	 */
	public function profile($id)
	{
		# code...
	}

	/**
	 * Get organisations list of an user (JSON)
	 */
	public function json_organisations($id)
	{
		$user = User::find($id);
		return Response::json($user->organisations->lists('name', 'id'));
	}
}