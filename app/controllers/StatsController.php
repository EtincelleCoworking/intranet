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

    public function subscriptions(){
        $datas = array();
        foreach(Subscription::TotalPerMonth()->get() as $item){
            $datas[$item->period] = $item->total;
        }
        return View::make('stats.subscriptions', array('datas' => $datas));
    }

    public function sales_per_category()
    {
        $colors = array();
        $colors[] = '#a3e1d4';
        $colors[] = '#dedede';
        $colors[] = '#b5b8cf';

        $data = array();
        $data['Coworking'] = array('amount' => InvoiceItem::total()->coworking()->get()->first()->total, 'color' => array_shift($colors));
        $data['Location de salle'] = array('amount' => InvoiceItem::total()->RoomRental()->get()->first()->total, 'color' => array_shift($colors));
        $data['Autre'] = array('amount' => InvoiceItem::total()->other()->get()->first()->total, 'color' => array_shift($colors));

        $total = 0;
        foreach($data as $k => $v){
            $total += $data[$k]['amount'];
        }
        foreach($data as $k => $v){
            $data[$k]['ratio'] = $total?sprintf('%0.2f', 100* $data[$k]['amount'] / $total):0;
        }

        return View::make('stats.pie', array('data' => $data, 'total' => $total));
    }

}
