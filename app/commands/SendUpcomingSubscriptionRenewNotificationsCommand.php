<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SendUpcomingSubscriptionRenewNotificationsCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'etincelle:send-upcoming-subscription-renew-notification';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $subscriptions = Subscription::where('is_automatic_renew_enabled', '=', true)
            ->join('subscription_kind', 'subscription_kind_id', '=', 'subscription_kind.id', 'left outer')
            ->where('renew_at', '>', date('Y-m-d'))
            ->where('renew_at', '<=', date('Y-m-d', strtotime('+7 days')))
            ->whereNull('reminded_at')
            ->where('subscription_kind.ressource_id', '=', Ressource::TYPE_COWORKING)
            ->select('subscription.*')
            ->get();
        foreach ($subscriptions as $subscription) {
            Mail::send('emails.upcoming_subscription_renew', array('subscription' => $subscription), function ($message) use ($subscription) {
                $message->from($_ENV['mail_address'], $_ENV['mail_name'])
                    ->bcc($_ENV['mail_address'], $_ENV['mail_name']);

                $message->to($subscription->user->email, $subscription->user->fullname);
                $message->subject(sprintf('%s - Renouvellement de votre abonnement le %s', $_ENV['organisation_name'], date('d/m/Y', strtotime($subscription->renew_at))));
            });
            $subscription->reminded_at = date('Y-m-d');
            $subscription->save();
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array();
    }

}
