<?php

/**
 * Equipment Entity
 */
class Equipment extends Eloquent
{

    public $timestamps = false;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'equipment';

    /**
     * PhoneBox belongs to User
     */
    public function location()
    {
        return $this->belongsTo('Location');
    }

    /**
     * Rules
     */
    public static $rules = array(
        'ip' => 'required|ip',
        'location_id' => 'required',
    );

    public function isUp(){
        return (time() - strtotime($this->last_seen_at)) < 5 * $this->frequency;
    }
}