<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SlackInviteAllExceptCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'etincelle:slack-invite-all-except';

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

    protected function displayChannelList($data, $message = '')
    {
        if ($message) {
            $this->output->writeln($message);
        }
        foreach ($data['channels'] as $channel_info) {
            $this->output->writeln(sprintf('[%s] %s', $channel_info['is_private'] ? 'X' : '-', $channel_info['name_normalized']));
//            if ($channel_info['is_channel']) {
//            }
//            print_r($channel_info);
        }
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $slack = new Slack($_ENV['slack_token']);
        $channel = $this->argument('channel');
        $channel_map = array();
        if (empty($channel)) {
            $json = $slack->call('conversations.list', array('types' => 'public_channel,private_channel'));
            $this->displayChannelList($json, 'Missing channel, please choose one from this list:');
            exit;
        } else {
            $json = $slack->call('conversations.list', array('types' => 'public_channel,private_channel'));
            foreach ($json['channels'] as $channel_info) {
                //if ($channel_info['is_channel']) {
                $channel_map[$channel_info['name_normalized']] = $channel_info['id'];
                // }
            }
            if (!isset($channel_map[$channel])) {
                $this->displayChannelList($json, sprintf('<error>Unknown channel: %s</error> Please choose one from this list:'));
            } else {
                $this->output->writeln(sprintf('<info>Channel: %s (ID: %s)</info>', $channel, $channel_map[$channel]));
                $channel = $channel_map[$channel];
            }
        }
        $json = $slack->call('users.list');

        $user_map = array();
        foreach ($json['members'] as $member) {
            if (!$member['is_bot'] && $member['updated']) {
                $user_map[$member['name']] = array(
                    'id' => $member['id'],
                    'caption' => @$member['real_name'],
                );
            }
        }
        $skipped = $this->option('skip');
        $skipped_ids = array();
        if (is_array($skipped)) {
            foreach ($skipped as $user_name) {
                if (isset($user_map[$user_name])) {
                    $skipped_ids[] = $user_map[$user_name]['id'];
                    unset($user_map[$user_name]);
                }
            }

            if ((0 == count($skipped_ids)) || (count($skipped_ids) != count($skipped))) {
                $this->output->writeln('<error>No user(s) to skip, please choose from this list:</error>');
                foreach ($user_map as $user_name => $user_data) {
                    $this->output->writeln(sprintf('- %s %s (%s)', $user_data['id'], $user_data['caption'], $user_name));
                }
            } else {
                $this->output->writeln('Inviting users...');
                foreach (array_chunk($user_map, 30, true) as $parts) {
                    $call_result = $slack->call('conversations.invite', array(
                        'channel' => $channel,
                        'users' => implode(',', array_values(array_map(function ($i) {
                            return $i['id'];
                        }, $parts)))
                    ));
                    if (isset($call_result ['ok']) && ($call_result ['ok'] == 1)) {
                        $this->output->write('.');
                    }
                }
                $this->output->writeln('');
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
            array('channel', InputArgument::OPTIONAL)
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
            array('skip', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'user to skip'),
        );
    }

}
