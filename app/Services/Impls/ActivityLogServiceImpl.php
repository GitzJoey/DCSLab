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
            case 'db':
                $friendlyName = 'Dashboard';
                break;
            case 'db.company.companies':
                $friendlyName = 'Companies';
                break;
            case 'db.company.employees':
                $friendlyName = 'Employees';
                break;
            case 'db.company.branches':
                $friendlyName = 'Branches';
                break;
            case 'db.company.warehouses':
                $friendlyName = 'Warehouses';
                break;    
            case 'db.finance.cashes':
                $friendlyName = 'Cashes';
                break;    
            case 'db.purchase.suppliers':
                $friendlyName = 'Suppliers';
                break; 
            case 'db.product.groups':
                $friendlyName = 'Product Groups';
                break;    
            case 'db.product.brands':
                $friendlyName = 'Product Brands';
                break;    
            case 'db.units':
                $friendlyName = 'Product Units';
                break;
            case 'db.products':
                $friendlyName = 'Products';
                break;
            case 'db.customer_groups':
                $friendlyName = 'Customer Groups';
                break;
            case 'db.customers':
                $friendlyName = 'Customers';
                break;
            case 'db.profile':
                $friendlyName = 'Profile';
                break;
            case 'db.activity':
                $friendlyName = 'Activity';
                break;
            case 'db.inbox':
                $friendlyName = 'Inbox';
                break;
            case 'db.admin.users.users':
                $friendlyName = 'Users';
                break;
            case 'db.admin.users.roles':
                $friendlyName = 'Roles';
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

    public function GetActivityById($id, $maxRecords = 25)
    {
        $usr = Auth::user();
        return Activity::causedBy($usr)
            ->whereIn('log_name', ['AuthActivity', 'RoutingActivity'])
            ->latest()->take($maxRecords)->select('log_name', 'description', 'properties', 'created_at')->get();
    }
}
