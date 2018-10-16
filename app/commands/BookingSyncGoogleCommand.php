<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class BookingSyncGoogleCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'etincelle:sync-google-calendar';

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
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function fire()
    {
        $configuration_filename = app_path() . '/../booking-sync-6d0892204a1a.json';
        putenv('GOOGLE_APPLICATION_CREDENTIALS=' . $configuration_filename);

        $client = new Google_Client();
        $client->setAuthConfig($configuration_filename);
        $client->addScope(Google_Service_Calendar::CALENDAR);
        $service = new Google_Service_Calendar($client);


        foreach (Ressource::whereNotNull('google_calendar_id')->with('locations')->get() as $ressource) {
            $count = 0;
            $this->output->writeln(sprintf('Starting synchronization of %s', $ressource->fullname));
            $this->output->writeln(' - Clearing existing events on Google Calendar');
            $optParams = array(
                //    'maxResults' => 10,
                'orderBy' => 'startTime',
                'singleEvents' => true,
                'timeMin' => date('c'),
            );
            $results = $service->events->listEvents($ressource->google_calendar_id, $optParams);
            $events = $results->getItems();
            foreach ($events as $event) {
                $service->events->delete($ressource->google_calendar_id, $event->id);
                $this->output->writeln(sprintf(' - Deleted event %s', $event->id));
                $count++;
            }
            $this->output->writeln(sprintf('Deleted %d events', $count));
            $this->output->writeln();
            $count = 0;

            foreach (BookingItem::where('ressource_id', '=', $ressource->id)->where('start_at', '>', date('Y-m-d'))->get() as $booking_item) {
                $event = new Google_Service_Calendar_Event(array(
                    'summary' => sprintf('Booking #%d', $booking_item->id),
                    'start' => array(
                        'dateTime' => date('c', strtotime($booking_item->start_at)),
                        'timeZone' => 'Europe/Paris',
                    ),
                    'end' => array(
                        'dateTime' => date('c', strtotime(sprintf('+%d minutes', $booking_item->duration), strtotime($booking_item->start_at))),
                        'timeZone' => 'Europe/Paris',
                    ),
                ));
                $count++;

                $event = $service->events->insert($ressource->google_calendar_id, $event);
                $start = $event->start->dateTime;
                if (empty($start)) {
                    $start = $event->start->date;
                }
                $this->output->writeln(sprintf(' - Created event %s - %s - %s %s', $event->id, $start, $event->getSummary(), $event->htmlLink));
            }
            $this->output->writeln(sprintf('Created %d events', $count));
        }

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
        return array(//	array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        );
    }

}
