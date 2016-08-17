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
        return View::make('stats.subscriptions', array('datas' => $datas));
    }

    public function sales_per_category()
    {
        $colors = array();
        $colors[] = '#3f2860';
        $colors[] = '#90c5a9';
        $colors[] = '#7a9a95';
        $colors[] = '#ef6d3b';

        $data = array();
        foreach (InvoiceItem::withoutExceptionnals()->total()->byKind()->get() as $item) {
            $data[$item->kind ? $item->kind : self::LABEL_OTHERS] = array('amount' => $item->total, 'color' => array_shift($colors));
        }

        $total = 0;
        foreach ($data as $k => $v) {
            $total += $data[$k]['amount'];
        }
        foreach ($data as $k => $v) {
            $data[$k]['ratio'] = $total ? sprintf('%0.2f', 100 * $data[$k]['amount'] / $total) : 0;
        }

        return View::make('stats.pie', array('data' => $data, 'total' => $total));
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
            }else{
                $result2[$i]['M'] = array('value' => 0, 'percent' => 0);
                $result2[$i]['F'] = array('value' => 0, 'percent' => 0);
            }
            ksort($result2);
        }
        //var_dump($result2);        exit;
        return View::make('stats.age', array('gender' => $result1, 'age' => $result2));
    }
}
