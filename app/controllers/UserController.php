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
			return Redirect::route('user_login')->with('mInfo', 'Merci de bien vouloir vous connecter');
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
	 * User Profile
	 */
	public function profile()
	{
		# code...
	}
}