<?php

/**
 * Cashflow Controller
 */
class StatsController extends BaseController
{

    public function overview(){
        $charts = array();
        foreach (InvoiceItem::TotalPerMonthWithoutStakeholders()->get() as $item) {
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

    public function charges(){
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

        foreach (InvoiceItem::TotalPerMonthWithoutStakeholders()->coworking()->get() as $item) {
            $charts['Coworking'][$item->period] = $item->total;
        }

        foreach (InvoiceItem::TotalPerMonthWithoutStakeholders()->roomRental()->get() as $item) {
            $charts['Location de salles'][$item->period] = $item->total;
        }

        foreach (InvoiceItem::TotalPerMonthWithoutStakeholders()->other()->get() as $item) {
            $charts['Autre'][$item->period] = $item->total;
        }

        foreach ($charts as $name => $chart) {
            ksort($charts[$name]);
        }


        return View::make('stats.ca', array('charts' => $charts));
    }


    public function customers()
    {
        $charts = array();

        foreach (InvoiceItem::TotalCountPerMonthWithoutStakeholders()->coworking()->get() as $item) {
            $charts['Coworking'][$item->period] = $item->total;
        }

        foreach (InvoiceItem::TotalCountPerMonthWithoutStakeholders()->roomRental()->get() as $item) {
            $charts['Location de salles'][$item->period] = $item->total;
        }

        foreach (InvoiceItem::TotalCountPerMonthWithoutStakeholders()->other()->get() as $item) {
            $charts['Autre'][$item->period] = $item->total;
        }

        foreach ($charts as $name => $chart) {
            ksort($charts[$name]);
        }


        return View::make('stats.ca', array('charts' => $charts));
    }

}
