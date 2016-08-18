<?php

/**
 * City Entity
 */
class DeviceSeen extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'devices_seen';

    public $timestamps = false;

    /**
     * Rules
     */
    public static $rules = array(
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
    );

    //protected $fillable = array('mac', 'user_id', 'name');


    /**
     * Relation BelongsTo (Invoices belongs to User)
     */
    public function device()
    {
        return $this->belongsTo('Device');
    }

}