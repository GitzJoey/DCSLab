<?php

namespace App\Services\Impls;

use App\Services\ActivityLogService;

use Illuminate\Support\Facades\Auth;
use Spatie\Activitylog\Models\Activity;

class ActivityLogServiceImpl implements ActivityLogService
{

    public function RoutingActivity($routeName, $routeParameters)
    {
        switch ($routeName) {
            case 'side-menu-dashboard-maindashboard':
                $friendlyName = 'Dashboard';
                break;
            case 'side-menu-dashboard-profile':
                $friendlyName = 'Profile';
                break;
            case 'side-menu-dashboard-activity':
                $friendlyName = 'Activity';
                break;
            case 'side-menu-dashboard-inbox':
                $friendlyName = 'Inbox';
                break;
            case 'side-menu-administrators-users':
                $friendlyName = 'Users';
                break;
            case 'side-menu-devtools-backup':
                $friendlyName = 'DB Backup';
                break;
            case 'side-menu-devtools-examples-ex1':
                $friendlyName = 'Example 1';
                break;
            case 'side-menu-devtools-examples-ex2':
                $friendlyName = 'Example 2';
                break;
            default:
                $friendlyName = $routeName;
                break;
        }

        if (strlen($friendlyName) != 0) {
            if (!empty($routeParameters)) {
                activity('RoutingActivity')
                    ->withProperty('parameters', $routeParameters)
                    ->log('Navigating to '.$friendlyName);
            } else {
                activity('RoutingActivity')
                    ->log('Navigating to '.$friendlyName);
            }
        }
    }

    public function AuthActivity($type)
    {
        activity('AuthActivity')
            ->log(ucfirst($type));
    }

    public function getAuthUserActivities($maxRecords = 25)
    {
        $id = Auth::user();

        return Activity::causedBy($id)
            ->whereIn('log_name', ['AuthActivity', 'RoutingActivity'])
            ->latest()->take($maxRecords)->select('log_name', 'description', 'properties', 'created_at')->get();
    }
}
