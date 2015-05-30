<?php

class Subscription extends Eloquent
{

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'subscription';

    public function user()
    {
        return $this->belongsTo('User');
    }

    public function organisation()
    {
        return $this->belongsTo('Organisation');
    }

    /**
     * Rules
     */
    public static $rules = array(
        'amount' => 'required|min:1'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'amount' => 'required|min:1'
    );

    public function getDaysBeforeRenewAttribute()
    {
        $date1 = new DateTime();
        $date2 = new DateTime($this->renew_at);
        $diff = $date1->diff($date2);
        $result = $diff->days;
        if($diff->invert){
            $result = -1 * $result;
        }
        return  $result;

    }


//    /**
//     * Get list of vat
//     */
//    public function scopeSelectAll($query)
//    {
//        $selectVals = $this->orderBy('value', 'DESC')->lists('value', 'id');
//        return $selectVals;
//    }
}