<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class UserMergeCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'user:merge';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    protected $wordpress_url = '';

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
        $user = User::where('email', '=', $this->argument('recent_email'))->first();
        $this->output->writeln(sprintf('User %s (ID = %d)', $user->email, $user->id));

        $olds = $this->option('old_email');
        dd($olds);
    }


    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('recent_email', InputArgument::REQUIRED, ''),
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
            array('old_email', null, InputOption::VALUE_IS_ARRAY, '', null),
        );
    }

}
