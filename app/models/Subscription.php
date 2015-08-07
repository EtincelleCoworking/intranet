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
        if ($diff->invert) {
            $result = -1 * $result;
        }
        return $result;

    }


    public function scopeTotalPerMonth($query)
    {
        return $query
            ->select(
                DB::raw('date_format(renew_at, "%Y-%m") as period'),
                DB::raw('SUM(amount) as total')
            )
            ->groupBy('period')
            ->orderBy('period', 'ASC')//->get()
            ;
    }

    public static function getActiveSubscriptionInfos()
    {
        $params = array();
        $active_subscription = InvoiceItem::where('ressource_id', Ressource::TYPE_COWORKING)
            ->where('invoices.user_id', Auth::user()->id)
            ->where('subscription_from', '<', date('Y-m-d'))
            ->where('subscription_to', '>', date('Y-m-d'))
            ->join('invoices', function ($j) {
                $j->on('invoice_id', '=', 'invoices.id')->where('type', '=', 'F');
            })
            ->first();

        $params['active_subscription'] = $active_subscription;

        if ($active_subscription) {
            $params['subscription_used'] = PastTime::recap(Auth::user()->id, $active_subscription->subscription_from, $active_subscription->subscription_to, Ressource::TYPE_COWORKING, false)->first();
        } else {
            $params['subscription_used'] = array('hours' => 0, 'minutes' => 0);
        }
        if ($active_subscription && $params['subscription_used']) {
            $params['subscription_ratio'] = round(100 * ($params['subscription_used']->hours + $params['subscription_used']->minutes / 60) / $active_subscription->subscription_hours_quota);
        } else {
            $params['subscription_ratio'] = 0;
        }
        return $params;
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