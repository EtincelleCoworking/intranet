<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SlackSyncUsersCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'etincelle:slack-sync-users';

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
        $slack = new Slack($_ENV['slack_token']);
        $json = $slack->call('users.list');
        $emails = array();
        $slack_ids = array();

        $query = User::where('id', '=', -1);
        foreach ($json['members'] as $member) {
            if (!empty($member['profile']['email']) && !$member['deleted']) {
                $emails[$member['profile']['email']] = $member['name'];
                $slack_ids[$member['name']] = $member['profile']['email'];
                $query->orWhere('email', 'LIKE', $member['profile']['email']);
                $query->orWhere('slack_id', 'LIKE', $member['name']);
            }
        }

        //$users = User::whereIn('email', array_keys($emails))->get();
        foreach ($query->get() as $user) {
            $user->email = strtolower($user->email);
            if (empty($user->slack_id)) {
                if (isset($emails[$user->email])) {
                    if ($user->slack_id != $emails[$user->email]) {
                        $user->slack_id = $emails[$user->email];
                        $user->save();
                        $this->output->writeln(sprintf('Updated: %s', $user->fullname));
                        unset($emails[$user->email]);
                        unset($slack_ids[$user->slack_id]);
                    }
                }
            }elseif(isset($slack_ids[$user->slack_id])) {
                unset($emails[$slack_ids[$user->slack_id]]);
                unset($slack_ids[$user->slack_id]);
            }
        }

        //print_r($emails);
        print_r($slack_ids);

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
