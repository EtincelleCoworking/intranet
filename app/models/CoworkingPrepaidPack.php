<?php

/**
 * City Entity
 */
class CoworkingPrepaidPack extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'coworking_prepaid_pack';


    /**
     * Rules
     */
    public static $rules = array();

    /**
     * Rules Add
     */
    public static $rulesAdd = array();


    public function user()
    {
        return $this->belongsTo('User');
    }


    public function invoice()
    {
        return $this->belongsTo('Invoice');
    }
}