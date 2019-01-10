<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class CatalyzSyncCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'catalyz:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

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
        // https://etincelle-coworking.catalyz.fr/api/invoices
        $json = [];
        $uri = sprintf('%s?count=100000&paid_at[lte]=%s', $this->argument('api_uri'), date('Y-m-d'));

        foreach (json_decode(file_get_contents($uri)) as $invoice) {
            $json[$invoice->reference] = $invoice;
        }
        $invoices = Invoice::orderBy('date_invoice', 'DESC')->where('type', 'F')->get();
        foreach ($invoices as $invoice) {
            if (isset($json[$invoice->ident])) {
                if ($json[$invoice->ident]->paid_at != $invoice->date_payment) {
                    if ($json[$invoice->ident]->paid_at) {
                        $this->output->writeln(sprintf('<fg=green>%s Catalyz : %10s - Intranet : %10s - %s</>',
                            $invoice->ident,
                            $json[$invoice->ident]->paid_at,
                            $invoice->date_payment,
                            $json[$invoice->ident]->customer->name
                        ));
                        $invoice->date_payment = $json[$invoice->ident]->paid_at;
                        $invoice->save();
                    } else {
                        $this->output->writeln(sprintf('%s Catalyz : %10s - Intranet : %10s - %s',
                            $invoice->ident,
                            $json[$invoice->ident]->paid_at,
                            $invoice->date_payment,
                            $json[$invoice->ident]->customer->name
                        ));
                    }

                }
            }
        }

    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('api_uri', InputArgument::REQUIRED, ''),
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
