<?php

namespace App\Services;

use Spatie\Activitylog\Models\Activity;

interface ActivityLogService
{
    public function RoutingActivity(
        string $routeName,
        string $routeParameters
    ): void;

    public function AuthActivity(
        string $type
    ): void;

    public function getAuthUserActivities(int $maxRecords = 25): Activity;
}
