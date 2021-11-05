<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    private $activityLogService;

    public function __construct(ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('dashboard.midone');
    }

    public function userMenu()
    {
        return [
            array(
                'icon' => 'HomeIcon',
                'pageName' => 'side-menu-dashboard',
                'title' => 'Dashboard',
                'subMenu' => [
                    array (
                        'icon' => '',
                        'pageName' => 'side-menu-dashboard-maindashboard',
                        'title' => 'Main Dashboard'
                    )
                ]
            ),
            array(
                'icon' => 'CpuIcon',
                'pageName' => 'side-menu-administrators',
                'title' => 'Administrator',
                'subMenu' => [
                    array (
                        'icon' => '',
                        'pageName' => 'side-menu-administrators-users',
                        'title' => 'Users'
                    )
                ]
            ),
            array(
                'icon' => 'GithubIcon',
                'pageName' => 'side-menu-devtools',
                'title' => 'Dev Tools',
                'subMenu' => [
                    array (
                        'icon' => '',
                        'pageName' => 'side-menu-devtools-backup',
                        'title' => 'DB Backup'
                    ),
                    array (
                        'icon' => '',
                        'pageName' => 'side-menu-devtools-examples',
                        'title' => 'Playgrounds',
                        'subMenu' => [
                            array (
                                'icon' => '',
                                'pageName' => 'side-menu-devtools-examples-ex1',
                                'title' => 'Example 1'
                            ),
                            array (
                                'icon' => '',
                                'pageName' => 'side-menu-devtools-examples-ex2',
                                'title' => 'Example 2'
                            )
                        ]
                    )
                ]
            )
        ];
    }
}
