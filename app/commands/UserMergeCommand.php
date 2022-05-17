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
            DB::table('booking')
                ->whereIn('user_id', $old_ids)
                ->update(['user_id' => $user->id]);
            DB::table('booking_item')
                ->whereIn('confirmed_by_user_id', $old_ids)
                ->update(['confirmed_by_user_id' => $user->id]);
            DB::table('devices')
                ->whereIn('user_id', $old_ids)
                ->update(['user_id' => $user->id]);
            DB::table('door_tokens')
                ->whereIn('user_id', $old_ids)
                ->update(['user_id' => $user->id]);
            DB::table('gift_photoshoot_slot')
                ->whereIn('user_id', $old_ids)
                ->update(['user_id' => $user->id]);
            DB::table('invoices')
                ->whereIn('user_id', $old_ids)
                ->update(['user_id' => $user->id]);
            DB::table('invoices_comments')
                ->whereIn('user_id', $old_ids)
                ->update(['user_id' => $user->id]);
            DB::table('invoices_items')
                ->whereIn('subscription_user_id', $old_ids)
                ->update(['subscription_user_id' => $user->id]);
            DB::table('locker_history')
                ->whereIn('user_id', $old_ids)
                ->update(['user_id' => $user->id]);

            foreach ($old_ids as $old_id) {
                try {
                    DB::table('organisation_user')
                        ->where('user_id', '=', $old_id)
                        ->update(['user_id' => $user->id]);
                } catch (\Illuminate\Database\QueryException $e) {
                    // ignore if user is already linked to that organisation
                }
            }

            DB::table('past_times')
                ->whereIn('user_id', $old_ids)
                ->update(['user_id' => $user->id]);
            DB::table('phonebox_session')
                ->whereIn('user_id', $old_ids)
                ->update(['user_id' => $user->id]);
            DB::table('skills')
                ->whereIn('user_id', $old_ids)
                ->update(['user_id' => $user->id]);
            DB::table('subscription')
                ->whereIn('user_id', $old_ids)
                ->update(['user_id' => $user->id]);
            DB::table('team_planning_item')
                ->whereIn('user_id', $old_ids)
                ->update(['user_id' => $user->id]);
            DB::table('users')
                ->whereIn('affiliate_user_id', $old_ids)
                ->update(['affiliate_user_id' => $user->id]);
            DB::table('user_gift')
                ->whereIn('user_id', $old_ids)
                ->update(['user_id' => $user->id]);
            DB::table('user_hashtag')
                ->whereIn('user_id', $old_ids)
                ->update(['user_id' => $user->id]);
            DB::table('user_job')
                ->whereIn('user_id', $old_ids)
                ->update(['user_id' => $user->id]);
            DB::table('wall_posts')
                ->whereIn('user_id', $old_ids)
                ->update(['user_id' => $user->id]);
            DB::table('users')
                ->whereIn('id', $old_ids)
                ->delete();
            $this->output->writeln('Merge completed');
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
