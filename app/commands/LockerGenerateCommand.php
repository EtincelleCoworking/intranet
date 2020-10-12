<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class LockerGenerateCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'etincelle:locker-generate';

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
        foreach (array(2 => array('name' => 'Carmes', 'count' => 16), 8 => array('name' => 'Albi', 'count' => 8)) as $location_id => $data) {
            $cabinet = new LockerCabinet();
            $cabinet->name = $data['name'];
            $cabinet->location_id = $location_id;
            $cabinet->save();

            $this->output->writeln($data['name']);
            for ($i = 1; $i < $data['count']; $i++) {
                $locker = new Locker();
                $locker->locker_cabinet_id = $cabinet->id;
                $locker->name = str_pad($i, '0', STR_PAD_LEFT);
                $locker->secret = substr(bin2hex(random_bytes(32)), 0, 32);
                $locker->save();

                $this->output->writeln($locker->name);
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
        return array(//	array('example', InputArgument::REQUIRED, 'An example argument.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(//		array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        );
    }

}
