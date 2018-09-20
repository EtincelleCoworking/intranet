<?php

/**
 * Tag Entity
 */
class Locker extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'locker';

    public function current_usage()
    {
        return $this->belongsTo('LockerHistory');
    }

    public function cabinet()
    {
        return $this->belongsTo('LockerCabinet', 'locker_cabinet_id');
    }

    /**
     * Rules
     */
    public static $rules = array();

    /**
     * Rules Add
     */
    public static $rulesAdd = array();
}