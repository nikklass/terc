<?php

namespace App\Listeners;

use App\Events\AccountAdded;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Mail;
use App\Mail\NewUserWelcome;

class SendWelcomeEmail
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  Registered  $event
     * @return void
     */
    public function handle(AccountAdded $event)
    {
        //if user has email address, send an email to user
        if ($event->user->email) {
            Mail::to($event->user->email)
                ->send(new NewUserWelcome($event->user));
        }
    }
}
