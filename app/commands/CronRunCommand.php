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

        $this->everyFiveMinutes($this->sendSmsNotificationForCloseMeetings);

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
                    'phone' => '0625024411'//$item->phone,
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
                    Log::debug('enough time');
                } elseif ($previous['user']['id'] == $current['user']['id']) {
                    // same user, do no notify
                    Log::debug('same user, do no notify');
                } else {
                    $phone = CronRunCommand::getPhoneNumberFormattedForSms($previous['user']['phone']);
                    if (!$phone) {
                        // pas de téléphone renseigné ou pas un portable ou pas au bon format
                        Log::debug('pas de téléphone renseigné ou pas un portable ou pas au bon format');
                    } elseif (!empty($previous['sms_uid'])) {
                        Log::debug('sms déjà envoyé');
                        // sms déjà envoyé
                    } elseif ($previous['start_at'] > $only_if_after) {
                        Log::debug('trop tôt');
                    } elseif ($current['start_at'] < $now) {
                        Log::debug('autre réservation déjà commencée en théorie');
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
                        Log::debug($message_content);
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
        if (strlen($data) != 10) {
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

}