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

    public function organisation()
    {
        return $this->belongsTo('Organisation');
    }

    public function items()
    {
        return $this->hasMany('BookingItem')//->orderBy('start_at', 'ASC')
            ;
    }

    public function scopeAll($query)
    {
        return $query;
    }

    public function scopeFuture($query)
    {
        return $query
            ->join('booking_item', function ($j) {
                $j->on('booking_id', '=', 'booking.id');
            })
            ->where('booking_item.start_at', '>=', date('Y-m-d'))
            ->where('booking.is_private', '=', false)
            ->select('booking.*')
            ->orderBy('booking_item.start_at', 'ASC')
            ->distinct()
            ->get();
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

    public function __construct(array $attributes = array())
    {
        $this->is_private = Config::get('booking::default_is_private', true);
        parent::__construct($attributes);
    }

    public static function generateVoucher($voucher_endpoint, $voucher_key, $voucher_secret, $occurs_at, $validity = 86400 /* ONE DAY */)
    {
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($curl, CURLOPT_USERPWD, $voucher_key . ':' . $voucher_secret);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, array(
            'count' => 1,
            'validity' => $validity,
            'expirytime' => 0, // amount in sec
            'vouchergroup' => date('Ymd', strtotime($occurs_at)),
        ));
        curl_setopt($curl, CURLOPT_URL, $voucher_endpoint);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        $result = json_decode(curl_exec($curl));
        curl_close($curl);

        $voucher = array_pop($result);
        if (is_object($voucher)) {
            return array(
                'username' => $voucher->username,
                'password' => $voucher->password,
            );
            return true;
        }

        return false;
    }

}