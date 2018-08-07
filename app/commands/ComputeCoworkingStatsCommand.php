<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ComputeCoworkingStatsCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'etincelle:compute-coworking-stats';

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
        $min_past_times = DB::select('SELECT MIN(time_start) as result FROM past_times WHERE ressource_id = ' . Ressource::TYPE_COWORKING);
        $min_past_times = $min_past_times[0]->result;

        $max_stats = DB::select('SELECT MAX(occurs_at) as result FROM stats_coworking_usage');
        $max_stats = $max_stats[0]->result;

        if (NULL == $max_stats) {
            $start = $min_past_times;
        } else {
            $start = $max_stats;
        }

        $locations = array();
        foreach (DB::select('SELECT id, coworking_capacity FROM locations 
          WHERE coworking_capacity > 0 AND id <> 2') as $item) {
            $locations[$item->id] = $item->coworking_capacity;
        }

        $start = date('Y-m-d H:00:00', strtotime($start));

        $now = date('Y-m-d H:00:00');
        while ($start < $now) {
            $end = date('Y-m-d H:00:00', strtotime('+1 hour', strtotime($start)));
            $this->output->writeln($start);
            $result = DB::select('SELECT users.default_location_id as location_id, COUNT(DISTINCT(users.id)) as cnt
FROM past_times
join users on past_times.user_id = users.id
WHERE past_times.time_start < "' . $end . '" AND past_times.time_end > "' . $start . '" AND ressource_id = 1 AND users.is_hidden_member = 0 AND free_coworking_time = 0
AND users.id not in (1, 1044, 877, 676, 307, 1474)
GROUP BY users.default_location_id');
            $stats = array();
            foreach ($result as $item) {
                $stats[$item->location_id] = $item->cnt;
            }
            foreach ($locations as $location_id => $capacity) {
                $count = isset($stats[$location_id]) ? $stats[$location_id] : 0;
                DB::insert(sprintf('INSERT INTO stats_coworking_usage (occurs_at, count, capacity, location_id) 
              VALUES ("%s", %d, %d, %d)', $start, $count, $capacity, $location_id));
            }
            $start = $end;
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(//array('example', InputArgument::REQUIRED, 'An example argument.'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(//array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        );
    }

}
