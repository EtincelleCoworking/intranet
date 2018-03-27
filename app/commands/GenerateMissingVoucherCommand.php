<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GenerateMissingVoucherCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'etincelle:generate-missing-vouchers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate missing vouchers';

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
        $datas = DB::select(DB::raw(sprintf('SELECT locations.voucher_endpoint, locations.voucher_key, locations.voucher_secret, booking_item.start_at, booking_item.booking_id 
FROM `booking_item`
JOIN ressources ON booking_item.ressource_id = ressources.id
JOIN locations ON ressources.location_id = locations.id
JOIN booking ON booking_item.booking_id = booking.id
WHERE locations.voucher_endpoint IS NOT NULL 
AND booking.wifi_login IS NULL
AND booking_item.start_at > now()
GROUP BY booking.id ORDER BY booking_item.start_at ASC')));

        if(count($datas) == 0){
            $this->output->writeln('All bookings are up to date.');
            return true;
        }
        foreach ($datas as $data) {
            $voucher = Booking::generateVoucher($data->voucher_endpoint, $data->voucher_key, $data->voucher_secret, $data->start_at);
            if (!$voucher) {
                $this->output->writeln(sprintf("<error>Error while generating voucher for %s (BookingID = %d)</error>", $data->start_at, $data->booking_id));
            } else {
                DB::update('update booking set wifi_login = ? , wifi_password = ? where id = ?', [$voucher['username'], $voucher['password'], $data->booking_id]);
                $this->output->writeln(sprintf("Generated voucher for %s (BookingID = %d): %s / %s", $data->start_at, $data->booking_id, $voucher['username'], $voucher['password']));
            }
        }
        return true;
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
        return array();
    }

}
