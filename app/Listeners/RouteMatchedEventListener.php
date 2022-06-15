<?php

namespace App\Listeners;

use App\Services\ActivityLogService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Routing\Route;

class RouteMatchedEventListener
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
        if (!array_key_exists('as', $event->route->action)) return;
        $routeName = $event->route->action['as'];
        $routeParameters = $event->route->action->parameters;

        $this->activityLogService->RoutingActivity($routeName, $routeParameters);
    }
}
