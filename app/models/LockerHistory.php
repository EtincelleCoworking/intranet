<?php

/**
 * Tag Entity
 */
class LockerHistory extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'locker_history';

    public function user()
    {
        return $this->belongsTo('User');
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