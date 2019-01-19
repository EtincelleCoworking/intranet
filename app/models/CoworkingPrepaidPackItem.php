<?php

/**
 * City Entity
 */
class CoworkingPrepaidPackItem extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'coworking_prepaid_pack_item';


    /**
     * Rules
     */
    public static $rules = array();

    /**
     * Rules Add
     */
    public static $rulesAdd = array();

    /**
     */
    public function pack()
    {
        return $this->belongsTo('CoworkingPrepaidPack');
    }

    /**
     */
    public function past_time()
    {
        return $this->belongsTo('PastTime');
    }
}