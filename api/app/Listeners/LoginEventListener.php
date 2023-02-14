<?php

namespace App\Listeners;

use App\Services\ActivityLogService;

class LoginEventListener
{
    private $activityLogService;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ActivityLogService $activityLogService)
    {
        $this->activityLogService = $activityLogService;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $this->activityLogService->AuthActivity('Login');
    }
}
