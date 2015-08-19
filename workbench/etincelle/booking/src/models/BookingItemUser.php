<?php

class BookingItemUser extends Illuminate\Database\Eloquent\Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'booking_item_user';

    /**
     * Rules
     */
    public static $rules = array(
        'user_id' => 'required|exists:user',
        'booking_item_id' => 'required|exists:booking_item',
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'user_id' => 'required|exists:user',
        'booking_item_id' => 'required|exists:booking_item',
    );

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function booking_item()
    {
        return $this->belongsTo('BookingItem');
    }

}