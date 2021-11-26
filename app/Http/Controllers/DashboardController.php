<?php

namespace App\Http\Controllers;

use App\Services\ActivityLogService;
use Illuminate\Http\Request;

class DashboardController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        return view('dashboard.midone');
    }

    public function userMenu()
    {
        return [
            array(
                'icon' => 'HomeIcon',
                'pageName' => 'side-menu-dashboard',
                'title' => 'components.menu.dashboard',
                'subMenu' => [
                    array (
                        'icon' => '',
                        'pageName' => 'side-menu-dashboard-maindashboard',
                        'title' => 'components.menu.main-dashboard'
                    )
                ]
            ),
            array(
                'icon' => 'UmbrellaIcon',
                'pageName' => 'side-menu-company',
                'title' => 'components.menu.company',
                'subMenu' => [
                    array (
                        'icon' => '',
                        'pageName' => 'side-menu-company-company',
                        'title' => 'components.menu.company-company'
                    )
                ]
            ),
            array(
                'icon' => 'CpuIcon',
                'pageName' => 'side-menu-administrators',
                'title' => 'components.menu.administrators',
                'subMenu' => [
                    array (
                        'icon' => '',
                        'pageName' => 'side-menu-administrators-users',
                        'title' => 'components.menu.administrators-users'
                    )
                ]
            ),
            array(
                'icon' => 'GithubIcon',
                'pageName' => 'side-menu-devtools',
                'title' => 'components.menu.devtools',
                'subMenu' => [
                    array (
                        'icon' => '',
                        'pageName' => 'side-menu-devtools-backup',
                        'title' => 'components.menu.devtools-dbbackup'
                    ),
                    array (
                        'icon' => '',
                        'pageName' => 'side-menu-devtools-examples',
                        'title' => 'components.menu.devtools-playgrounds',
                        'subMenu' => [
                            array (
                                'icon' => '',
                                'pageName' => 'side-menu-devtools-examples-ex1',
                                'title' => 'components.menu.devtools-playgrounds-ex1'
                            ),
                            array (
                                'icon' => '',
                                'pageName' => 'side-menu-devtools-examples-ex2',
                                'title' => 'components.menu.devtools-playgrounds-ex2'
                            )
                        ]
                    )
                ]
            )
        ];
    }
}
