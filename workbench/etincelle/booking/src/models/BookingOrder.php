<?php

class BookingOrder extends Illuminate\Database\Eloquent\Model
{
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'booking_order';

    /**
     * Rules
     */
    public static $rules = array(
        'user_id' => 'required|exists:users',
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'user_id' => 'required|exists:users',
    );


    /**
     * Relation BelongsTo (Invoices belongs to User)
     */
    public function user()
    {
        return $this->belongsTo('User');
    }

    public function invoice()
    {
        return $this->belongsTo('Invoice');
    }

    public function scopeAll($query)
    {
        return $query;
    }

    public function scopeOverview($query, $user_id = null)
    {
        $query
            ->join('users', function($j)
            {
                $j->on('booking_order.user_id', '=', 'users.id');
            })
            ->join('booking_item', function($j)
            {
                $j->on('booking_item.user_id', '=', 'users.id');
            })
            ->join('booking', function($j)
            {
                $j->on('booking_item.booking_id', '=', 'booking.id');
            })
            ->select(
                DB::raw('SUM(booking_item.duration) as quantity_done')
            )
            ->groupBy('user_id')
            ->orderBy('users.lastname', 'ASC')
            //->get()
            ;
        if($user_id){
            $query->where('users.id', '=', $user_id);
        }
        return $query;
    }
}