<?php

namespace App\Listeners;

use App\Events\Registered;
use App\Mail\NewUserConfirm;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SendConfirmEmail
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
    public function handle(Registered $event)
    {
        //if user has email address, send an email to user
        if ($event->user->email) {
            Mail::to($event->user->email)
                ->send(new NewUserConfirm($event->user));
        }
    }
}
