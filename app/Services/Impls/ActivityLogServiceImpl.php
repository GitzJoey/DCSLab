<?php

namespace App\Services\Impls;

use App\Services\ActivityLogService;

use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class ActivityLogServiceImpl implements ActivityLogService
{
    public function __construct()
    {
        
    }

    public function RoutingActivity(string $routeName, array $routeParameters): void
    {
        $friendlyName = match ($routeName) {
            'db' => 'Dashboard',
            default => ''
        };

        if (empty($friendlyName)) return;

        if (!empty($routeParameters)) {
            activity('RoutingActivity')
                ->withProperty('parameters', $routeParameters)
                ->log('Navigating to '.$friendlyName);
        } else {
            activity('RoutingActivity')
                ->log('Navigating to '.$friendlyName);
        }
    }

    public function AuthActivity(string $type): void
    {
        activity('AuthActivity')
            ->log(ucfirst($type));
    }

    public function getAuthUserActivities(int $maxRecords = 25)
    {
        $id = Auth::user();

        return Activity::causedBy($id)
            ->whereIn('log_name', ['AuthActivity', 'RoutingActivity'])
            ->latest()->take($maxRecords)->select('log_name', 'description', 'properties', 'created_at')->get();
    }
}
