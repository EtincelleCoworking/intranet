<?php
/**
* Tag Entity
*/
class UserGift extends Eloquent
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'user_gift';



    public function kind()
    {
        return $this->belongsTo('GiftKind');
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