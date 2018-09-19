<?php

/**
 * Tag Entity
 */
class GiftPhotoshootSession extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'gift_photoshoot_session';


    public function slots()
    {
        return $this->hasMany('GiftPhotoshootSlot', 'session_id')->orderBy('start_at', 'ASC');;
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