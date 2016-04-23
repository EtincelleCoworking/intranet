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
        $items = DB::select(DB::raw(sprintf('SELECT ii.subscription_user_id, i.days
          FROM invoices i JOIN invoices_items ii ON i.id = ii.invoice_id WHERE ii.ressource_id = %d AND ii.subscription_user_id IS NOT NULL ORDER BY i.days DESC', Ressource::TYPE_COWORKING)));
        $results = array();
        $users = array();
        foreach ($items as $item) {
            if (!isset($results[$item->days])) {
                $results[$item->days] = array();
            }
            $results[$item->days][$item->subscription_user_id] = '';
            $users[$item->subscription_user_id] = true;
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

}
