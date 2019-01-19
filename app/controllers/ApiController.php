<?php

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;


class AircallController extends BaseController
{
public function webhook(){
    $data['call'] = [
        'reference' => md5(time()),
        'start_at' => date('Y-m-d H:i:s')
    ];
    $data['user'] = [
        'firstname' => 'Sébastien',
        'lastname' => 'Hordeaux',
        'email' => 'sebastien@etincelle-coworking.com'
    ];
    $data['organisations'] = [
        [
            'id' => 1,
            'name' => 'Etincelle Coworking',
            'address' => '2 rue d\'Austerlitz',
            'postal_code' => '31000',
            'city' => 'Toulouse'
        ]];
    $data['invoices'] = [
        [
            'id' => 1,
            'reference' => 'F1901-0001',
            'occurs_at' => '2019-01-01',
            'paid_at' => null,
            'amount' => 120.00
        ]];
    $data['quotes'] = [
        [
            'id' => 1,
            'reference' => 'D1901-0001',
            'occurs_at' => '2019-01-01',
            'amount' => 120.00,
            'status' => 'pending'
        ]];
    $data['bookings'] = [
        [
            'id' => 1,
            'start_at' => '2019-01-01 10:00',
            'end_at' => '2019-01-01 12:00',
            'location' => 'Toulouse',
            'room' => 'Salle 10-12 persones',
            'participants' => 10,
            'is_confirmed' => false
        ]];

    $data['previous_calls'] = [
        [
            'id' => 1,
            'user' => [
                'firstname' => 'Sébastien',
                'lastname' => 'Hordeaux',
                'email' => 'sebastien@etincelle-coworking.com'
            ],
            'occurs_at' => '2019-01-01 12:00',
            'duration' => 72,
            'comment' => '...comment...'
        ]];
    //$result = $pusher->trigger('aircall', 'call.created', $data);
    echo json_encode($data);
}
}
