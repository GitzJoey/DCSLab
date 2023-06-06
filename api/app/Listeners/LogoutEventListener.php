<?php

namespace App\Listeners;

use Illuminate\Support\Facades\Cache;

class LogoutEventListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if (is_null(auth())) {
            return;
        }

        Cache::tags([auth()->id()])->flush();
    }
}
