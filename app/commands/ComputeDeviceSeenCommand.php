<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ComputeDeviceSeenCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'etincelle:compute-device-seen';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $max_duration = 30; // minutes

        $dates = array();
        if (!empty($this->option('date'))) {
            $dates[] = $this->option('date');
        } else {
            $items = DB::select('SELECT DISTINCT(DATE(last_seen_at)) as occurs_at FROM devices_seen WHERE last_seen_at < DATE(NOW()) ORDER BY occurs_at ASC');
            foreach ($items as $item) {
                $dates[] = $item->occurs_at;
            }
        }

        $max_duration *= 60; // to seconds

        foreach ($dates as $date) {
            $this->output->writeln(sprintf('Processing %s', $date));
            $sql = 'SELECT DISTINCT(device_id) as id, location_id FROM devices_seen WHERE DATE(last_seen_at) = "' . $date . '" ORDER BY device_id ASC';
            if ($this->output->isDebug()) {
                $this->output->writeln($sql);
            }
            $devices = DB::select($sql);
            foreach ($devices as $device) {
                $this->output->write(sprintf(' - Device #%-6d', $device->id));
                $sql = 'SELECT id, last_seen_at FROM devices_seen WHERE DATE(last_seen_at) = "' . $date . '" AND device_id = ' . $device->id . ' AND location_id = ' . $device->location_id . ' ORDER BY last_seen_at ASC';
                if ($this->output->isDebug()) {
                    $this->output->writeln($sql);
                }
                $items = DB::select($sql);

                $last = null;
                $range_start = null;
                $ids_to_remove = array();
                foreach ($items as $item) {
                    $ids_to_remove[] = $item->id;
                    if (null == $last) {
                        $last = strtotime($item->last_seen_at);
                        $range_start = $last;
                    } else {
                        $current = strtotime($item->last_seen_at);

                        if ($current - $last < $max_duration) {
                            $last = $current;
                        } else {
                            //$this->output->writeln(sprintf('%s %s %d %d %d', date('Y-m-d H:i:s', $last), date('Y-m-d H:i:s', $current), $current - $last, $max_duration, $current - $last < $max_duration));
                            $this->createRange($range_start, $last, $device->id, $device->location_id);
                            $last = $current;
                            $range_start = $last;
                        }
                    }
                }
                if ($range_start) {
                    $this->createRange($range_start, $current, $device->id, $device->location_id);
                }
                $this->output->writeln('');
                if (!$this->option('dry-run')) {
                    DeviceSeen::destroy($ids_to_remove);
                    if ($this->output->isVerbose()) {
                        $this->output->writeln(sprintf('Removing DeviceSeen IDs = %s', implode(', ', $ids_to_remove)));
                    }
                }
            }
        }
    }

    protected function createRange($start_at, $end_at, $device_id, $location_id)
    {
        $range = new DeviceSeenRange();
        $range->location_id = $location_id;
        $range->device_id = $device_id;
        $range->start_at = date('Y-m-d H:i:s', $start_at);
        $range->end_at = date('Y-m-d H:i:s', $end_at);
        $this->output->write(sprintf(' / %s - %s', date('H:i', $start_at), date('H:i', $end_at)));
        if ($this->option('dry-run')) {
            $this->output->writeln('');
        } else {
            $range->save();
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
        return array(
            array('date', null, InputOption::VALUE_REQUIRED, 'YYYY-MM-DD', null),
            array('dry-run', null, InputOption::VALUE_NONE, '', null),
            array('v', null, InputOption::VALUE_NONE, '', null),
            array('vv', null, InputOption::VALUE_NONE, '', null),
        );
    }

}
