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

    public function scopeAvailable($query){
        return $query->whereNull('current_usage_id')->lists('name', 'id');
    }

    public function addUsage($user_id){
        $locker_history = new LockerHistory();
        $locker_history->taken_at = date('Y-m-d H:i:s');
        $locker_history->user_id = $user_id;
        $locker_history->locker_id = $this->id;
        $locker_history->save();

        $this->current_usage_id = $locker_history->id;
        $this->save();

        return $locker_history;
    }
}