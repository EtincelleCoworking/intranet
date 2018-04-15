<?php

/**
 * PhoneBox Entity
 */
class PhoneboxSession extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'phonebox_session';

    public $timestamps = false;

    /**
     * PhoneBox belongs to User
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    /**
     * PhoneBox belongs to User
     */
    public function phonebox()
    {
        return $this->belongsTo('Phonebox');
    }

    /**
     * Rules
     */
    public static $rules = array(
        'start_at' => 'required',
        'user_id' => 'required',
    );

}