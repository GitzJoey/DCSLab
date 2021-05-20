<?php

namespace App\Services;

interface ActivityLogService
{
    public function RoutingActivity(
        $routeName,
        $routeParameters
    );

    public function AuthActivity(
        $type
    );

    public function GetActivityById($id);
}
