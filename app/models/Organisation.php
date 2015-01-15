<?php
/**
* Organisation Model
*/
class Organisation extends Eloquent
{

	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = 'organisations';

    /**
     * The guarded fields
     */
    protected $guarded = array('id');

	/**
	 * Relation Belongs To Many (Organisation has many Users)
	 */
	public function users()
	{
		return $this->belongsToMany('User', 'organisation_user', 'organisation_id', 'user_id');
	}

	/**
	 * Rules
	 */
	public static $rules = array(
		'name' => 'required|min:1'
	);

	/**
	 * Rules Add
	 */
	public static $rulesAdd = array(
		'name' => 'required|min:1|unique:organisations'
	);
}