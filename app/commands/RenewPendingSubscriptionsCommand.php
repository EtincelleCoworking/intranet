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
            $invoice->send();

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

}
