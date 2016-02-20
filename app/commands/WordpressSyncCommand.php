<?php

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use GuzzleHttp\Client;

class WordpressSyncCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'wordpress:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronise l\'intranet et le site Wordpress';

    protected $wordpress_url = 'http://www.etincelle-coworking.com';

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
        $this->createNewPages();
    }

    protected function createNewPages()
    {
        /** @var Booking $booking */
        foreach (Booking::future() as $booking) {
            if (!$booking->wordpress_id) {
                $booking->wordpress_id = $this->createPage($booking);
                $booking->save();
                printf("Created page %d - %d - %s\n", $booking->wordpress_id, $booking->id, $booking->title);
            } else {
                // valider que la page existe toujours?
                if ($this->updatePage($booking)) {
                    printf("Updated page %d - %d - %s\n", $booking->wordpress_id, $booking->id, $booking->title);
                }
            }
        }
        // Supprimer les événements qui ont étés suppprimés

    }

    protected function getAuth()
    {
        return array($this->argument('login'), $this->argument('password'));
    }

    protected function updatePage($booking)
    {
        $client = new Client();

        $res = $client->request('POST', $this->wordpress_url . '/wp-json/wp/v2/pages/' . $booking->wordpress_id,
            array(
                'form_params' => $this->computeBooking($booking),
                'auth' => $this->getAuth()
            )
        );
        return $res->getStatusCode() == 200;
    }

    protected function createPage($booking)
    {
        $client = new Client();

        $res = $client->request('POST', $this->wordpress_url . '/wp-json/wp/v2/pages',
            array(
                'form_params' => $this->computeBooking($booking),
                'auth' => $this->getAuth()
            )
        );
        $result = $res->getHeader('Location');
        if (is_array($result) && 1 == count($result)) {
            $location = array_pop($result);
            if (preg_match('#/wp-json/wp/v2/pages/([0-9]+)$#', $location, $tokens)) {
                return $tokens[1];
            }
        }
        return false;
    }

    protected function computeBooking($booking)
    {
        $result = array(
            'date' => $booking->created_at->format('Y-m-d H:i:s'),
            'slug' => sprintf('%d-%s', $booking->id, Str::slug($booking->title)),
            'title' => $booking->title,
            'content' => $booking->content,
            'status' => 'publish',
            'comment_status' => 'closed',
            'ping_status' => 'open',
            'parent' => 168,
            //'author' => '',
        );
        //print_r($result);exit;
        return $result;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('login', InputArgument::REQUIRED, 'Website login'),
            array('password', InputArgument::REQUIRED, 'Website password'),
        );
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array(//			array('example', null, InputOption::VALUE_OPTIONAL, 'An example option.', null),
        );
    }

}
