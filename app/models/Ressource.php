<?php

/**
 * Ressource Entity
 */
class Ressource extends Eloquent
{
    const TYPE_COWORKING = 1;
    const TYPE_EXCEPTIONNAL = 12;
    const TYPE_DOMICILIATION = 9;
    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ressources';

    /**
     * Relation hasMany (Ressource has many Invoices_Items)
     */
    public function items()
    {
        return $this->hasMany('InvoiceItem');
    }

    /**
     * Relation One To Many (Ressource has many Past Times)
     */
    public function pasttimes()
    {
        return $this->hasMany('PastTime');
    }

    /**
     * Relation BelongsTo (Invoices_Items belongs to Ressource)
     */
    public function kind()
    {
        return $this->belongsTo('RessourceKind', 'ressource_kind_id');
    }

    public function subscription()
    {
        return $this->belongsTo('Subscription');
    }

    /**
     * Rules
     */
    public static $rules = array(
        'name' => 'required|min:1'
    );

    /**
     * Rules Add
     */
    public static $rulesAdd = array(
        'name' => 'required|min:1'
    );

    public function getLabelCssAttribute()
    {
        return sprintf('background-color: %s; color: %s; border: 1px solid %s; margin-right: 10px; opacity: 0.75',
            $this->booking_background_color,
            adjustBrightness($this->booking_background_color, -128),
            adjustBrightness($this->booking_background_color, -32)
        );
    }

    /**
     * Get list of ressources
     */
    public function scopeSelectAll($query, $emptyCaption = 'Aucune ressource')
    {
        $selectVals = array();
        if (!empty($emptyCaption)) {
            $selectVals[0] = $emptyCaption;
        }
        $commonKey = 'Services communs';
        $selectVals[$commonKey] = array();

        foreach ($query->select('ressources.*')
                     ->join('locations', 'ressources.location_id', '=', 'locations.id', 'left outer')
                     ->orderBy('locations.name', 'asc')
                     ->orderBy('ressources.order_index', 'asc')
                     ->orderBy('order_index', 'ASC')->get() as $ressource) {
            $location = (string)$ressource->location;
            if(empty($location)){
                $location = $commonKey;
            }
            $selectVals[$location][$ressource->id] = $ressource->name;
        }
        return $selectVals;
    }

    /**
     * Get list of ressources
     */
    public function scopeBookable($query, $emptyCaption = '')
    {
        $selectVals = array();
        if (!empty($emptyCaption)) {
            $selectVals[null] = $emptyCaption;
        }
        $commonKey = 'Services communs';
        $selectVals[$commonKey] = array();
        foreach ($query->whereIsBookable(true)
                     ->select('ressources.*')
                     ->join('locations', 'ressources.location_id', '=', 'locations.id')
                     ->orderBy('locations.name', 'asc')
                     ->orderBy('ressources.order_index', 'asc')
                     ->orderBy('order_index', 'ASC')->get() as $ressource) {
            $location = (string)$ressource->location;
            if(empty($location)){
                $location = $commonKey;
            }
            $selectVals[$location][$ressource->id] = $ressource->name;
        }
        return $selectVals;
    }

    /**
     * Relation BelongsTo (Invoices_Items belongs to Ressource)
     */
    public function location()
    {
        return $this->belongsTo('Location', 'location_id');
    }

    public function getStats()
    {
        return Ressource::getStatForRessource($this->id);
    }

    protected static function getWorkingDays($startDate, $endDate, $holidays = array())
    {
        // do strtotime calculations just once
        $endDate = strtotime($endDate);
        $startDate = strtotime($startDate);


        //The total number of days between the two dates. We compute the no. of seconds and divide it to 60*60*24
        //We add one to inlude both dates in the interval.
        $days = ($endDate - $startDate) / 86400 + 1;

        $no_full_weeks = floor($days / 7);
        $no_remaining_days = fmod($days, 7);

        //It will return 1 if it's Monday,.. ,7 for Sunday
        $the_first_day_of_week = date("N", $startDate);
        $the_last_day_of_week = date("N", $endDate);

        //---->The two can be equal in leap years when february has 29 days, the equal sign is added here
        //In the first case the whole interval is within a week, in the second case the interval falls in two weeks.
        if ($the_first_day_of_week <= $the_last_day_of_week) {
            if ($the_first_day_of_week <= 6 && 6 <= $the_last_day_of_week) $no_remaining_days--;
            if ($the_first_day_of_week <= 7 && 7 <= $the_last_day_of_week) $no_remaining_days--;
        } else {
            // (edit by Tokes to fix an edge case where the start day was a Sunday
            // and the end day was NOT a Saturday)

            // the day of the week for start is later than the day of the week for end
            if ($the_first_day_of_week == 7) {
                // if the start date is a Sunday, then we definitely subtract 1 day
                $no_remaining_days--;

                if ($the_last_day_of_week == 6) {
                    // if the end date is a Saturday, then we subtract another day
                    $no_remaining_days--;
                }
            } else {
                // the start date was a Saturday (or earlier), and the end date was (Mon..Fri)
                // so we skip an entire weekend and subtract 2 days
                $no_remaining_days -= 2;
            }
        }

        //The no. of business days is: (number of weeks between the two dates) * (5 working days) + the remainder
//---->february in none leap years gave a remainder of 0 but still calculated weekends between first and last day, this is one way to fix it
        $workingDays = $no_full_weeks * 5;
        if ($no_remaining_days > 0) {
            $workingDays += $no_remaining_days;
        }

        //We subtract the holidays
        foreach ($holidays as $holiday) {
            $time_stamp = strtotime($holiday);
            //If the holiday doesn't fall in weekend
            if ($startDate <= $time_stamp && $time_stamp <= $endDate && date("N", $time_stamp) != 6 && date("N", $time_stamp) != 7)
                $workingDays--;
        }

        return $workingDays;
    }


    public static function getStatForRessource($id)
    {
        $items = DB::select(DB::raw(sprintf('SELECT 
 date_format(invoices.date_invoice, \'%%Y-%%m\') as occurs_at, 
 sum(if((UNIX_timestamp(time_end)-UNIX_timestamp( time_start ))/3600>=7,7,(UNIX_timestamp(time_end)-UNIX_timestamp( time_start ))/3600)) as sold_hours
FROM invoices 
join past_times ON past_times.invoice_id = invoices.id 
WHERE past_times.ressource_id = %1$d
AND invoices.type = \'F\'
group by occurs_at DESC', $id)));
        $sold_hours = array();
        foreach ($items as $item) {
            $sold_hours[$item->occurs_at] = $item->sold_hours;
        }
        //var_dump($items);


        $items = DB::select(DB::raw(sprintf('SELECT 
 date_format(invoices.date_invoice, \'%%Y-%%m\') as occurs_at, 
 round(sum(invoices_items.amount)) as amount
FROM `invoices_items` 
join invoices on invoices.id = invoices_items.invoice_id AND invoices.type = \'F\'
WHERE invoices_items.ressource_id = %1$d
group by occurs_at DESC', $id)));

        foreach ($items as $index => $item) {
            $when = strtotime($item->occurs_at . '-01');
            $items[$index]->working_days = self::getWorkingDays(date('Y-m-01', $when), date('Y-m-t', $when));
            $items[$index]->sold_hours = isset($sold_hours[$item->occurs_at]) ? $sold_hours[$item->occurs_at] : 0;
            $items[$index]->busy_rate = 100 * $items[$index]->sold_hours / ($items[$index]->working_days * 7);
        }
        return $items;
    }

}