<?php


use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;

class BookingOrderController extends Controller
{

    public function invoicing()
    {
        $results = DB::select( DB::raw('SELECT users.id, users.firstname, users.lastname, users.email
         , (SELECT SUM(invoices_items.booking_hours) FROM invoices_items JOIN invoices ON invoices_items.invoice_id = invoices.id WHERE invoices.user_id = users.id) * 60 as quantity_ordered
         , (SELECT SUM(booking_item.duration) FROM booking_item JOIN booking ON booking_item.booking_id = booking.id WHERE booking.user_id = users.id AND booking_item.is_free = false)  as quantity_used
         FROM users 
         LEFT OUTER JOIN invoices ON invoices.user_id = users.id LEFT OUTER JOIN invoices_items ON invoices_items.invoice_id = invoices.id
         LEFT OUTER JOIN booking ON booking.user_id = users.id 
         WHERE (invoices_items.booking_hours IS NOT NULL OR booking.id IS NOT NULL) GROUP BY users.id ORDER BY users.lastname ASC, users.firstname ASC') );
        //var_dump($results);
        //exit;
        // durée de validité?

        return View::make('booking::orders', array('items' => $results));
    }
}