<?php

/**
 * Cashflow Controller
 */
class CashflowController extends BaseController
{
    public function vat()
    {
        $paid = array();
        $paid_rates = array();
        $sum = array();
        foreach (ChargeItem::TotalTVA() as $item) {
            $paid[$item->days][$item->value] = $item->total;
            $paid_rates[$item->value] = true;
            if (empty($sum[$item->days])) {
                $sum[$item->days] = 0;
            }
            $sum[$item->days] += $item->total;
        }

        $received = array();
        $received_rates = array();
        foreach (InvoiceItem::TotalTVA()->get() as $item) {
            $received[$item->days][$item->value] = $item->total;
            $received_rates[$item->value] = true;
            if (empty($sum[$item->days])) {
                $sum[$item->days] = 0;
            }
            $sum[$item->days] -= $item->total;
        }

        krsort($sum);
        ksort($paid_rates);
        ksort($received_rates);

        $month2quarter = array(
            '01' => 1,
            '02' => 1,
            '03' => 1,
            '04' => 2,
            '05' => 2,
            '06' => 2,
            '07' => 3,
            '08' => 3,
            '09' => 3,
            '10' => 4,
            '11' => 4,
            '12' => 4,
        );

        $overview = array();
        foreach($sum as $period => $value){
            if(preg_match('/(.+)-(.+)/', $period, $matchs)){
                $quarter=sprintf('%s-Q%s', $matchs[1], $month2quarter[$matchs[2]]);
                if(!isset($overview[$quarter])){
                    $overview[$quarter] = 0;
                }
                $overview[$quarter] += $value;

            }
        }

        krsort($overview);


        return View::make('cashflow.vat', array('paid' => $paid, 'received' => $received,
            'sum' => $sum,
            'overview' => $overview,
            'paid_rates' => array_keys($paid_rates), 'received_rates' => array_keys($received_rates)));
    }

}
