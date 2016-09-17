<?php


use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;

class BookingOrderController extends Controller
{

    public function invoicing()
    {
        var_dump(BookingOrder::overview()->get());

        $params = array();
        return View::make('booking::invoicing', $params);
    }
}