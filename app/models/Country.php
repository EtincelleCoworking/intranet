<?php
/**
* Country Model
*/
class Country extends Eloquent
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'countries';

    /**
     * The guarded fields
     */
    protected $guarded = array('id');

    /**
     * Relation Has Many (Countries has many organisations)
     */
    public function item()
    {
        return $this->belongsTo('InvoiceItem');
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
        'name' => 'required|min:1|unique:countries'
    );

    /**
     * Get list of vat
     */
    public function scopeSelect($query)
    {
        $selectVals = $this->lists('name', 'id');
        return $selectVals;
    }
}