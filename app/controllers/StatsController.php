<?php

/**
 * Cashflow Controller
 */
class StatsController extends BaseController
{
    public function ca()
    {
        $charts = array();

        foreach (InvoiceItem::TotalPerMonthWithoutStakeholders()->coworking()->get() as $item) {
            $charts['Coworking'][$item->period] = $item->total;
        }

        foreach (InvoiceItem::TotalPerMonthWithoutStakeholders()->roomRental()->get() as $item) {
            $charts['Location de salles'][$item->period] = $item->total;
        }

        foreach (InvoiceItem::TotalPerMonthWithoutStakeholders()->get() as $item) {
            $charts['Produits (hors associés)'][$item->period] = $item->total;
        }

        foreach (InvoiceItem::TotalPerMonth()->get() as $item) {
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
