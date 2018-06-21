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
        $from_date = '2018-06-25 08:00';
        $to_date = '2018-09-12';
        $duration = 14 * 60;
        $ressource_id = 3;
        $user_id = 1921;
        $organisation_id = 255;
        $booking_title = 'Simplon.co';

        $now = $from_date;
        while ($now <= $to_date) {
            $booking = new Booking();
            $booking->organisation_id = $organisation_id;
            $booking->user_id = $user_id;
            $booking->title = $booking_title;
            $booking->is_private = true;
            $booking->save();

            $booking_item = new BookingItem();
            $booking_item->booking_id = $booking->id;
            $booking_item->start_at = $now;
            $booking_item->duration = $duration;
            $booking_item->ressource_id = $ressource_id;
            $booking_item->is_free = false;
            $booking_item->confirmed_at = date('Y-m-d H:i');
            $booking_item->confirmed_by_user_id = 1;
            $booking_item->save();
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
