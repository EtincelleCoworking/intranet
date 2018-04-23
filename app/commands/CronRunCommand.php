<?php

# Cron job command for Laravel 4.2
# Inspired by Laravel 5's new upcoming scheduler (https://laravel-news.com/2014/11/laravel-5-scheduler)
#
# Author: Soren Schwert (GitHub: sisou)
#
# Requirements:
# =============
# PHP 5.4
# Laravel 4.2 ? (not tested with 4.1 or below)
# A desire to put all application logic into version control
#
# Installation:
# =============
# 1. Put this file into your app/commands/ directory and name it 'CronRunCommand.php'.
# 2. In your artisan.php file (found in app/start/), put this line: 'Artisan::add(new CronRunCommand);'.
# 3. On the server's command line, run 'php artisan cron:run'. If you see a message telling you the
#    execution time, it works!
# 4. On your server, configure a cron job to call 'php-cli artisan cron:run >/dev/null 2>&1' and to
#    run every five minutes (*/5 * * * *)
# 5. Observe your laravel.log file (found in app/storage/logs/) for messages starting with 'Cron'.
#
# Usage:
# ======
# 1. Have a look at the example provided in the fire() function.
# 2. Have a look at the available schedules below (starting at line 132).
# 4. Code your schedule inside the fire() function.
# 3. Done. Now go push your cron logic into version control!

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CronRunCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cron:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run the scheduler';

    /**
     * Current timestamp when command is called.
     *
     * @var integer
     */
    protected $timestamp;

    /**
     * Hold messages that get logged
     *
     * @var array
     */
    protected $messages = array();

    /**
     * Specify the time of day that daily tasks get run
     *
     * @var string [HH:MM]
     */
    protected $runAt = '03:00';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->timestamp = time();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        /**
         * EXAMPLES
         */
        /*
                // You can use any of the available schedules and pass it an anonymous function
                $this->everyFiveMinutes(function()
                {
                    // In the function, you can use anything that you can use everywhere else in Laravel.

                    // Like models:
                    $affectedRows = User::where('logged_in', true)->update(array('logged_in' => false)); // Not really useful, but possible

                    // Or call artisan commands:
                    Artisan::call('auth:clear-reminders');

                    // You can append messages to the cron log like so:
                    $this->messages[] = $affectedRows . ' users logged out';
                });

                // Another example:
                // Send the admin an email every day
                $this->dailyAt('09:00', function()
                {
                    // This uses the mailer class
                    Mail::send('hello', array(), function($message)
                    {
                        $message->to('admin@mydomain.com', 'Cron Job')->subject('I am still running!');
                    });
                });
        */

        //region Assign invoices_items.subscription_user_id
        $this->daily(function () {
            Log::info('Cron: Assign invoices_items.subscription_user_id');
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::statement('update invoices_items join invoices on invoices_items.invoice_id = invoices.id 
set `subscription_user_id` = invoices.user_id 
where  subscription_user_id is null;');
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        });
        //endregion

        //region Assign past_times to invoices (active subscriptions)
        $this->hourly(function () {
            Log::info('Cron: Assign past_times to invoices (active subscriptions)');

            DB::statement('UPDATE past_times
    SET invoice_id = (
        SELECT invoices.id
        FROM invoices
            JOIN invoices_items ON invoices.id = invoices_items.invoice_id
        WHERE invoices_items.ressource_id = past_times.ressource_id
            AND invoices_items.subscription_user_id = past_times.user_id
            AND invoices.type = "F"
            AND past_times.date_past BETWEEN invoices_items.subscription_from AND invoices_items.subscription_to
            AND past_times.invoice_id = 0
        LIMIT 1
    )
    WHERE (invoice_id is null or invoice_id = 0)
        AND ressource_id = ' . Ressource::TYPE_COWORKING);
        });
        //endregion

        //region Mark past time as gift for some members
        $this->hourly(function () {
            Log::info('Cron: Mark past time as gift for some members');
            DB::statement('UPDATE past_times
    SET is_free = true
    WHERE (invoice_id is null or invoice_id = 0)
        AND ressource_id = ' . Ressource::TYPE_COWORKING . '
        AND user_id in (SELECT id FROM users WHERE free_coworking_time = 1)');
        });
        //endregion

        $this->checkMonitoring();
        $this->everyFiveMinutes(array($this, 'sendSmsNotificationForCloseMeetings'));


        $this->daily(array($this, 'generateMissingBookingKeyForUsers'));
        $this->hourly(function () {
            Artisan::call('odoo:update', array('--users' => true));
        });

        $this->dailyAt('23:00', function () {
            Artisan::call('odoo:update', array('--pending-pos-to-orders' => true));
        });

        $this->daily(function () {
            Artisan::call('etincelle:update-member-status');
        });

        $this->dailyAt('04:00', array($this, 'assignCoworkingPackItemsToUsers'));
        $this->cleanPhoneboxSession();

        $this->finish();
    }

    protected function sendSmsNotificationForCloseMeetings()
    {
        Log::info('Cron: Send SMS notification to close meetings');
        $sql = 'SELECT users.id as user_id, users.firstname, users.lastname, users.email, users.phone,
        booking.id as booking_id, booking_item.id as booking_item_id, booking.title as booking_title, booking_item.start_at, DATE_ADD(booking_item.start_at, INTERVAL booking_item.duration MINUTE) as end_at, booking.sms_uid,
        if(`locations`.`name` is null,cities.name,concat(cities.name, \' > \',  `locations`.`name`)) as `kind`,
        ressources.id as ressource_id, ressources.name as ressource_name
        FROM booking join booking_item ON booking.id = booking_item.booking_id
join ressources on ressources.id = booking_item.ressource_id
join locations on ressources.location_id = locations.id
        join cities on locations.city_id = cities.id
join users on booking.user_id = users.id
        WHERE start_at BETWEEN "' . date('Y-m-d') . ' 00:00:00" AND  "' . date('Y-m-d') . ' 23:59:59"
and locations.city_id = 1
group by booking.id
        ORDER BY ressources.id ASC, booking_item.start_at ASC, booking_item.duration DESC';
        $ressources = array();
        $items = DB::select(DB::raw($sql));
        foreach ($items as $item) {
            if (!isset($ressources[$item->ressource_id])) {
                $ressources[$item->ressource_id] = array();
                $ressources[$item->ressource_id]['name'] = $item->ressource_name;
                $ressources[$item->ressource_id]['location'] = $item->kind;
                $ressources[$item->ressource_id]['bookings'] = array();
            }
            $ressources[$item->ressource_id]['bookings'][] = array(
                'user' => array(
                    'id' => $item->user_id,
                    'firstname' => $item->firstname,
                    'lastname' => $item->lastname,
                    'email' => $item->email,
                    'phone' => $item->phone,
                ),
                'id' => $item->booking_id,
                'item_id' => $item->booking_item_id,
                'title' => $item->booking_title,
                'start_at' => $item->start_at,
                'end_at' => $item->end_at,
                'sms_uid' => $item->sms_uid,
            );
        }

        $only_if_after = new DateTime();
        $only_if_after->add(new DateInterval('PT30M'));
        $only_if_after = $only_if_after->format('Y-m-d H:i:s');
        $now = date('Y-m-d H:i:s');

        $client = null;
        $warningDelay = 60;
        foreach ($ressources as $ressource_id => $data) {

            $previous = array_shift($data['bookings']);
            while (count($data['bookings']) > 0) {
                $current = array_shift($data['bookings']);

                $current_start_at = strtotime($current['start_at']);
                $previous_ends_at = strtotime($previous['end_at']);

                $gap = ($current_start_at - $previous_ends_at) / 60;

                if ($gap > $warningDelay) {
                    // enough time
                    Log::info('enough time');
                } elseif ($previous['user']['id'] == $current['user']['id']) {
                    // same user, do no notify
                    Log::info('same user, do no notify');
                } else {
                    $phone = CronRunCommand::getPhoneNumberFormattedForSms($previous['user']['phone']);
                    if (!$phone) {
                        // pas de téléphone renseigné ou pas un portable ou pas au bon format
                        Log::info(sprintf('pas de téléphone renseigné ou pas un portable ou pas au bon format (%s)', $previous['user']['phone']));
                    } elseif (!empty($previous['sms_uid'])) {
                        Log::info('sms déjà envoyé');
                        // sms déjà envoyé
                    } elseif ($previous['start_at'] > $only_if_after) {
                        Log::info('trop tôt');
                    } elseif ($current['start_at'] < $now) {
                        Log::info('autre réservation déjà commencée en théorie');
                    } else {
                        $twilio_number = Config::get('etincelle.twilio_sms_number');
                        if (null == $client) {
                            $account_sid = Config::get('etincelle.twilio_account_sid');
                            $auth_token = Config::get('etincelle.twilio_auth_token');

                            $client = new \Twilio\Rest\Client($account_sid, $auth_token);
                        }

                        $message_content = sprintf('Bonjour, "%1$s" est réservé à %2$s. Merci de libérer la salle avant %4$s comme prévu. @Etincelle',
                            $data['name'],
                            date('H\hi', $current_start_at),
                            date('H\hi', strtotime($previous['start_at'])),
                            date('H\hi', $previous_ends_at));

                        $this->slack(Config::get('etincelle.slack_staff_toulouse'), array(
                            'text' => sprintf('SMS envoyé à %s %s <%s> au %s', $previous['user']['firstname'], $previous['user']['lastname'], $previous['user']['email'], User::formatPhoneNumber($phone)),
                            'attachments' => array(
                                array(
                                    "text" => $message_content
                                )),
                            'link_names' => 1,
                            //'attachments' => $attachments
                        ));
                        Log::info($message_content);
                        $result = $client->messages->create(
                            $phone,
                            array(
                                'from' => $twilio_number,
                                'body' => $message_content
                            )
                        );
                        $sql = sprintf('UPDATE booking SET sms_uid = "%s" WHERE id = %d', $result->sid, $previous['id']);
                        DB::statement($sql);
                    }
                }

                $previous = $current;
            }
        }
    }

    static function getPhoneNumberFormattedForSms($data)
    {
        $data = preg_replace('/[^0-9]/', '', $data);
        switch (strlen($data)) {
            case 9:
                $data = '0' . $data;
                break;
            case 10:
                break;
            default:
                return false;
        }
        if (!in_array(substr($data, 0, 2), array('06', '07'))) {
            return false;
        }

        return '+33' . substr($data, 1);
    }

    protected function finish()
    {
        // Write execution time and messages to the log
        $executionTime = round(((microtime(true) - $_SERVER['REQUEST_TIME_FLOAT']) * 1000), 3);
        Log::info('Cron: execution time: ' . $executionTime . ' | ' . implode(', ', $this->messages));
    }

    /**
     * AVAILABLE SCHEDULES
     */

    protected function everyFiveMinutes(callable $callback)
    {
        if ((int)date('i', $this->timestamp) % 5 === 0) call_user_func($callback);
    }

    protected function everyTenMinutes(callable $callback)
    {
        if ((int)date('i', $this->timestamp) % 10 === 0) call_user_func($callback);
    }

    protected function everyFifteenMinutes(callable $callback)
    {
        if ((int)date('i', $this->timestamp) % 15 === 0) call_user_func($callback);
    }

    protected function everyThirtyMinutes(callable $callback)
    {
        if ((int)date('i', $this->timestamp) % 30 === 0) call_user_func($callback);
    }

    /**
     * Called every full hour
     */
    protected function hourly(callable $callback)
    {
        if (date('i', $this->timestamp) === '00') call_user_func($callback);
    }

    /**
     * Called every hour at the minute specified
     *
     * @param  integer $minute
     */
    protected function hourlyAt($minute, callable $callback)
    {
        if ((int)date('i', $this->timestamp) === $minute) call_user_func($callback);
    }

    /**
     * Called every day
     */
    protected function daily(callable $callback)
    {
        if (date('H:i', $this->timestamp) === $this->runAt) call_user_func($callback);
    }

    /**
     * Called every day at the 24h-format time specified
     *
     * @param  string $time [HH:MM]
     */
    protected function dailyAt($time, callable $callback)
    {
        if (date('H:i', $this->timestamp) === $time) call_user_func($callback);
    }

    /**
     * Called every day at 12:00am and 12:00pm
     */
    protected function twiceDaily(callable $callback)
    {
        if (date('h:i', $this->timestamp) === '12:00') call_user_func($callback);
    }

    /**
     * Called every weekday
     */
    protected function weekdays(callable $callback)
    {
        $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
        if (in_array(date('D', $this->timestamp), $days) && date('H:i', $this->timestamp) === $this->runAt) call_user_func($callback);
    }

    protected function mondays(callable $callback)
    {
        if (date('D', $this->timestamp) === 'Mon' && date('H:i', $this->timestamp) === $this->runAt) call_user_func($callback);
    }

    protected function tuesdays(callable $callback)
    {
        if (date('D', $this->timestamp) === 'Tue' && date('H:i', $this->timestamp) === $this->runAt) call_user_func($callback);
    }

    protected function wednesdays(callable $callback)
    {
        if (date('D', $this->timestamp) === 'Wed' && date('H:i', $this->timestamp) === $this->runAt) call_user_func($callback);
    }

    protected function thursdays(callable $callback)
    {
        if (date('D', $this->timestamp) === 'Thu' && date('H:i', $this->timestamp) === $this->runAt) call_user_func($callback);
    }

    protected function fridays(callable $callback)
    {
        if (date('D', $this->timestamp) === 'Fri' && date('H:i', $this->timestamp) === $this->runAt) call_user_func($callback);
    }

    protected function saturdays(callable $callback)
    {
        if (date('D', $this->timestamp) === 'Sat' && date('H:i', $this->timestamp) === $this->runAt) call_user_func($callback);
    }

    protected function sundays(callable $callback)
    {
        if (date('D', $this->timestamp) === 'Sun' && date('H:i', $this->timestamp) === $this->runAt) call_user_func($callback);
    }

    /**
     * Called once every week (basically the same as using sundays() above...)
     */
    protected function weekly(callable $callback)
    {
        if (date('D', $this->timestamp) === 'Sun' && date('H:i', $this->timestamp) === $this->runAt) call_user_func($callback);
    }

    /**
     * Called once every week at the specified day and time
     *
     * @param  string $day [Three letter format (Mon, Tue, ...)]
     * @param  string $time [HH:MM]
     */
    protected function weeklyOn($day, $time, callable $callback)
    {
        if (date('D', $this->timestamp) === $day && date('H:i', $this->timestamp) === $time) call_user_func($callback);
    }

    /**
     * Called each month on the 1st
     */
    protected function monthly(callable $callback)
    {
        if (date('d', $this->timestamp) === '01' && date('H:i', $this->timestamp) === $this->runAt) call_user_func($callback);
    }

    /**
     * Called each year on the 1st of January
     */
    protected function yearly(callable $callback)
    {
        if (date('m', $this->timestamp) === '01' && date('d', $this->timestamp) === '01' && date('H:i', $this->timestamp) === $this->runAt) call_user_func($callback);
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

        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded'));
        curl_setopt($ch, CURLOPT_POSTFIELDS, "payload=" . urlencode(json_encode($data)));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);

        $errors = curl_error($ch);
        if ($errors) {
            Log::error($errors, array('context' => 'user.shown'));
        }
        $responseCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        Log::info(sprintf('Slack payload: %s', json_encode($data)), array('context' => 'user.shown'));
        Log::info(sprintf('Slack response (HTTP Code: %s): %s', $responseCode, $result), array('context' => 'user.shown'));
        curl_close($ch);

        return $result;
    }

    protected function generateMissingBookingKeyForUsers()
    {
        DB::statement('UPDATE users SET booking_key = md5(UUID())  WHERE booking_key IS NULL');
    }

    protected function assignCoworkingPackItemsToUsers()
    {
        /*
        $sql = 'SELECT invoices_items.invoice_id as invoice_id, invoices_items.id as invoice_item_id, invoices_items.coworking_pack_item_user_id as user_id, COUNT(coworking_prepaid_pack_item.*) as remaining_count
         FROM coworking_prepaid_pack_item
           JOIN invoices_items ON invoices_items.id = coworking_prepaid_pack_item.invoice_item_id
           LEFT OUTER JOIN past_times ON coworking_prepaid_pack_item.past_time_id = past_times.id
         WHERE coworking_prepaid_pack_item.past_time_id IS NULL
          AND invoices_items.coworking_pack_item_user_id IS NOT NULL
         GROUP BY invoices_items.coworking_pack_item_user_id
         HAVING cnt > 0';


        // Pour chaque utilisateur qui a un compte prépayé ouvert
        //   Pour chaque temps passé non encore traité
        //     Si le nb d'unité consommée est >= nombre d'unité disponible
        //       - Associer le coworking_prepaid_pack_item.invoice_item_id
        //       - Associer le coworking_prepaid_pack_item.past_time_id
        //       - past_time.comment = x/10
        //       - past_time.invoice_id = ..
        //     S'il en manque >> Alerte
        //     Si on arrive à la fin >> Proposer de renouveller / option de renouvellement automatique?


        // Faire un tableau de bord des comptes prépayés
        // Utilisateur - nb consommé / nb commandé - date de commandé - date de validité


        $items = DB::select(DB::raw($sql));
        foreach ($items as $item) { // list all prepaid orders not completed
            $remaining_count = $item->remaining_count;
            // get all unassigned PastTime items for this user, ordered by time
            foreach (PastTime::where('user_id', '=', $item->user_id)
                         ->where('is_free', '=', false)
                         ->where('invoice_id', '=', false)// ou NULL?
                ->orderBy('date_past', 'ASC')->get() as $open_past_time) {

                    $duration = min(2, ceil(((strtotime($open_past_time->time_end) - strtotime($open_past_time->time_start)) / 3600) / PastTimeController::COWORKING_HALF_DAY_MAX_DURATION));

                    if($remaining_count >= $duration){
                        while($duration >0){
                            $open_past_time->invoice_id = $item->invoice_id;
                            if(!empty($open_past_time->comment)){
                                $open_past_time->comment .= ' + ';
                            }
                            $open_past_time->comment .= sprintf('%d / 10', $remaining_count--);
                            $open_past_time->save();
                            $duration--;


                        $prepaid_item = new CoworkingPrepaidPackItem();
                        $prepaid_item->invoice_item_id = $item->invoice_item_id;
                        $prepaid_item->past_time_id = $open_past_time->id;
                    }
                }


                $invoice_line->text .= sprintf("\n - %s de %s à %s (%s demi journée%s)", date('d/m/Y', strtotime($item->time_start)),
                    date('H:i', strtotime($item->time_start)), date('H:i', strtotime($item->time_end)), $duration, ($duration > 1) ? 's' : '');
                if (count($users) > 1) {
                    $invoice_line->text .= ' - ' . $item->user()->getResults()->fullname;
                }
                $invoice_line->amount += $duration * (self::COWORKING_HALF_DAY_PRICING / 1.2);

                $item->invoice_id = $invoice->id;
                $item->save();
            }

            $sql = 'SELECT ';
        }
        */
    }

    protected function cleanPhoneboxSession()
    {
        DB::statement(sprintf('UPDATE phonebox SET active_session_id = null WHERE id in 
          (SELECT phonebox_id as id from phonebox_session 
            where ended_at <= "%s" and id = active_session_id)', date('Y-m-d H:i:s')));
    }

    protected function checkMonitoring()
    {
        //region Host getting down
        $items = DB::select(DB::raw(
            'SELECT equipment.id, equipment.ip, equipment.name, equipment.last_seen_at
            if(locations.name IS NULL, cities.name,concat(cities.name, \' > \',  locations.name)) as location,
          FROM equipment 
            join locations on equipment.location_id = locations.id
            join cities on locations.city_id = cities.id
          WHERE is_critical = 1 
            AND last_seen_at IS NOT NULL 
            AND DATE_ADD(last_seen_at, INTERVAL 5 * frequency SECOND) < NOW())
            AND (notified_at IS NULL OR (notified_at < last_seen_at))
          '));
        foreach ($items as $equipment) {
            $message = sprintf('%1$s > %2$s (%3$s) ne réponds pas depuis %4$s',
                $equipment->location, $equipment->name, $equipment->ip, date('d/m/Y H:i', strtotime($equipment->last_seen_at)));

            $this->slack(Config::get('etincelle.slack_staff_toulouse'), array(
                'text' => $message,
            ));
            $sql = sprintf('UPDATE equipment SET notified_at = NOW() WHERE id = %d', $equipment->id);
            DB::statement($sql);
        }
        //endregion

        //region Host getting back
        $items = DB::select(DB::raw(
            'SELECT equipment.id, equipment.ip, equipment.name, equipment.last_seen_at
            if(locations.name IS NULL, cities.name,concat(cities.name, \' > \',  locations.name)) as location,
          FROM equipment 
            join locations on equipment.location_id = locations.id
            join cities on locations.city_id = cities.id
          WHERE is_critical = 1 
            AND last_seen_at IS NOT NULL 
            AND notified_at IS NOT NULL 
            AND notified_at < last_seen_at
          '));
        foreach ($items as $equipment) {
            $message = sprintf('%1$s > %2$s (%3$s) réponds à nouveau %4$s',
                $equipment->location, $equipment->name, $equipment->ip, date('d/m/Y H:i', strtotime($equipment->last_seen_at)));

            $this->slack(Config::get('etincelle.slack_staff_toulouse'), array(
                'text' => $message,
            ));
            $sql = sprintf('UPDATE equipment SET notified_at = NULL WHERE id = %d', $equipment->id);
            DB::statement($sql);
        }
        //endregion
    }
}