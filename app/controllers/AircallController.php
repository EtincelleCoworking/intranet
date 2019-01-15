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

    protected function sendInsightCard($json)
    {
        $uri = sprintf('https://%s:%s@api.aircall.io/v1/calls/%s/insight_cards',
            $_ENV['aircall_id'], $_ENV['aircall_secret'], $json->data->id);

        $data = json_encode( [
            'call_id' => $json->data->id,
            'creationDate' => time(),
            'contents' => [
                [
                    'type' => 'title',
                    'text' => 'TITLE',
                    'link' => 'https://www.google.com/'
                ],
                [
                    'type' => 'shortText',
                    'text' => 'SHORT_TEXT',
                    'label' => 'LABEL',
                    'link' => 'https://www.google.com/'
                ]
            ]
        ]);
        $ch = curl_init($uri);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($data))
        );

        curl_exec($ch);
        curl_close($ch);
    }

    public function webhook()
    {
        $json = json_decode(Request::getContent());
        $phone = $json->data->raw_digits;
        $data['call'] = $json;

        $user = User::where('phone', $this->parsePhoneNumber($phone))
            ->with('organisations')
            ->first();
        $data['user'] = null;
        $one_year_ago = date('Y-m-d', strtotime('-1 year'));

        if ($user) {
            $this->sendInsightCard($json);

            $data['user'] = [
                'id' => $user->id,
                'firstname' => $user->firstname,
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
            foreach (Invoice::where('type', 'F')->where('user_id', $user->id)->where('date_invoice', '>', $one_year_ago)->orderBy('date_invoice', 'DESC')->with('items')->get() as $invoice) {
                $data['invoices'][] = [
                    'id' => $invoice->id,
                    'reference' => $invoice->ident,
                    'occurs_at' => $invoice->date_invoice,
                    'paid_at' => $invoice->date_payment,
                    'amount' => $invoice->totalWithTaxes,
                    'edit_link' => route('invoice_modify', array('id' => $invoice->id))
                ];
            }
            $data['quotes'] = [];
            foreach (Invoice::where('type', 'D')->where('user_id', $user->id)->where('date_invoice', '>', $one_year_ago)->orderBy('date_invoice', 'DESC')->with('items')->get() as $invoice) {
                $data['quotes'][] = [
                    'id' => $invoice->id,
                    'reference' => $invoice->ident,
                    'occurs_at' => $invoice->date_invoice,
                    'paid_at' => $invoice->date_payment,
                    'amount' => $invoice->totalWithTaxes,
                    'edit_link' => route('invoice_modify', array('id' => $invoice->id))
                ];
            }
            $data['bookings'] = [];
            foreach (BookingItem::join('booking', 'booking.id', '=', 'booking_item.booking_id')->where('booking.user_id', $user->id)->where('start_at', '<', date('Y-m-d'))->limit(5)->orderBy('start_at', 'DESC')->with('ressource')->with('ressource.location')->with('ressource.location.city')->get() as $item) {
                $data['bookings'][] = [
                    'id' => $item->id,
                    'start_at' => $item->start_at,
                    'end_at' => date('Y-m-d H:i:s', strtotime($item->start_at) + $item->duration * 60),
                    'location' => $item->ressource->location->city->name,
                    'room' => $item->ressource->name,
                    'participants' => $item->participant_count,
                    'is_confirmed' => $item->confirmed_at != null
                ];
            }
            foreach (BookingItem::join('booking', 'booking.id', '=', 'booking_item.booking_id')->where('booking.user_id', $user->id)->where('start_at', '>', date('Y-m-d'))->orderBy('start_at', 'DESC')->with('ressource')->with('ressource.location')->with('ressource.location.city')->limit(5)->get() as $item) {
                $data['bookings'][] = [
                    'id' => $item->id,
                    'start_at' => $item->start_at,
                    'end_at' => date('Y-m-d H:i:s', strtotime($item->start_at) + $item->duration * 60),
                    'location' => $item->ressource->location->city->name,
                    'room' => $item->ressource->name,
                    'participants' => $item->participant_count,
                    'is_confirmed' => $item->confirmed_at != null
                ];
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
                'cluster' => $_ENV['pusher_app_cluster'],
                'useTLS' => true,
                'debug' => true
            );
            $pusher = new \Pusher\Pusher(
                $_ENV['pusher_app_key'],
                $_ENV['pusher_app_secret'],
                $_ENV['pusher_app_id'],
                $options
            );
            $result = $pusher->trigger('aircall', 'call.created', $data);
            var_dump($result);
            echo '<pre>';
            echo json_encode($data, JSON_PRETTY_PRINT);
            echo '</pre>';
            //echo json_encode($data);
        }
    }
}
