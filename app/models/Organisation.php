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
     * Relation BelongsTo (Organisation belongs to country)
     */
    public function country()
    {
        return $this->belongsTo('Country');
    }

    /**
     * Organisation has many invoices
     */
    public function invoices()
    {
        return $this->hasMany('Invoice')->orderBy('date_invoice', 'DESC');
    }

    /**
     * Organisation has many charges
     */
    public function charges()
    {
        return $this->hasMany('Charge');
    }

    /**
     * Full address
     */
    public function getFulladdressAttribute()
    {
        return $this->name."\r\n".$this->address."\r\n".$this->zipcode.' '.$this->city."\r\n".$this->country->name;
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

    /**
     * Get list of organisations where user is not in
     */
    public function scopeSelectNotInOrganisation($query, $user, $title = "Select")
    {
        $ids = OrganisationUser::where('user_id', $user)->lists('organisation_id');
        $selectVals[''] = $title;
        if ($ids) {
            $selectVals += $this->whereNotIn('id', $ids)->lists('name', 'id');
        } else {
            $selectVals += $this->lists('name', 'id');
        }
        return $selectVals;
    }


    /**
     * Get list of organisations
     */
    public function scopeSelect($query, $title = "Select")
    {
        $selectVals[''] = $title;
        $selectVals += $this->orderBy('name', 'ASC')->get()->lists('name', 'id');
        return $selectVals;
    }
}