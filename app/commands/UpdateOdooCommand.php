<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class UpdateOdooCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'etincelle:update-odoo';

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
        $xmlrpc= new Odoo();

        $result = $xmlrpc->getKnownUsers();

        $odoo_datas = array();
        foreach ($result as $item) {
            if (empty($item['ref'])) {
                printf('empty ref for %s <%s>' . "\n", $item['name'], $item['email']);
            } else {
                $odoo_datas[$item['ref']] = $item;
            }
        }
        print_r($odoo_datas);

        /*
        id
        phone
        mobile
        display_name
        email
        active
        function
        is_company
        ref

        company_name
        street
        street2
        city
        vat

         *
         */
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return array(
		);
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return array(
		);
	}

}


?>