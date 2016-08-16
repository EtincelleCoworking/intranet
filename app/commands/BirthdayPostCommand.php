<?php

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use GuzzleHttp\Client;

class BirthdayPostCommand extends Command
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'etincelle:birthday';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ajoute un message de bon anniversaire sur le mur';

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
        $author = User::where('role', '=', 'superadmin')->first();
        if (!$author) {
            $this->error('Impossible de trouver un administrateur comme auteur des messages');
            return false;
        }
        $count = 0;
        /** @var User $user */
        foreach (User::where('birthday', 'like', date('%-m-d'))->where('is_member', '=', true)->get() as $user) {
            $post = new WallPost();
            $post->setAsRoot();
            $post->user_id = $author->id;
            $post->message = $this->getMessage($user);
            if ($post->save()) {
                $this->info(sprintf('User: %s', $user->fullname));
                $count++;
            }
        }
        if ($count) {
            $this->info(sprintf('%d anniversaire%s', $count, ($count > 1) ? 's' : ''));
            WallPost::purgeCache();
        } else {
            $this->info('Aucun anniversaire aujourd\'hui');
        }
    }

    protected function getGifUrl()
    {
        $result = json_decode(file_get_contents(sprintf('http://api.giphy.com/v1/gifs/search?q=happy+birthday&limit=1&offset=%d&api_key=dc6zaTOxFJmzC', rand(0, 1000))));
        return $result->data[0]->images->original->url;
        //return $result->data[0]->images->fixed_height->url;
    }

    protected function getMessage($user)
    {
        $message = sprintf('Joyeux anniversaire <a href="%s">%s</a>!', route('user_profile', $user->id), $user->fullname);
        $message .= "\n\n";
        $message .= sprintf('![](%s "Happy Birthday")', $this->getGifUrl());
        $message .= "\n\n";
        $message .= sprintf('(Image au hasard, Powered By [Giphy](http://giphy.com/))', $this->getGifUrl());
        return $message;
    }

    /**
     * Get the console command arguments.
     *
     * @return array
     */
    protected function getArguments()
    {
        return array(
//            array('login', InputArgument::REQUIRED, 'Website login'),
//            array('password', InputArgument::REQUIRED, 'Website password'),
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
