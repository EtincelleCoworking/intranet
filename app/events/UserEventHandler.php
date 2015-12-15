<?php


class UserEventHandler
{

    /**
     * Handle user login events.
     */
    public function onUserLogin($user)
    {
//        $user->last_login_at = new DateTime();
//        $user->save();
    }

    /**
     * Handle user logout events.
     */
    public function onUserLogout($event)
    {
        //
    }

    /**
     * Register the listeners for the subscriber.
     *
     * @param  Illuminate\Events\Dispatcher $events
     * @return array
     */
    public function subscribe($events)
    {
        $events->listen('auth.login', 'UserEventHandler@onUserLogin');

        $events->listen('auth.logout', 'UserEventHandler@onUserLogout');
    }

}
