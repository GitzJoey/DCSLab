<?php

namespace App\Services;

interface ActivityLogService
{
    public function RoutingActivity(
        string $routeName,
        array $routeParameters
    ): void;

    public function AuthActivity(
        string $type
    ): void;

    public function getAuthUserActivities(int $maxRecords = 25);
}
