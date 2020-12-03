<?php

namespace App\Providers;

use App\Utils\Arr;
use App\Utils\State;
use Illuminate\Auth\Events\Authenticated;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
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

        Event::listen(Authenticated::class, function(Authenticated $event) {
            // Log::info($event->user);
            if (! State::has('user_preferences_loaded')) {
                if ($file = config_path('user_preferences.ser')) {
                    if ($preferences = Arr::get(unserialize(file_get_contents($file)), $event->user->id)) {
                        config($preferences);
                    }
                }
                State::set('user_preferences_loaded', true);
            }
        });
    }
}
