<?php

/**
 * Cashflow Controller
 */
class StatsController extends BaseController
{
    const LABEL_OTHERS = 'Autre';

    public function overview()
    {
        $charts = array();
        foreach (InvoiceItem::TotalPerMonth()->WithoutStakeholders()->get() as $item) {
            $charts['Produits (hors associés)'][$item->period] = $item->total;
        }

        foreach (InvoiceItem::TotalPerMonth()->get() as $item) {
            $charts['Produits'][$item->period] = $item->total;
        }


        foreach ($charts as $name => $chart) {
            ksort($charts[$name]);
        }


        return View::make('stats.ca', array('charts' => $charts));
    }

    public function charges()
    {
        $charts = array();
        foreach (ChargeItem::TotalPerMonth() as $item) {
            $charts['Charges'][$item->period] = $item->total;
        }

        foreach ($charts as $name => $chart) {
            ksort($charts[$name]);
        }


        return View::make('stats.ca', array('charts' => $charts));

    }


    public function sales()
    {
        $charts = array();

        foreach (InvoiceItem::TotalPerMonth()->withoutStakeholders()->byKind()->get() as $item) {
            $charts[$item->kind ? $item->kind : self::LABEL_OTHERS][$item->period] = $item->total;
        }

        foreach ($charts as $name => $chart) {
            ksort($charts[$name]);
        }


        return View::make('stats.ca', array('charts' => $charts));
    }


    public function customers()
    {
        $charts = array();

        foreach (InvoiceItem::TotalCountPerMonth()->WithoutStakeholders()->byKind()->get() as $item) {
            $charts[$item->kind ? $item->kind : self::LABEL_OTHERS][$item->period] = $item->total;
        }

        foreach ($charts as $name => $chart) {
            ksort($charts[$name]);
        }


        return View::make('stats.ca', array('charts' => $charts));
    }

    public function subscriptions()
    {
        $datas = array();
        foreach (Subscription::TotalPerMonth()->get() as $item) {
            $datas[$item->period] = $item->total;
        }

        $data = DB::select(DB::raw(sprintf('SELECT subscription_kind.name, subscription_kind.price, count( subscription.id ) AS nb
FROM `subscription_kind`
JOIN subscription ON subscription_kind.id = subscription.subscription_kind_id
JOIN users ON subscription.user_id = users.id
WHERE subscription_kind.ressource_id = %d
GROUP BY subscription_kind.id', Ressource::TYPE_COWORKING)));
        $ratio_all = array();
        $ratio_all_total = 0;
        $ratio_all_total_price = 0;
        foreach ($data as $item) {
            $caption = str_replace(array(' - %UserName%', 'Coworking - '), array('', ''), $item->name);
            $ratio_all[$caption]['count'] = $item->nb;
            $ratio_all[$caption]['amount'] = $item->nb * $item->price;
            $ratio_all_total += $item->nb;
            $ratio_all_total_price += $item->nb * $item->price;
        }
        foreach ($data as $item) {
            $caption = str_replace(array(' - %UserName%', 'Coworking - '), array('', ''), $item->name);
            $ratio_all[$caption]['ratio'] = 100 * $item->nb / $ratio_all_total;
        }
        $data = DB::select(DB::raw(sprintf('SELECT if(`locations`.`name` is null,cities.name,concat(cities.name, \' > \',  `locations`.`name`)) as location, subscription_kind.name, subscription_kind.price, count( subscription.id ) AS nb
FROM `subscription_kind`
JOIN subscription ON subscription_kind.id = subscription.subscription_kind_id
JOIN users ON subscription.user_id = users.id
JOIN locations on locations.id = users.default_location_id
JOIN cities on cities.id = locations.city_id
WHERE subscription_kind.ressource_id = %d
GROUP BY locations.id, subscription_kind.id', Ressource::TYPE_COWORKING)));
        $ratio_spaces = array();
        $ratio_spaces_total = array();
        foreach ($data as $item) {
            $caption = str_replace(array(' - %UserName%', 'Coworking - '), array('', ''), $item->name);
            $ratio_spaces[$item->location][$caption]['count'] = $item->nb;
            $ratio_spaces[$item->location][$caption]['amount'] = $item->nb * $item->price;
            if (!isset($ratio_spaces_total[$item->location])) {
                $ratio_spaces_total[$item->location] = array('amount' => 0, 'count' => 0);
            }
            $ratio_spaces_total[$item->location]['count'] += $item->nb;
            $ratio_spaces_total[$item->location]['amount'] += $item->nb * $item->price;
        }
        foreach ($ratio_spaces as $location => $data) {
            foreach ($data as $caption => $d) {
                $ratio_spaces[$location][$caption]['ratio'] = 100 * $ratio_spaces[$location][$caption]['count'] / $ratio_spaces_total[$location]['count'];
            }
        }
        return View::make('stats.subscriptions', array(
            'datas' => $datas,
            'ratio_all' => $ratio_all,
            'ratio_all_total' => $ratio_all_total,
            'ratio_all_total_price' => $ratio_all_total_price,
            'ratio_spaces' => $ratio_spaces,
            'ratio_spaces_total' => $ratio_spaces_total
        ));
    }

    public function sales_per_category($period = null)
    {
        $colors = array();
        $colors[] = '#3f2860';
        $colors[] = '#90c5a9';
        $colors[] = '#7a9a95';
        $colors[] = '#ef6d3b';

        $data = array();
        $query = InvoiceItem::withoutExceptionnals()->total()->byKind();
        if ($period) {
            $period = strtotime($period);
            $query->whereBetween('invoices.date_invoice', array(date('Y-m-01', $period), date('Y-m-t', $period)));
        }

        foreach ($query->get() as $item) {
            $data[$item->kind ? $item->kind : self::LABEL_OTHERS] = array('amount' => $item->total, 'color' => array_shift($colors));
        }

        $total = 0;
        foreach ($data as $k => $v) {
            $total += $data[$k]['amount'];
        }
        foreach ($data as $k => $v) {
            $data[$k]['ratio'] = $total ? sprintf('%0.2f', 100 * $data[$k]['amount'] / $total) : 0;
        }

        return View::make('stats.pie', array(
            'data' => $data, 'total' => $total, 'period' => $period));
    }

    protected function getNextPeriod($value)
    {
        $year = substr($value, 0, 4);
        $month = substr($value, 4, 2);
        if ($month == 12) {
            $year++;
            $month = 1;
        } else {
            $month++;
        }
        return sprintf('%04d%02d', $year, $month);
    }


    protected function getPreviousPeriod($value)
    {
        $year = substr($value, 0, 4);
        $month = substr($value, 4, 2);
        if ($month == 1) {
            $year--;
            $month = 12;
        } else {
            $month--;
        }
        return sprintf('%04d%02d', $year, $month);
    }

    public function members()
    {
        $items = DB::select(DB::raw(sprintf('SELECT ii.subscription_user_id, if(ii.subscription_from = "0000-00-00 00:00:00", i.days, date_format(ii.subscription_from, "%%Y%%m")) as days
          FROM invoices i JOIN invoices_items ii ON i.id = ii.invoice_id 
          WHERE i.type = "F" AND ii.ressource_id = %d 
            AND ii.subscription_user_id IS NOT NULL 
            AND ii.subscription_from <= "%s 23:59:59"
            ORDER BY days DESC, i.organisation_id ASC', Ressource::TYPE_COWORKING, date('Y-m-t'))));
        $results = array();
        $users = array();
        foreach ($items as $item) {
            if (!isset($results[$item->days])) {
                $results[$item->days] = array();
            }
            $results[$item->days][$item->subscription_user_id] = '';
            $users[$item->subscription_user_id] = true;
        }
        $items = DB::select(DB::raw(sprintf('SELECT distinct(pt.user_id) FROM past_times pt
          JOIN users u ON u.id = pt.user_id
          WHERE pt.ressource_id = %d 
            AND u.role <> "superadmin"
            AND pt.date_past BETWEEN "%s" AND "%s"', Ressource::TYPE_COWORKING, date('Y-m-01'), date('Y-m-t'))));
        $period = date('Ym');
        foreach ($items as $item) {
            if (!isset($results[$period])) {
                $results[$period] = array();
            }
            $results[$period][$item->user_id] = '';
            $users[$item->user_id] = true;
        }
        $users_instances = User::whereIn('id', array_keys($users))->get();
        foreach ($users_instances as $user) {
            $users[$user->id] = $user;
        }
        $items = array();
        foreach ($results as $period => $u) {
            foreach ($u as $user_id => $status) {
                $previous = $this->getPreviousPeriod($period);
                $next = $this->getNextPeriod($period);
                if (isset($results[$previous][$user_id])) {
                    if (isset($results[$next]) && !isset($results[$next][$user_id])) {
                        //$results[$period][$user_id] = ;
                        $items[$period]['leaving'][$user_id] = $users[$user_id];
                    } else {
                        $items[$period]['members'][$user_id] = $users[$user_id];
                    }
                } else {
                    //$results[$period][$user_id] = 'new';
                    if (isset($results[$next]) && !isset($results[$next][$user_id])) {
                        $items[$period]['new-leaving'][$user_id] = $users[$user_id];
                    } else {
                        $items[$period]['new'][$user_id] = $users[$user_id];
                    }
                }
            }
        }
        return View::make('stats.members', array('items' => $items));
    }

    public function age()
    {
        $items = DB::select(DB::raw('SELECT gender, count(*) as cnt FROM users WHERE is_member = true AND gender IS NOT NULL GROUP BY gender'));
        $result1 = array();
        $total = 0;
        foreach ($items as $item) {
            $result1[$item->gender] = $item->cnt;
            $total += $item->cnt;
        }
        foreach ($result1 as $gender => $value) {
            $result1[$gender] = round(100 * $value / $total);
        }

        $items = DB::select(DB::raw('SELECT gender, TIMESTAMPDIFF(YEAR, birthday, CURDATE()) AS age, count(*) as cnt FROM users WHERE is_member = true AND birthday != "0000-00-00" AND gender IS NOT NULL GROUP BY gender, age ORDER BY gender ASC, age ASC'));
        $result2 = array();
        $maxAge = 0;
        $max = 0;
        $min = 1000;
        $total_age = 0;
        $total_count = 0;
        foreach ($items as $item) {
            $result2[$item->age][$item->gender] = $item->cnt;
            if ($maxAge < $item->age) {
                $maxAge = $item->age;
            }
            if ($max < $item->cnt) {
                $max = $item->cnt;
            }
            if ($min > $item->age) {
                $min = $item->age;
            }
            $total_age += $item->age;
            $total_count++;
        }
        for ($i = $min; $i <= $maxAge; $i++) {
            if (isset($result2[$i])) {
                foreach (array('M', 'F') as $gender) {
                    if (!isset($result2[$i][$gender])) {
                        $result2[$i][$gender] = array('value' => 0, 'percent' => 0);
                    } else {
                        $result2[$i][$gender] = array('value' => $result2[$i][$gender], 'percent' => round(100 * $result2[$i][$gender] / $max));
                    }
                }
            } else {
                $result2[$i]['M'] = array('value' => 0, 'percent' => 0);
                $result2[$i]['F'] = array('value' => 0, 'percent' => 0);
            }
            ksort($result2);
        }
        //var_dump($result2);        exit;
        return View::make('stats.age', array('gender' => $result1, 'age' => $result2, 'average' => round($total_age / $total_count, 2)));
    }


    public function spaces()
    {
        $items = DB::select(DB::raw('select 
date_format(invoices.date_invoice, "%Y-%m") as period, 
SUM(invoices_items.amount) as total, 
if(`locations`.`name` is null,cities.name,concat(cities.name, \' > \',  `locations`.`name`)) as `kind` 

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
if(`locations`.`name` is null,cities.name,concat(cities.name, \' > \',  `locations`.`name`)) as `kind` 

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
        /*

                $items = DB::select(DB::raw('select
        date_format(locations_cost.period, "%Y-%m") as period,
        SUM(locations_cost.amount) as total,
        if(`locations`.`name` is null,cities.name,concat(cities.name, \' > \',  `locations`.`name`)) as `kind`

        from `locations_cost`
        join `locations` on u.default_location_id = `locations`.`id`
        left outer join cities on city_id = cities.id

        group by `period`, kind
        order by `period` ASC, kind ASC
        '));
                $costs = array();
                foreach ($items as $item) {
                    $costs[$item->kind][$item->period] += (float)$item->total;
                }
        */
        $costs = array(
            'Albi' => array(
                '2017-02' => 1350,
                '2017-07' => 1870,
                '2018-03' => 2500,
            ),
            'Montauban' => array(
                '2015-09' => 2050,
                '2016-12' => 3870,
                '2017-11' => 2400,
            ),
            'Toulouse > Carmes' => array(
                '2016-01' => 500,
                '2016-04' => 3500,
                '2016-09' => 10650,
            ),
            'Toulouse > Victor Hugo' => array(
                '2016-09' => 3400
            ),
            'Toulouse > Wilson' => array(
                '2015-01' => 7000,
                '2016-12' => 9280,
                '2017-06' => 12500,
            ),
            'Toulouse > Espace W' => array(
                '2017-08' => 300,
                '2017-10' => 4000,
            ),
        );

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
        $operations = array(
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
                '2017-11' => 550,

            ),
            'Toulouse > Wilson III' => array()
        );

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
                    if (!isset($datas[$location][substr($period, 0, 4)])) {
                        $datas[$location][substr($period, 0, 4)] = array();
                    }

                    $datas[$location][substr($period, 0, 4)][$period] = array(
                        'sales' => (float)$result[$location][$period],
                        'cost' => (float)$costs[$location][$period],
                        'balance' => (float)$result[$location][$period] - (float)$costs[$location][$period],
                    );
                }
            }
            krsort($datas[$location]);
        }
        $global = array();
        foreach ($datas as $location => $subdata) {
            foreach ($subdata as $year => $subdata2) {
                foreach ($subdata2 as $month => $values) {
                    foreach ($values as $k => $v) {
                        if (!isset($global[$year][$month][$k])) {
                            $global[$year][$month][$k] = 0;
                        }
                        $global[$year][$month][$k] += (float)$v;
                    }
                }
            }
        }

        $location_slugs = array();
        $items = DB::select(DB::raw('select 
`locations`.slug, 
if(`locations`.`name` is null,cities.name,concat(cities.name, \' > \',  `locations`.`name`)) as `kind` 

from `locations` 
left outer join cities on locations.city_id = cities.id'));
        foreach ($items as $item) {
            $location_slugs[$item->kind] = $item->slug;
        }

        return View::make('stats.spaces', array(
            'datas' => $datas,
            'location_slugs' => $location_slugs,
            'global' => $global,
        ));
    }

    public function spaces_details($space_slug, $period)
    {
        $invoices_ids = array();
        $items = DB::select(DB::raw(sprintf('select 
invoices.id 

from `invoices_items` 
inner join `invoices` on `invoice_id` = `invoices`.`id` and invoices.`type` = \'F\' 
left outer join `organisations` on invoices.`organisation_id` = `organisations`.`id` 
left outer join `ressources` on invoices_items.`ressource_id` = `ressources`.`id` 
left outer join `locations` on ressources.`location_id` = `locations`.`id` 

where ressources.ressource_kind_id NOT IN (' . RessourceKind::TYPE_COWORKING . ', ' . RessourceKind::TYPE_EXCEPTIONNAL . ')
AND date_format(invoices.date_invoice, "%%Y-%%m") = "%s"
AND locations.slug = "%s"
order by invoices.date_invoice ASC', $period, $space_slug)));
        foreach ($items as $item) {
            $invoices_ids[$item->id] = true;
        }

        $items = DB::select(DB::raw(sprintf('select 
invoices.id 
from `invoices_items` 
inner join `invoices` on `invoice_id` = `invoices`.`id` and `type` = \'F\' 
left outer join `organisations` on `organisation_id` = `organisations`.`id` 
join `ressources` on invoices_items.ressource_id = `ressources`.`id`

join users u on u.id = invoices_items.subscription_user_id 
join `locations` on u.default_location_id = `locations`.`id` 

where (`organisations`.`is_founder` = \'0\' or `organisation_id` is null) 
AND ressources.ressource_kind_id = ' . RessourceKind::TYPE_COWORKING . '
AND date_format(invoices.date_invoice, "%%Y-%%m") = "%s"
AND locations.slug = "%s"
', $period, $space_slug)));
        foreach ($items as $item) {
            $invoices_ids[$item->id] = true;
        }

        $items = Invoice::with(array('organisation', 'user'))->whereIn('id', array_keys($invoices_ids))->get();
        //var_dump(array_keys($invoices_ids));exit;

        return View::make('stats.spaces_details', array(
            'items' => $items,
            'space' => Location::where('slug', $space_slug)->first(),
            'period' => $period,
        ));
    }


    public function sales_per_ressource($ressource_id)
    {
        $items = DB::select(DB::raw(sprintf('SELECT  date_format(invoices.date_invoice, \'%%Y-%%m\') as occurs_at, round(sum(invoices_items.amount)) as amount
FROM `invoices_items` 
join invoices on invoices.id = invoices_items.invoice_id
AND invoices.type = \'F\'
WHERE 
invoices_items.ressource_id = %d
group by occurs_at DESC', $ressource_id)));

        return View::make('stats.sales_per_ressource', array(
                'ressource' => Ressource::where('id', $ressource_id)->first(),
                'items' => $items
            )
        );
    }
}
