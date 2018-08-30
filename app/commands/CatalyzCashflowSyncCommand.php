<?php

use GuzzleHttp\Client;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CatalyzCashflowSyncCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'cashflow:sync';

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
        $duration = '3 months';
        $account_id=2;
        $ends_at = (new \DateTime())->modify($duration)->format('Y-m-d');

        $collection = new BankOperationCollection($ends_at);
        (new StripeBankOperationFactory())->populate($collection);
        (new SubscriptionBankOperationFactory())->populate($collection);
        (new CashflowBankOperationFactory($account_id))->populate($collection);
        (new InvoiceBankOperationFactory($account_id))->populate($collection);
        (new VatBankOperationFactory($account_id))->populate($collection);

        $operations = array();
        foreach ($collection->getItems(0) as $date => $item_data) {
            $operations[$date] = $item_data['positive'] + $item_data['negative'];
        }

        try {
            $client = new Client();
            $res = $client->request('POST', sprintf('http://app.catalyz-cashflow.local/api/account/%d', $account_id), [
                'form_params' => [
                    'module' => 'intranet',
                    'operations' => $operations
                ]
            ]);
            echo $res->getBody();
        } catch (\Exception $e) {
            echo 'reported exception';
            echo((string)$e->getResponse()->getBody());
        }

    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(//array('example', InputArgument::REQUIRED, 'An example argument.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(//array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        );
    }

}
