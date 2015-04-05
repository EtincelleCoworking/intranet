<?php

/**
 * Cashflow Controller
 */
class StatsController extends BaseController
{
    public function ca()
    {
        $charts = array();
        foreach (InvoiceItem::TotalPerMonthWithoutStakeholders() as $item) {
            $charts['Produits (hors associés)'][$item->period] = $item->total;
        }

        foreach (InvoiceItem::TotalPerMonth() as $item) {
            $charts['Produits'][$item->period] = $item->total;
        }

        foreach (ChargeItem::TotalPerMonth() as $item) {
            $charts['Charges'][$item->period] = $item->total;
        }

        foreach ($charts as $name => $chart) {
            ksort($charts[$name]);
        }


        return View::make('stats.ca', array('charts' => $charts, 'pending' => InvoiceItem::Pending()));
    }

}
