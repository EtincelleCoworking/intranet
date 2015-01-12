<?php

use Illuminate\Auth\UserTrait;
use Illuminate\Auth\UserInterface;
use Illuminate\Auth\Reminders\RemindableTrait;
use Illuminate\Auth\Reminders\RemindableInterface;

class User extends Eloquent implements UserInterface, RemindableInterface {

	use UserTrait, RemindableTrait;

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'users';

	/**
	 * The attributes excluded from the model's JSON form.
	 *
	 * @var array
	 */
	protected $hidden = array('password', 'remember_token');

	/**
	 * The fillable fields
	 */
	protected $fillable = array('fullname', 'email');

	/**
	 * The guarded fields
	 */
	protected $guarded = array('id', 'password');

	/**
	 * Rules
	 */
	public static $rules = array(
		'email' => 'required|email',
		'fullname' => 'required',
		'password' => 'min:5'
	);

	/**
	 * Relation One To Many (User has many Invoices)
	 */
	public function invoices()
	{
		return $this->hasMany('Invoice');
	}

}
