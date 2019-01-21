<?php

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use League\Csv\Reader;

class ImportUsersFromSkeddaCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'skedda:import';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import users from Skedda export';

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
        $filename = $this->argument('filename');
        if (!file_exists($filename) || !is_file($filename)) {
            $this->error(sprintf('Fichier inconnu: %s', $filename));
            return false;
        }
        $emailAlias = array(
            'squadra.consultants@wanadoo.fr' => 'bdb@squadra.fr',
            'koudjo.amegbleame@kamconsulting.fr' => 'koudjo.amegbleame@gmail.com',
        );

        $skip = array('michel@cloudcontact.biz');

        $reader = Reader::createFromPath($filename);
        $reader->setDelimiter(';');
        $reader->each(function ($row, $index, $iterator) use ($reader, $skip, $emailAlias) {
            if ($index > 0) { // skip header
                if (count($row) == 7) {
                    $email = $row[3];
                    if (isset($emailAlias[$email])) {
                        $email = $emailAlias[$email];
                    }
                    if (!in_array($email, $skip)) {
                        $user = User::whereEmail($email)->first();
                        if (!is_object($user)) {
                            $user = new User();
                            $user->firstname = $row[1];
                            $user->lastname = $row[0];
                            $user->email = $email;
                            $user->phone = str_replace('Ph. ', '', $row[4]);
                            $user->role = 'member';
                            $user->password = Hash::make('etincelle');
                            $user->save();

                            Mail::send('emails.skedda-migration', array('user' => $user), function($m) use ($user)
                            {
                                $m->from($_ENV['mail_address'], $_ENV['mail_name'])
                                    ->bcc($_ENV['mail_bcc'])
                                    ->to($user->email, $user->fullname)
                                    ->subject(sprintf('%s - Outil de réservation de salles', $_ENV['organisation_name']));

                            });
                            $this->info(sprintf('%s: OK', $user->fullname));
                        }
                    }
                }

            }

            return true;
        });

    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
            array('filename', InputArgument::REQUIRED, 'CSV file with user\'s informations'),
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

