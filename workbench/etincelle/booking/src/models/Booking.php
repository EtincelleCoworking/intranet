<?php

class Booking extends Illuminate\Database\Eloquent\Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'booking';

    /**
     * Rules
     */
    public static $rules = array(
        'user_id' => 'required|exists:users',
        'title' => 'required|min:1'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'user_id' => 'required|exists:users',
        'title' => 'required|min:1'
    );


    /**
     * Relation BelongsTo (Invoices belongs to User)
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    public function items()
    {
        return $this->hasMany('BookingItem')
            //->orderBy('start_at', 'ASC')
            ;
    }

    public function scopeAll($query)
    {
        return $query;
    }

    static public function selectableHours()
    {
        $slot = 15;

        $result = array();
        for ($h = 0; $h < 24; $h++) {
            for ($m = 0; $m < 60; $m += $slot) {
                $value = sprintf('%02d:%02d', $h, $m);
                $result[$value] = $value;
            }
        }
        return $result;
    }
}