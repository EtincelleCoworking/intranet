<?php
/**
* Organisation User Model
*/
class OrganisationUser extends Eloquent
{
	protected $table = 'organisation_user';

	public function organisation()
	{
		return $this->belongsTo('Organisation');
	}

	public function user()
	{
		return $this->belongsTo('User');
	}
}