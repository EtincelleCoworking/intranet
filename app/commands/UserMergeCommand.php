<?php

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
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
        $email = $this->argument('recent_email');
        $user = User::where('email', '=', $email)->first();
        if (!$user) {
            $this->output->writeln(sprintf('<error>Unable to find user %s</error>', $email));
            return false;
        }
        $this->output->writeln(sprintf('User %s (ID = %d)', $user->email, $user->id));

        $old_emails = $this->option('old');
        $olds = User::whereIn('email', $old_emails)->get();

        $old_ids = array();
        foreach ($olds as $old) {
            $index = array_search($old->email, $old_emails);
            if ($index !== false) {
                unset($old_emails[$index]);
            }
            $old_ids[] = $old->id;
        }
        if (count($old_emails) > 0) {
            $this->output->writeln(sprintf('<error>Unable to find user%s %s</error>',
                (count($old_emails) == 1) ? '' : 's', implode(',', $old_emails)));
            return false;
        }
        DB::transaction(function () use ($user, $old_ids) {
            DB::table('booking')->update(['user_id' => $user->id])->whereIn('user_id', $old_ids);
            DB::table('booking_item')->update(['confirmed_by_user_id' => $user->id])->whereIn('confirmed_by_user_id', $old_ids);
            DB::table('devices')->update(['user_id' => $user->id])->whereIn('user_id', $old_ids);
            DB::table('door_tokens')->update(['user_id' => $user->id])->whereIn('user_id', $old_ids);
            DB::table('gift_photoshoot_slot')->update(['user_id' => $user->id])->whereIn('user_id', $old_ids);
            DB::table('invoices')->update(['user_id' => $user->id])->whereIn('user_id', $old_ids);
            DB::table('invoices_comments')->update(['user_id' => $user->id])->whereIn('user_id', $old_ids);
            DB::table('invoices_items')->update(['subscription_user_id' => $user->id])->whereIn('subscription_user_id', $old_ids);
            DB::table('locker_history')->update(['user_id' => $user->id])->whereIn('user_id', $old_ids);
            DB::table('organisation_user')->update(['user_id' => $user->id])->whereIn('user_id', $old_ids);
            DB::table('past_times')->update(['user_id' => $user->id])->whereIn('user_id', $old_ids);
            DB::table('phonebox_session')->update(['user_id' => $user->id])->whereIn('user_id', $old_ids);
            DB::table('skills')->update(['user_id' => $user->id])->whereIn('user_id', $old_ids);
            DB::table('subscription')->update(['user_id' => $user->id])->whereIn('user_id', $old_ids);
            DB::table('team_planning_item')->update(['user_id' => $user->id])->whereIn('user_id', $old_ids);
            DB::table('users')->update(['affiliate_user_id' => $user->id])->whereIn('affiliate_user_id', $old_ids);
            DB::table('user_gift')->update(['user_id' => $user->id])->whereIn('user_id', $old_ids);
            DB::table('user_hashtag')->update(['user_id' => $user->id])->whereIn('user_id', $old_ids);
            DB::table('user_job')->update(['user_id' => $user->id])->whereIn('user_id', $old_ids);
            DB::table('wall_posts')->update(['user_id' => $user->id])->whereIn('user_id', $old_ids);
            DB::table('user')->whereIn('id', $old_ids)->delete();
        });
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
            array('old', null, InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY, '', null),
        );
    }

}
