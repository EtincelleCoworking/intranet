<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class OdooUpdateCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'odoo:update';

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
        if ($this->option('users')) {
            $this->updateUsers($this->option('force'));
        }

        if ($this->option('pending-pos-to-orders')) {
            $occurs_at = date('Y-m-d');
            if ($occurs_at > '2018-03-16') {
                $this->createOrderFromPendingPosSales($occurs_at);
            }
        }
        /*
                $result = $xmlrpc->getUnassignedOpenOrder(date('Y-m-d'));
                print_r($result);
                exit;
        */
    }


    protected function createOrderFromPendingPosSales($occurs_at)
    {
        $xmlrpc = new Odoo();
        $xmlrpc->createOrderFromPendingPosSales($occurs_at);
    }

    protected function updateUsers($force = false)
    {
        $xmlrpc = new Odoo();
        $result = $xmlrpc->getKnownUsers();

        $odoo_datas = array();
        foreach ($result as $item) {
            if (empty($item['ref'])) {
                printf('empty ref for %s <%s>' . "\n", $item['name'], $item['email']);
            } else {
                $odoo_datas[$item['ref']] = $item;
            }
        }
        //print_r($odoo_datas);


        $sql = sprintf('SELECT id, concat(firstname, " ", lastname) as name, email, phone FROM users WHERE id in 
(SELECT DISTINCT(user_id) FROM past_times WHERE ressource_id = %d and date_past > DATE_SUB(now(), INTERVAL 3 MONTH))', Ressource::TYPE_COWORKING);
        $items = DB::select(DB::raw($sql));
        $created_count = 0;
        $updated_count = 0;
        $skipped_count = 0;
        foreach ($items as $item) {
            if (!in_array($item->id, array(1))) {
                if (isset($odoo_datas[$item->id])) {
                    // exist remotely
                    $needs_update = ($odoo_datas[$item->id]['name'] != $item->name)
                        || ($odoo_datas[$item->id]['email'] != $item->email)
                        || ($odoo_datas[$item->id]['phone'] != $item->phone);
                    if ($needs_update || $force) {
                        if ($force) {
                            printf("(FORCED)\n");
                        }
                        printf("- name: [%s] / [%s] %s\n", $odoo_datas[$item->id]['name'], $item->name, ($odoo_datas[$item->id]['name'] != $item->name) ? '<--' : '');
                        printf("- email: [%s] / [%s] %s\n", $odoo_datas[$item->id]['email'], $item->email, ($odoo_datas[$item->id]['email'] != $item->email) ? '<--' : '');
                        printf("- phone: [%s] / [%s] %s\n", $odoo_datas[$item->id]['phone'], $item->phone, ($odoo_datas[$item->id]['phone'] != $item->phone) ? '<--' : '');

                        $xmlrpc->updateUser($odoo_datas[$item->id]['ref'], $item->id, $item->name, $item->email, $item->phone);
                        printf('Updated %s <%s>, Remote ID = %d' . "\n", $item->name, $item->email, $odoo_datas[$item->id]['ref']);
                        $updated_count++;
                    } else {
                        printf('Skipped %s <%s>, Remote ID = %d' . "\n", $item->name, $item->email, $odoo_datas[$item->id]['ref']);
                        $skipped_count++;
                    }
                } else {
                    // create it
                    $id = $xmlrpc->createUser($item->id, $item->name, $item->email, $item->phone);
                    printf('Created %s <%s>, Remote ID = %d' . "\n", $item->name, $item->email, $id);
                    $created_count++;
                }
            }
        }

        printf("\n\nSkipped: %d, Updated: %d, Created: %d\n", $skipped_count, $updated_count, $created_count);

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
            array('users', null, InputOption::VALUE_NONE),
            array('force', null, InputOption::VALUE_NONE),
            array('pending-pos-to-orders', null, InputOption::VALUE_NONE),
        );
    }

}


?>