<?php


use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Console\Input\InputOption;

class UpdateCoworkerLocationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $name = 'etincelle:update-coworker-location';

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
        $this->addOption('months', null, InputOption::VALUE_REQUIRED, '', 1);
        $this->addOption('dry-run', '', InputOption::VALUE_NONE);
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function fire()
    {
        $duration = $this->option('months');
        $sql = 'SELECT users.firstname, users.lastname, past_times.user_id, past_times.location_id, users.default_location_id, SUM(TIMESTAMPDIFF(SECOND,past_times.time_start, past_times.time_end)) as cnt
            FROM past_times
                JOIN users ON users.id = past_times.user_id
            WHERE past_times.ressource_id = ?
                AND past_times.time_end IS NOT NULL
                AND past_times.time_start > now() - INTERVAL ? MONTH
                AND users.is_staff = false
                AND users.free_coworking_time = false
            GROUP BY past_times.user_id, past_times.location_id, users.default_location_id
                , users.firstname, users.lastname
            ORDER BY past_times.user_id, cnt desc
            ';
        $users = [];
        $processed_users = [];
        foreach (DB::select($sql, [Ressource::TYPE_COWORKING, $duration]) as $item) {
            if (empty($processed_users[$item->user_id])) {
                $processed_users[$item->user_id] = true;
                if ($item->default_location_id != $item->location_id) {
                    $users[$item->user_id] = [
                        'name' => $item->firstname . ' ' . $item->lastname,
                        'current_location' => $item->default_location_id,
                        'target_location' => $item->location_id
                    ];
                }
            }
        }
        if (count($users) === 0) {
            $this->output->writeln('No changes found.');
            return 0;
        }
        $locations = [
            null => 'Unknown'
        ];
        foreach (Location::select('id', 'name')->get() as $location) {
            $locations[$location->id] = $location->name;
        }

        foreach (User::whereIn('id', array_keys($users))->get() as $user) {
            $this->output->writeln(mb_sprintf('%-30s %-15s -> %-15s',
                $user->name, $locations[$users[$user->id]['current_location']]
                , $locations[$users[$user->id]['target_location']]
            ));
            if (!$this->option('dry-run')) {
                $user->default_location_id = $users[$user->id]['target_location'];
                $user->save();
            }
        }

        return 0;
    }
}

function mb_sprintf($format, ...$args)
{
    $params = $args;

    $callback = function ($length) use (&$params) {
        $value = array_shift($params);
        return strlen($value) - mb_strlen($value) + $length[0];
    };

    $format = preg_replace_callback('/(?<=%|%-)\d+(?=s)/', $callback, $format);

    return sprintf($format, ...$args);
}
