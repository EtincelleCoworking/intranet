<?php

use Illuminate\Http\Response;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;


class AircallController extends BaseController
{
    protected function parsePhoneNumber($value)
    {
        if (strpos($value, '+33') === 0) {
            $value = '0' . substr($value, 3);
        }
        return preg_replace('/[^0-9]/', '', $value);
    }

    public function webhook($phone)
    {
        $json = json_decode(Request::getContent());
        $data['call'] = $json;

        $user = User::where('phone', $this->parsePhoneNumber($json->data->raw_digits))
            ->with('organisations')
            ->first();
        $data['user'] = null;
        if ($user) {
            $data['user'] = [
                'id' => $user->id,
                'firstname' => $user->firtname,
                'lastname' => $user->lastname,
                'email' => $user->email
            ];
            $data['organisations'] = [];
            foreach ($user->organisations as $organisation) {
                $data['organisations'][] = [
                    'id' => $organisation->id,
                    'name' => $organisation->name,
                    'address' => $organisation->address,
                    'postal_code' => $organisation->zipcode,
                    'city' => $organisation->city
                ];
            }
            $data['invoices'] = [];
            foreach (Invoice::where('type', 'F')->where('user_id', $user->id)->orderBy('date_invoice', 'DESC')->with('items')->get() as $invoice) {
                $data['invoices'][] = [
                    'id' => $invoice->id,
                    'reference' => $invoice->ident,
                    'occurs_at' => $invoice->date_invoice,
                    'paid_at' => $invoice->date_payment,
                    'amount' => $invoice->TotalWithTaxes()
                ];
            }
            $data['quotes'] = [];
            foreach (Invoice::where('type', 'D')->where('user_id', $user->id)->orderBy('date_invoice', 'DESC')->with('items')->get() as $invoice) {
                $data['quotes'][] = [
                    'id' => $invoice->id,
                    'reference' => $invoice->ident,
                    'occurs_at' => $invoice->date_invoice,
                    'paid_at' => $invoice->date_payment,
                    'amount' => $invoice->TotalWithTaxes()
                ];
            }
            $data['bookings'] = [];
            foreach (BookingItem::where('user_id', $user->id)->orderBy('date_invoice', 'DESC')->with('ressource')->with('ressource.location')->with('ressource.location.city')->get() as $item) {
                [
                    [
                        'id' => $item->id,
                        'start_at' => $item->start_at,
                        'end_at' => date('Y-m-d H:i:s', strtotime($item->start_at) + $item->duration * 60),
                        'location' => $item->ressource->location->city->name,
                        'room' => $item->ressource->name,
                        'participants' => $item->participant_count,
                        'is_confirmed' => $item->confirmed_at != null
                    ]];
            }

            $data['previous_calls'] = [/*
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
            ]
*/];
            $options = array(
                'cluster' => $_ENV['PUSHER_APP_CLUSTER'],
                'useTLS' => true,
                'debug' => true
            );
            $pusher = new \Pusher\Pusher(
                $_ENV['PUSHER_APP_KEY'],
                $_ENV['PUSHER_APP_SECRET'],
                $_ENV['PUSHER_APP_ID'],
                $options
            );
            $result = $pusher->trigger('aircall', 'call.created', $data);
            var_dump($result);
            //echo json_encode($data);
        }
    }
}
