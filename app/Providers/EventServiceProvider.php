<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'App\Events\Registered' => [
            'App\Listeners\SendWelcomeEmail',
        ],
        'App\Events\SmsOutboxCreated' => [
            'App\Listeners\SendUserSms',
        ],
        'App\Events\UssdRegistrationCreated' => [
            'App\Listeners\SaveUssdRegistrationArchive',
        ],
        'App\Events\UssdRegistrationUpdated' => [
            'App\Listeners\UpdateUssdRegistrationArchive',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
