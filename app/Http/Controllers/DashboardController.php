<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use App\Services\UserService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends BaseController
{
    private $userService;
    private $activityLogService;

    public function __construct(UserService $userService, ActivityLogService $activityLogService)
    {
        parent::__construct();

        $this->middleware('auth');
        $this->userService = $userService;
        $this->activityLogService = $activityLogService;
    }

    public function index(Request $request)
    {
        $this->activityLogService->RoutingActivity($request->route()->getName(), $request->all());

        return view('dashboard.midone');
    }

    public function userProfile()
    {
        $parameters = array (
            'readById' =>  Auth::user()->id
        );

        return $this->userService->read($parameters);
    }

    public function userMenu()
    {
        return [
            array(
                'icon' => 'HomeIcon',
                'pageName' => 'side-menu-dashboar',
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
