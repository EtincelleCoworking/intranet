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
    public function onUserShown($user, $PastTime, $Location)
    {
        if (!$PastTime->user_id) {
            return true;
        }
        if (!$Location->slack_endpoint) {
            return true;
        }
        // this is first show of the day
        $user = $PastTime->user;

        if ($user->slack_id) {
            $message = sprintf('@%s est là !', $user->slack_id);
        } else {
            $message = sprintf('%s est là !', $user->fullname);
        }

        $urls = array();
        $urls[] = sprintf('<%s|Intranet>', URL::route('user_profile', array('id' => $user->id)));
        if ($user->social_facebook) {
            $urls[] = sprintf('<%s|Facebook>', $user->social_facebook);
        }
        if ($user->social_linkedin) {
            $urls[] = sprintf('<%s|Linkedin>', $user->social_linkedin);
        }
        if ($user->twitter) {
            $urls[] = sprintf('<https://twitter.com/%s|Twitter>', $user->twitter);
        }
        if ($user->social_instagram) {
            $urls[] = sprintf('<%s|Instagram>', $user->social_instagram);
        }
        if ($user->social_github) {
            $urls[] = sprintf('<%s|GitHub>', $user->social_github);
        }
        $content = $user->bio_short . "\n\nVoir son profil sur " . implode(', ', $urls);

        $attachments = array();
        $attachments[] = array(
            'title' => $user->fullname,
            //'title_link' => '',
            'text' => $content,
            'image_url' => asset($user->avatarUrl)
        );
        $client = new Client();

        $res = $client->request('POST', 'https://andruxnet-random-famous-quotes.p.mashape.com/?cat=famous',
            array('headers' => array(
                "X-Mashape-Key" => "vO38VakGS1mshNCjVHqaNY1gFFipp1LFn8vjsnisUkgMJX5ZIY",
                "Content-Type" => "application/x-www-form-urlencoded",
                "Accept" => "application/json"
            ))
        );
        $quote = json_decode($res->getBody(), true);

        $attachments[] = array(
            'pretext' => 'Citation du jour :',
            'author_name' => $quote['author'],
            'text' => $quote['quote'],
        );

        $this->slack($Location->slack_endpoint, array(
            'text' => $message,
            'link_names' => 1,
            'attachments' => $attachments
        ));
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
