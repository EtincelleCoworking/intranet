<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;


class ApiCoffeeShopController extends BaseController
{
    public function store()
    {
        $json = json_decode(Request::getContent(), JSON_OBJECT_AS_ARRAY);
        $count = 0;
        foreach ($json['content'] as $product_slug => $quantity) {
            $order = new CoffeeShopOrder();
            $order->user_id = $json['user_id'];
            $order->occurs_at = $json['occurs_at'];
            $order->product_slug = $product_slug;
            $order->quantity = $quantity;
            if ($order->save()) {
                $count++;
            }
        }

        $result = array(
            'status' => 'ok',
            'message' => sprintf('La commande de %d produit(s) a été enregistrée', $count)
        );

        $response = new \Illuminate\Http\Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->setContent(json_encode($result));
        return $response;
    }


    public function history($user_id)
    {
        $data = array();
        $items = DB::select(sprintf('SELECT * FROM coffeeshop_orders WHERE user_id = %d and invoice_id IS NULL ORDER BY occurs_at DESC', $user_id));
        foreach ($items as $item) {
            $data[] = [
                'product' => $item->product_slug,
                'quantity' => $item->quantity,
                'occurs_at' => $item->occurs_at
            ];
        }

        $result = array(
            'status' => 'ok',
            'data' => $data
        );

        $response = new \Illuminate\Http\Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET');
        $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, X-Auth-Token');
        $response->setContent(json_encode($result));
        return $response;
    }

    public function pending()
    {
        $data = array();
        $items = DB::select(sprintf('SELECT user_id, concat(users.firstname, " ", users.lastname) as username, SUM(quantity) as pending_item_count FROM coffeeshop_orders join users on users.id = coffeeshop_orders.user_id WHERE invoice_id IS NULL GROUP BY user_id'));
        foreach ($items as $item) {
            $data[$item->user_id] = [
                'name' => $item->username,
                'pending' => $item->pending_item_count
            ];
        }

        $result = array(
            'status' => 'ok',
            'data' => $data
        );

        $response = new \Illuminate\Http\Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET');
        $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, X-Auth-Token');
        $response->setContent(json_encode($result));
        return $response;
    }

    public function stats()
    {
        $data = array();
        $items = DB::select(sprintf('select product_slug, count(*) as nb from coffeeshop_orders group by product_slug order by product_slug asc'));
        foreach ($items as $item) {
            $data[$item->product_slug] = $item->nb;
        }

        $result = array(
            'status' => 'ok',
            'data' => $data
        );

        $response = new \Illuminate\Http\Response();
        $response->headers->set('Content-Type', 'application/json');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET');
        $response->headers->set('Access-Control-Allow-Headers', 'Origin, Content-Type, X-Auth-Token');
        $response->setContent(json_encode($result));
        return $response;
    }
}