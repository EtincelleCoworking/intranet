<?php
/**
* Tag Entity
*/
class GiftPhotoshootSlot extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'gift_photoshoot_slot';


    public function user()
    {
        return $this->belongsTo('User');
    }

    public function session()
    {
        return $this->belongsTo('GiftPhotoshootSession');
    }


    /**
     * Rules
     */
    public static $rules = array(
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
    );
}