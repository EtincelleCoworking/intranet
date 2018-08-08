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
        foreach (InvoiceItem::TotalPerMonth()->WithoutExceptionnals()->get() as $item) {
            $charts['Produits (hors exceptionnels)'][$item->period] = $item->total;
        }

        foreach (InvoiceItem::TotalPerMonth()->get() as $item) {
            $charts['Produits'][$item->period] = $item->total;
        }

        $operations = Location::getOperationTweaks();
        foreach ($operations as $space_name => $data) {
            foreach ($data as $period => $value) {
                if (!isset($charts['Produits'][$period])) {
                    $charts['Produits'][$period] = 0;
                }
                if (!isset($charts['Produits (hors exceptionnels)'][$period])) {
                    $charts['Produits (hors exceptionnels)'][$period] = 0;
                }
                $charts['Produits'][$period] += $value;
                $charts['Produits (hors exceptionnels)'][$period] += $value;
            }
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

    public function sales_per_category($period = null, $location_id = null)
    {
        $colors = array();
        $colors[] = '#3f2860';
        $colors[] = '#90c5a9';
        $colors[] = '#7a9a95';
        $colors[] = '#ef6d3b';

        $data = array();
        $query = InvoiceItem::withoutExceptionnals()->total()->byKind();
        if ($location_id) {
            $query->byLocation($location_id);
        }
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

        return View::make('stats.pie_per_category', array(
            'data' => $data,
            'location_id' => $location_id,
            'locations' => Location::selectAll(false),
            'total' => $total,
            'period' => $period));
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
        $datas = Location::getStats();
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
        // ddd
        $items = DB::select(DB::raw('select 
`locations`.slug, 
#if(`locations`.`name` is null,cities.name,concat(cities.name, \' > \',  `locations`.`name`)) as `kind` 
if(`locations`.`name` is null,cities.name,`locations`.`name` ) as `kind` 
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
        $from = date('Y-m-01', strtotime('-6 months'));
        $to = date('Y-m-d');
        $ressources = array($ressource_id);


        $sql = sprintf('select 
organisations.name, sum(invoices_items.amount) as amount
from `invoices_items` 
  inner join `invoices` on `invoice_id` = `invoices`.`id` and `type` = \'F\' 
  inner join `organisations` on `organisation_id` = `organisations`.`id` 
where invoices.id IN 
  (SELECT DISTINCT(invoices_items.invoice_id) 
    FROM invoices_items
      JOIN invoices on invoice_id = invoices.id
      JOIN past_times ON past_times.invoice_id = invoices.id
  WHERE invoices_items.ressource_id IN (' . implode(', ', $ressources) . ')
    AND past_times.date_past BETWEEN "' . $from . '" AND "' . $to . '"
  )
GROUP BY organisations.id ORDER by amount DESC');
        $items = DB::select(DB::raw($sql));
        $result = array();
        foreach ($items as $item) {
            $result[$item->name] = array('amount' => $item->amount);
        }

        $ressource = Ressource::where('id', $ressource_id)->first();
        return View::make('stats.sales_per_ressource', array(
                'ressource' => $ressource,
                'items' => $ressource->getStats(),
                'top_customers' => $result,
                'top_customers_from' => $from,
                'top_customers_to' => $to,
            )
        );
    }


    public function top_customers()
    {
        $from = date('Y-m-d', strtotime('-3 months'));
        $to = date('Y-m-d');
        $ressources = array(31);

        $sql = sprintf('select 
organisations.name, sum(invoices_items.amount) as amount
from `invoices_items` 
inner join `invoices` on `invoice_id` = `invoices`.`id` and `type` = \'F\' 
left outer join `organisations` on `organisation_id` = `organisations`.`id` 
where invoices.id IN 
  (SELECT DISTINCT(invoices_items.invoice_id) 
    FROM invoices_items
      JOIN invoices on invoice_id = invoices.id
      JOIN past_times ON past_times.invoice_id = invoices.id
  WHERE invoices_items.ressource_id IN (' . implode(', ', $ressources) . ')
    AND past_times.date_past BETWEEN "' . $from . '" AND "' . $to . '"
  )
GROUP BY organisations.id ORDER by amount DESC');
        echo $sql;
        $items = DB::select(DB::raw($sql));
        $result = array();
        foreach ($items as $item) {
            $result[$item->name] = $item->amount;
        }

        var_dump($result);
        exit;

        return View::make('stats.top_customers', array(
                'items' => $result
            )
        );
    }

    private function Gradient($HexFrom, $HexTo, $ColorSteps)
    {
        $FromRGB['r'] = hexdec(substr($HexFrom, 0, 2));
        $FromRGB['g'] = hexdec(substr($HexFrom, 2, 2));
        $FromRGB['b'] = hexdec(substr($HexFrom, 4, 2));

        $ToRGB['r'] = hexdec(substr($HexTo, 0, 2));
        $ToRGB['g'] = hexdec(substr($HexTo, 2, 2));
        $ToRGB['b'] = hexdec(substr($HexTo, 4, 2));

        $StepRGB['r'] = ($FromRGB['r'] - $ToRGB['r']) / ($ColorSteps - 1);
        $StepRGB['g'] = ($FromRGB['g'] - $ToRGB['g']) / ($ColorSteps - 1);
        $StepRGB['b'] = ($FromRGB['b'] - $ToRGB['b']) / ($ColorSteps - 1);

        $GradientColors = array();

        for ($i = 0; $i <= $ColorSteps; $i++) {
            $RGB['r'] = floor($FromRGB['r'] - ($StepRGB['r'] * $i));
            $RGB['g'] = floor($FromRGB['g'] - ($StepRGB['g'] * $i));
            $RGB['b'] = floor($FromRGB['b'] - ($StepRGB['b'] * $i));

            $HexRGB['r'] = sprintf('%02x', ($RGB['r']));
            $HexRGB['g'] = sprintf('%02x', ($RGB['g']));
            $HexRGB['b'] = sprintf('%02x', ($RGB['b']));

            $GradientColors[] = implode(NULL, $HexRGB);
        }
        $GradientColors = array_filter($GradientColors, function ($val) {
            return (strlen($val) == 6 ? true : false);
        });
        return $GradientColors;
    }

    public function coworking()
    {
        $start_at = date('Y-m-d', strtotime('-10 days'));
        $end_at = date('Y-m-d 23:59:59');
        if (Input::has('filtre_start')) {
            $date_start_explode = explode('/', Input::get('filtre_start'));
            if (count($date_start_explode) == 3) {
                $start_at = $date_start_explode[2] . '-' . $date_start_explode[1] . '-' . $date_start_explode[0];
            }
        }
        if (Input::has('filtre_end')) {
            $date_end_explode = explode('/', Input::get('filtre_end'));
            if (count($date_end_explode) == 3) {
                $end_at = $date_end_explode[2] . '-' . $date_end_explode[1] . '-' . $date_end_explode[0];
            }
        }

        $city = Auth::user()->location->city;
        $data = DB::select('SELECT occurs_at, count, 100 * count / capacity as percent 
            FROM stats_coworking_usage 
            JOIN locations on locations.id = stats_coworking_usage.location_id
            WHERE occurs_at > "' . $start_at . '" AND occurs_at < "' . $end_at . ' 23:59:59" 
              AND locations.city_id = ' . $city->id . '
            ORDER BY occurs_at DESC');

        $combined = Input::get('filtre_combined');

        $min_time = 7;
        $excluded = array();
        for ($i = 0; $i < $min_time; $i++) {
            $excluded[] = sprintf('%02d:00', $i);
        }

        $overall = array();

        $items = array();
        foreach ($data as $item) {
            $date = substr($item->occurs_at, 0, 10);
            $day_id = date('N', strtotime($date));
            switch ($day_id) {
                case 1:
                    $dateFmt = 'Lun';
                    break;
                case 2:
                    $dateFmt = 'Mar';
                    break;
                case 3:
                    $dateFmt = 'Mer';
                    break;
                case 4:
                    $dateFmt = 'Jeu';
                    break;
                case 5:
                    $dateFmt = 'Ven';
                    break;
                case 6:
                    $dateFmt = 'Sam';
                    break;
                case 7:
                    $dateFmt = 'Dim';
                    break;
                default:
                    $dateFmt = '';
            }

            $time = substr($item->occurs_at, 11, 5);
            if (!in_array($time, $excluded)) {
                if ($combined) {
                    $items[$dateFmt][$time]['count'][] = $item->count;
                    $items[$dateFmt][$time]['percent'][] = $item->percent;
                } else {
                    $dateFmt .= ' ' . date('d/m', strtotime($date));
                    $items[$dateFmt][$time] = array(
                        'count' => $item->count,
                        'percent' => $item->percent,
                        'percent_step' => round($item->percent / 10) * 10,
                    );

                }
            }

            if (in_array($day_id, array(1, 2, 3, 4, 5)) && ($time >= '09:00') && ($time < '18:00')) {
                $overall[] = $item->percent;
            }
        }

        if ($combined) {
            $items = array(
                'Lundi' => @$items['Lun'],
                'Mardi' => @$items['Mar'],
                'Mercredi' => @$items['Mer'],
                'Jeudi' => @$items['Jeu'],
                'Vendredi' => @$items['Ven'],
                'Samedi' => @$items['Sam'],
                'Dimanche' => @$items['Dim'],
            );
        }

        $colors = array_merge(array('ffffff'),
            array_values($this->Gradient("00FF00", "FFFF00", 5)),
            array_values($this->Gradient("FFFF00", "FF0000", 5))
        );

        return View::make('stats.coworking', array(
                'items' => $items,
                'colors' => $colors,
                'min_time' => $min_time,
                'city' => $city,
                'combined' => $combined,
                'start_at' => $start_at,
                'end_at' => $end_at,
                'overall' => array_sum($overall) / count($overall)
            )
        );
    }
}
