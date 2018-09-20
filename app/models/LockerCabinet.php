<?php

/**
 * Tag Entity
 */
class LockerCabinet extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'locker_cabinet';

    public function lockers()
    {
        return $this->hasMany('Locker')->orderBy('name', 'ASC');;
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