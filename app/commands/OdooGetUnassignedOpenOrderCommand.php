<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class OdooGetUnassignedOpenOrderCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'odoo:unassigned-open-order';

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
        $xmlrpc = new Odoo();
        $result = $xmlrpc->getUnassignedOpenOrder($this->option('occurs_at'));
        print_r($result);
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
        return array(
            array('occurs_at', date('Y-m-d'), InputOption::VALUE_OPTIONAL, 'Related date (YYYY-MM-DD)', null),
        );
    }

}


?>