<?php

/**
 * Location Entity
 */
class Location extends Eloquent
{
    /**
     * Rules
     */
    public static $rules = array();

    /**
     * Rules Add
     */
    public static $rulesAdd = array();

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

        if (!$includeDisabled) {
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

    public static function getCostPerLocation()
    {
        $costs = array(
            'Albi' => array(
                '2017-02' => 1350,
                '2017-07' => 1820,
                '2018-03' => 2550,
                '2018-12' => 1550,
                '2019-02' => 2035,
            ),
            'Montauban' => array(
                '2015-09' => 2050,
                '2016-12' => 3870,
                '2017-11' => 2430,
                '2018-04' => 1850,
                '2018-11' => 0,
            ),
            'Carmes' => array(
                '2016-01' => 500,
                '2016-04' => 3500,
                '2016-09' => 10740,
                '2017-12' => 11820,
                '2018-02' => 10415,
                '2019-03' => 8350,
                '2019-12' => 6650,
            ),
            'Victor Hugo' => array(
                '2016-09' => 3415,
                '2017-12' => 3735,
                '2018-01' => 3565,
                '2018-02' => 3705,
                '2018-08' => 3430,
                '2019-03' => 4440,
                '2019-09' => 4150,
                '2019-12' => 4125,
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
                '2018-08' => 16265 + 4060,// - Caroline
                '2018-09' => 17685 + 4480 - 1080, // Julie partiel
                '2018-10' => 17405 + 4480,
                '2018-11' => 17405 + 4480 + 500, // Suayip
                '2019-01' => 17405 + 4480 + 500 + 815, // Suayip + Albert 1er
                '2019-02' => 17405 + 4480 + 500 + 210, // Suayip + Albert 1er
                '2019-03' => 24530,
                '2019-07' => 24000, // -Suayip
                '2019-09' => 21650, // +Marina 50% -Julie AM
                '2019-10' => 21950, // +Marina 100%
                '2019-12' => 24050, // +Tamara+Léa
                '2020-02' => 19750, // -Aurélie -Julie
            ),
            //'Toulouse > Espace W' => array(),
            'Alsace Lorraine' => array(
                '2017-12' => 2170,
                '2018-02' => 6300,
                '2018-08' => 5075, // - Caroline - Ménage
                '2018-09' => 6300,
                '2019-02' => 8480, // +Lyne
                '2019-08' => 8380, // -Lyne
                '2019-10' => 7620, // +Marina
                '2019-09' => 7620, // +Marina
                '2019-12' => 8175, // +Tamara+Léa
                '2020-02' => 8435,
            ),
            'Baour Lormian' => array(
                '2019-11' => 3400,
                '2020-02' => 4050,
            ),
            'Wilson 4+5' => array(
                '2020-02' => 4890,
                '2020-03' => 9780,
            )
        );
        $periods = array();
        $when = Config::get('etincelle.activity_started');
        while ($when < time()) {
            $periods[date('Y-m', $when)] = 0;
            $when = strtotime('+1 month', $when);
        }

        foreach ($costs as $location => $data) {
            foreach ($periods as $period => $value) {
                if (!isset($costs[$location][$period])) {
                    $costs[$location][$period] = false;
                }
            }
            ksort($costs[$location]);
            $cost = 0;
            foreach ($costs[$location] as $period => $value) {
                if (is_int($value)) {
                    $cost = $value;
                }
                $costs[$location][$period] = $cost;
            }
        }
        return $costs;

    }

    public static function getOperationTweaks()
    {
        return array(
            'Carmes' => array(
                // etalement du paiement Carmes
                '2016-03' => -5 * 9250,
                '2016-04' => 9250,
                '2016-05' => 9250,
                '2016-06' => 9250,
                '2016-07' => 9250,
                '2016-08' => 9250,
                '2019-03' => -2 * 14475.28,
                '2019-04' => 14475.28,
                '2019-05' => 14475.28,
                '2019-06' => -2 * 14475.28,
                '2019-07' => 14475.28,
                '2019-08' => 14475.28,
                '2019-09' => -2 * 14475.28,
                '2019-10' => 14475.28,
                '2019-11' => 14475.28,
                '2019-12' => -2 * 13475.18,
                '2020-01' => 13475.18,
                '2020-02' => 13475.18,
            ),
            'Victor Hugo' => array(
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
                '2019-09' => -6780, // AJC
                '2019-10' => 2253,
                '2019-11' => 2253,
                '2019-12' => 2254,

            ),
            'Albi' => array(
                '2017-11' => -425,
                '2017-12' => 425,
            ),
            'Wilson' => array(
                '2018-06' => -2590,
                '2018-07' => 1085,
                '2018-08' => 1085,
                '2018-09' => 420 - 1612.5,
                '2018-10' => 1612.5 - 1290,
                '2018-11' => 1290,
                '2019-08' => -2512.50, // Simplon
                '2019-09' => 2512.50 - 422 - 422, // Activ Partners
                '2019-10' => 0,
                '2019-11' => 0,
                '2020-01' => -1312.50 - 900, // Simplon + ELPMSN
                '2020-02' => 1312.50 + 300,// Simplon + ELPMSN
                '2020-03' => 1312.50 + 300,// Simplon + ELPMSN
                '2020-04' => 1312.50 + 300,// Simplon + ELPMSN
            ),

            'Alsace Lorraine' => array(
                '2018-09' => -1706.25,
                '2018-10' => 1706.25,
                '2019-07' => -2800,
                '2019-08' => 2000,
                '2019-09' => 800 - 422 - 422,
                '2019-10' => 422,
                '2019-11' => 422,
            )
        );
    }

    public static function getStats()
    {
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

    public function generateVoucher($occurs_at, $validity = 86400 /* ONE DAY */)
    {
        if ($this->voucher_endpoint) {
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($curl, CURLOPT_USERPWD, $this->voucher_key . ':' . $this->voucher_secret);
            curl_setopt($curl, CURLOPT_POST, 1);
            curl_setopt($curl, CURLOPT_POSTFIELDS, array(
                'count' => 1,
                'validity' => $validity,
                'expirytime' => 0, // amount in sec
                'vouchergroup' => date('Ymd', strtotime($occurs_at)),
            ));
            curl_setopt($curl, CURLOPT_URL, $this->voucher_endpoint);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
            $result = json_decode(curl_exec($curl));
            curl_close($curl);

            if (is_array($result)) {
                $voucher = array_pop($result);
                if (is_object($voucher)) {
                    return array(
                        'username' => $voucher->username,
                        'password' => $voucher->password,
                    );
                }
            }
        }
        return false;
    }
}