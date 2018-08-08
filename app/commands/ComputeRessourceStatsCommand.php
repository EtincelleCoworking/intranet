<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ComputeRessourceStatsCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'etincelle:compute-ressource-stats';

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
        $ressource_id = $this->argument('ressource');
        $ressources = array();
        if ($ressource_id) {
            $ressources[] = $ressource_id;
        } else {
            foreach (Ressource::where('ressource_kind_id', '=', RessourceKind::TYPE_MEETING_ROOM)->get() as $ressource) {
                $ressources[] = $ressource->id;
            }
        }
        foreach ($ressources as $ressource_id) {
            $this->output->writeln($ressource_id);

            $min_past_times = DB::select('SELECT MIN(time_start) as result FROM past_times WHERE ressource_id = ' . $ressource_id);
            $min_past_times = $min_past_times[0]->result;

            $max_stats = DB::select('SELECT MAX(occurs_at) as result FROM stats_ressource_usage WHERE ressource_id = ' . $ressource_id);
            $max_stats = $max_stats[0]->result;

            if (NULL == $max_stats) {
                $start = $min_past_times;
            } else {
                $start = $max_stats;
            }

            $start = date('Y-m-d H:00:00', strtotime($start));

            $now = date('Y-m-d H:00:00');
            while ($start < $now) {
                $end = date('Y-m-d H:00:00', strtotime('+1 hour', strtotime($start)));
                $this->output->writeln($start);
                $result = DB::select('SELECT COUNT(*) as cnt
              FROM past_times
              WHERE past_times.time_start < "' . $end . '" AND past_times.time_end > "' . $start . '" AND ressource_id = ' . $ressource_id);
                DB::insert(sprintf('INSERT INTO stats_ressource_usage (occurs_at, busy, ressource_id) 
              VALUES ("%s", %d, %d)', $start, $result[0]->cnt > 0, $ressource_id));
                $start = $end;
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
            array('ressource', InputArgument::OPTIONAL, 'Ressource ID'),
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
