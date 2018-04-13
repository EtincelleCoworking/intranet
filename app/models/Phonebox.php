<?php

/**
 * PhoneBox Entity
 */
class Phonebox extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'phonebox';

    /**
     * PhoneBox belongs to User
     */
    public function location()
    {
        return $this->belongsTo('Location');
    }

    /**
     * PhoneBox belongs to Ressource
     */
    public function active_session()
    {
        return $this->belongsTo('PhoneboxSession', 'active_session_id');
    }

    /**
     * Rules
     */
    public static $rules = array(
        'name' => 'required|min:1',
    );

}