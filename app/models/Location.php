<?php

/**
 * Location Entity
 */
class Location extends Eloquent
{
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

    public function ips()
    {
        return $this->hasMany('LocationIp', 'id')->orderBy('updated_at', 'DESC');
    }

    /**
     * Get list of ressources
     */
    public function scopeSelectAll($query, $includeEmpty = '-', $includeDisabled = false)
    {
        $selectVals = array();
        if ($includeEmpty) {
            $selectVals[null] = $includeEmpty;
        }
        $query
            ->join('cities', 'cities.id', '=', 'locations.city_id')
            ->select(array('locations.id', DB::raw('concat(cities.name, \' > \', IF(locations.name IS NULL, \'\', locations.name)) as _name')));

        if(!$includeDisabled){
            $query->where('enabled', '=', true);
        }
        $selectVals += $query
            ->orderBy('cities.name', 'ASC')
            ->orderBy('locations.name', 'ASC')
            ->lists('_name', 'id');
        foreach ($selectVals as $k => $v) {
            $selectVals[$k] = rtrim($v, ' > ');
        }
        return $selectVals;
    }

    public function __toString()
    {
        return (string)$this->full_name;
    }

    public function getFullNameAttribute()
    {
        if ($this->name) {
            return sprintf('%s > %s', $this->city->name, $this->name);
        }
        return $this->city->name;
    }

    /**
     * Relation BelongsTo (Invoices_Items belongs to Ressource)
     */
    public function city()
    {
        return $this->belongsTo('City', 'city_id');
    }

    public static function getCostPerLocation(){
        $costs = array(
            'Albi' => array(
                '2017-02' => 1350,
                '2017-07' => 1820,
                '2018-03' => 2550,
            ),
            'Montauban' => array(
                '2015-09' => 2050,
                '2016-12' => 3870,
                '2017-11' => 2430,
                '2018-04' => 1850,
            ),
            'Carmes' => array(
                '2016-01' => 500,
                '2016-04' => 3500,
                '2016-09' => 10740,
                '2017-12' => 11820,
                '2018-02' => 10415,
            ),
            'Victor Hugo' => array(
                '2016-09' => 3415,
                '2017-12' => 3735,
                '2018-01' => 3565,
                '2018-02' => 3705,
            ),
            'Wilson' => array(
                '2015-01' => 7000,
                '2016-12' => 9280,
                '2017-06' => 12500,
                '2017-08' => 12500 + 300, // Espace W
                '2017-09' => 14150 + 300,
                '2017-10' => 14150 + 4480,
                '2017-12' => 15080 + 4480,
                '2018-02' => 17685 + 4480,
            ),
            //'Toulouse > Espace W' => array(),
            'Alsace Lorraine' => array(
                '2017-12' => 2170,
                '2018-02' => 6300,
            ),
        );
        $periods = array();
        $when = Config::get('etincelle.activity_started');
        while($when < time()){
            $periods[date('Y-m', $when)] = 0;
            $when = strtotime('+1 month', $when);
        }

        foreach ($costs as $location => $data) {
            foreach ($periods as $period => $value) {
                if (!isset($costs[$location][$period])) {
                    $costs[$location][$period] = 0;
                }
            }
            ksort($costs[$location]);
            $cost = 0;
            foreach ($costs[$location] as $period => $value) {
                if ($value) {
                    $cost = $value;
                }
                $costs[$location][$period] = $cost;
            }
        }
        return $costs;

    }

    public static function getOperationTweaks(){
        return array(
            'Toulouse > Carmes' => array(
                // etalement du paiement Carmes
                '2016-03' => -5 * 9250,
                '2016-04' => 9250,
                '2016-05' => 9250,
                '2016-06' => 9250,
                '2016-07' => 9250,
                '2016-08' => 9250,
            ),
            'Toulouse > Victor Hugo' => array(
                // Loyer 12/2016 A. T.
                '2016-11' => -1050,
                '2016-12' => 1050,
                // Loyer trimestriel > mensuel
                '2017-03' => -2 * 550,
                '2017-04' => 550,
                '2017-05' => 550,
                '2017-06' => -2 * 550,
                '2017-07' => 550,
                '2017-08' => 550,
                '2017-09' => -2 * 550,
                '2017-10' => 550,
                '2017-11' => 550 - 3 * 550,
                '2017-12' => 550,
                '2018-01' => 550,
                '2018-02' => 550,
                '2018-03' => -2 * 550,
                '2018-04' => 550 - 600,
                '2018-05' => 550,
                '2018-06' => 600,

            ),
            'Toulouse > Espace W' => array(),
            'Albi' => array(
                '2017-11' => -425,
                '2017-12' => 425,
            ),
        );
    }

    public static function getStats(){
        $items = DB::select(DB::raw('select 
date_format(invoices.date_invoice, "%Y-%m") as period, 
SUM(invoices_items.amount) as total, 
# if(`locations`.`name` is null,cities.name,concat(cities.name, \' > \',  `locations`.`name`)) as `kind` 
if(`locations`.`name` is null,cities.name,locations.name) as `kind`

from `invoices_items` 
inner join `invoices` on `invoice_id` = `invoices`.`id` and invoices.`type` = \'F\' 
left outer join `organisations` on invoices.`organisation_id` = `organisations`.`id` 
left outer join `ressources` on invoices_items.`ressource_id` = `ressources`.`id` 
left outer join `locations` on ressources.`location_id` = `locations`.`id` 
left outer join cities on locations.city_id = cities.id

where ressources.ressource_kind_id NOT IN (' . RessourceKind::TYPE_COWORKING . ', ' . RessourceKind::TYPE_EXCEPTIONNAL . ')

group by `period`, kind
order by kind ASC, `period` desc')); // `organisations`.`is_founder` = '0' or (`organisation_id` is null) AND
        $result = array();
        $periods = array();
        foreach ($items as $item) {
            $result[$item->kind][$item->period] = (float)$item->total;
            $periods[$item->period] = true;
        }

        $items = DB::select(DB::raw('select 
date_format(invoices.date_invoice, "%Y-%m") as period, 
SUM(invoices_items.amount) as total, 
# if(`locations`.`name` is null,cities.name,concat(cities.name, \' > \',  `locations`.`name`)) as `kind` 
if(`locations`.`name` is null,cities.name,locations.name) as `kind`

from `invoices_items` 
inner join `invoices` on `invoice_id` = `invoices`.`id` and `type` = \'F\' 
left outer join `organisations` on `organisation_id` = `organisations`.`id` 
join `ressources` on invoices_items.ressource_id = `ressources`.`id`

join users u on u.id = invoices_items.subscription_user_id 
join `locations` on u.default_location_id = `locations`.`id` 
left outer join cities on city_id = cities.id

where (`organisations`.`is_founder` = \'0\' or `organisation_id` is null) 
AND ressources.ressource_kind_id = ' . RessourceKind::TYPE_COWORKING . '

group by `period`, kind
order by kind ASC, `period` DESC
'));
        foreach ($items as $item) {
            if (!isset($result[$item->kind][$item->period])) {
                $result[$item->kind][$item->period] = 0;
            }
            $periods[$item->period] = true;
            $result[$item->kind][$item->period] += (float)$item->total;
        }


        foreach ($result as $location => $data) {
            foreach ($periods as $period => $value) {
                if (!isset($result[$location][$period])) {
                    $result[$location][$period] = 0;
                }
            }
            ksort($result[$location]);
        }

        $costs = Location::getCostPerLocation();
        $operations = self::getOperationTweaks();

        $this_month = date('Y-m');

        $datas = array();
        foreach ($costs as $location => $data) {
            foreach ($data as $period => $value) {
                if ($period <= $this_month) {
                    if (!isset($result[$location][$period])) {
                        $result[$location][$period] = 0;
                    }
                    if (!isset($costs[$location][$period])) {
                        $costs[$location][$period] = 0;
                    }
                    if (isset($operations[$location][$period])) {
                        $result[$location][$period] += (float)$operations[$location][$period];
                    }
                    $y = substr($period, 0, 4);
                    if (!isset($datas[$location][$y])) {
                        $datas[$location][$y] = array();
                    }

                    $datas[$location][$y][$period] = array(
                        'sales' => (float)$result[$location][$period],
                        'cost' => (float)$costs[$location][$period],
                        'balance' => (float)$result[$location][$period] - (float)$costs[$location][$period],
                    );
                }
            }
            krsort($datas[$location]);

        }
            return $datas;
    }
}