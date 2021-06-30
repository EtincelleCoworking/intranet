<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class BookingGenerateCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'etincelle:booking-generate';

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
        $from_date = '2019-03-01 08:00';
        $to_date = strtotime('2019-04-30 23:59:59');
        $duration = 14 * 60;
        $ressource_id = 4;
        $user_id = 2623;
        $organisation_id = 1620;
        $booking_title = 'Boston Storage';

        $now = strtotime($from_date);
        while ($now <= $to_date) {
            $booking = new Booking();
            $booking->organisation_id = $organisation_id;
            $booking->user_id = $user_id;
            $booking->title = $booking_title;
            $booking->is_private = true;
            $booking->save();

            $booking_item = new BookingItem();
            $booking_item->booking_id = $booking->id;
            $booking_item->start_at = date('Y-m-d H:i', $now);
            $booking_item->duration = $duration;
            $booking_item->ressource_id = $ressource_id;
            $booking_item->is_free = false;
            $booking_item->confirmed_at = date('Y-m-d H:i');
            $booking_item->confirmed_by_user_id = 1;
            $booking_item->save();

            $this->getOutput()->writeln(sprintf('%s', date('Y-m-d H:i', $now)));
            $now = strtotime('+1 day', $now);
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
        return array(//		array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        );
    }

}

