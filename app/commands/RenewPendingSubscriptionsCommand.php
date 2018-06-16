<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class RenewPendingSubscriptionsCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'etincelle:renew-pending-subscriptions';

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
            ->where('renew_at', '<=', date('Y-m-d'))
            ->get();
        foreach ($subscriptions as $subscription) {
            $invoice = $subscription->renew();

            $data = array();
            $data['text'] = sprintf('La facture <%s|%s> de renouvellement d\'abonnement de %s a été créée automatiquement',
                URL::route('invoice_modify', $invoice->id), $invoice->ident, $subscription->user->fullname);
            $this->slack(Config::get('etincelle.slack_staff_toulouse'), $data);
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
