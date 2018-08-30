<?php

/**
 * City Entity
 */
class DeviceSeenRange extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'devices_seen_range';

    public $timestamps = false;

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
    public function device()
    {
        return $this->belongsTo('Device');
    }

    /**
     */
    public function location()
    {
        return $this->belongsTo('Location');
    }

}