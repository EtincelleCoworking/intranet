<?php

use GuzzleHttp\Client;

class UserEventHandler
{

    /**
     * Handle user login events.
     */
    public function onUserLogin($user)
    {
//        $user->last_login_at = new DateTime();
//        $user->save();

        // define active location
        //var_dump($_SERVER['REMOTE_ADDR']);
        $ip = LocationIp::where('name', '=', $_SERVER['REMOTE_ADDR'])->first();
        if ($ip) {
            $user->default_location_id = $ip->location->id;
            $user->save();
        }
        //var_dump($ip);
//        var_dump((string)$ip->location);
//        exit;
    }


    /**
     * Handle user logout events.
     */
    public function onUserShown($PastTime, $Location)
    {
        if (!$PastTime->user_id) {
            return true;
        }
        if (!$Location->slack_endpoint) {
            // https://hooks.slack.com/services/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
            return true;
        }
        $timeslot = PastTime::where('user_id', '=', $PastTime->user_id)
            ->where('date_past', '=', $PastTime->date_past)
            ->where('time_start', '<', $PastTime->time_start)
            ->orderBy('time_start', 'ASC')
            ->first();
        if (!$timeslot) {
            // this is first show of the day
            $user = $PastTime->user;

            if($user->gender = 'F'){
                $message = 'Elle est dans la place :';
            }else{
                $message = 'Il est dans la place :';
            }

            $attachments = array();
            $attachments[] = array(
                'title' => $user->fullname,
                'title_link' =>  URL::route('user_profile', array('id' => $user->id)),
                'text' =>  $user->bio_short,
                'image_url' =>  $user->avatarUrl
            );
            $client = new Client();

            $res = $client->request('POST', 'https://andruxnet-random-famous-quotes.p.mashape.com/?cat=famous',
                array('headers' => array(
                    "X-Mashape-Key" => "vO38VakGS1mshNCjVHqaNY1gFFipp1LFn8vjsnisUkgMJX5ZIY",
                    "Content-Type" => "application/x-www-form-urlencoded",
                    "Accept" => "application/json"
                ))
            );
            $quote =json_decode($res->getBody(), true);

            $attachments[] = array(
                'author_name' => $quote['author'],
                'text' =>  $quote['quote']
            );


            $this->slack($Location->slack_endpoint, array(
                'text' => $message,
                'attachments' => $attachments
            ));
        }
    }

    protected function slack($endpoint, $data)
    {
//        $data = array();
//        $data['text'] = $message;
//        if($icon){
//            $data['icon_emoji'] = $icon;
//        }
//
//        array(
//            "text"          =>  $message,
//            "icon_emoji"    =>  ':white_check_mark:',
//            'attachments'=> array(
//                array(
//                    'title'=>'title',
//                    'title_link'=>'https://frenchwork.fr',
//                    'text'=>'text',
//                )
//            )
//        )
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, "payload=" . json_encode($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);
        curl_close($ch);

        return $result;
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher $events
     * @return array
     */
    public function subscribe($events)
    {
        $events->listen('auth.login', 'UserEventHandler@onUserLogin');
        $events->listen('user.shown', 'UserEventHandler@onUserShown');
    }

}
