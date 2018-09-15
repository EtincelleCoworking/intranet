<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class SlackDeleteOldFilesCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'etincelle:slack-delete-old-files';

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
        $min_age = (int)$this->option('age');
        $slack = new Slack($_ENV['slack_token']);
        $current_page = 1;
        $max_pages = null;
        $size = 0;
        $this->output->writeln(sprintf('Looking for files older than %d days', $min_age));
        while (($max_pages == null) || ($current_page <= $max_pages)) {
            $json = $slack->call('files.list', array('page' => $current_page));
            if (null == $max_pages) {
                $max_pages = $json['paging']['pages'];
            }
            $this->output->writeln(sprintf('Page %d/%d', $current_page, $max_pages));
            foreach ($json['files'] as $file) {
                $age = (time() - $file['timestamp']) / (24 * 60 * 60);
                //$this->output->writeln(sprintf('%.2fMo %5d %s %-50s ', $file['size'] / (1024 * 1024), $age, date('d/m/Y H:i', $file['timestamp']), $file['name']));
                if ($age > $min_age) {
                    $result = $slack->call('files.delete', array('file' => $file['id']));
                    if (!$result['ok']) {
                        $this->output->writeln(sprintf('<error>An error has occured when trying to delete file %s: %s</error>', $file['id'], print_r($result, true)));
                    } else {
                        $this->output->writeln(sprintf('Deleted file (id: %s) %s %-70s - Freed: %.2fMo', $file['id'], date('d/m/Y H:i', $file['timestamp']), $file['name'], $size / (1024 * 1024)));
                    }
                    $size += $file['size'];
                }
            }
            $current_page++;
        }

        if ($size == 0) {
            $this->output->writeln('No file to delete.');
        } else {
            $this->output->writeln(sprintf('Freed: %.2fMo', $size / (1024 * 1024)));
        }
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(
            array('age', null, InputOption::VALUE_REQUIRED, 'Minimum age of files to delete (in days)', 6 * 30),
        );
    }

}
