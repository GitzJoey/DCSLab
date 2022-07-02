<?php

namespace App\Listeners;

use App\Services\ActivityLogService;
use App\Services\DashboardService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogoutEventListener
{
    private $activityLogService;
    private $dashboardService;

    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(ActivityLogService $activityLogService, DashboardService $dashboardService)
    {
        $this->activityLogService = $activityLogService;
        $this->dashboardService = $dashboardService;
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $this->activityLogService->AuthActivity('Logout');

        if (auth()->check()) {
            $this->dashboardService->clearUserCache(auth()->id());
        }
    }
}
