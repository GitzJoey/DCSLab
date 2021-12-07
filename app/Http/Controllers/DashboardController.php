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
                'icon' => 'TruckIcon',
                'pageName' => 'side-menu-supplier',
                'title' => 'components.menu.supplier',
                'subMenu' => [
                    array (
                        'icon' => '',
                        'pageName' => 'side-menu-supplier-supplier',
                        'title' => 'components.menu.supplier-supplier'
                    ),
                ]
            ),
            array(
                'icon' => 'PackageIcon',
                'pageName' => 'side-menu-product',
                'title' => 'components.menu.product',
                'subMenu' => [
                    array (
                        'icon' => '',
                        'pageName' => 'side-menu-product-product',
                        'title' => 'components.menu.product-product'
                    ),
                    array (
                        'icon' => '',
                        'pageName' => 'side-menu-product-service',
                        'title' => 'components.menu.product-service'
                    )
                ]
            ),
            array(
                'icon' => 'CpuIcon',
                'pageName' => 'side-menu-administrator',
                'title' => 'components.menu.administrator',
                'subMenu' => [
                    array (
                        'icon' => '',
                        'pageName' => 'side-menu-administrator-user',
                        'title' => 'components.menu.administrator-user'
                    )
                ]
            ),
            array(
                'icon' => 'GithubIcon',
                'pageName' => 'side-menu-devtool',
                'title' => 'components.menu.devtool',
                'subMenu' => [
                    array (
                        'icon' => '',
                        'pageName' => 'side-menu-devtool-backup',
                        'title' => 'components.menu.devtool-dbbackup'
                    ),
                    array (
                        'icon' => '',
                        'pageName' => 'side-menu-devtool-example',
                        'title' => 'components.menu.devtool-playground',
                        'subMenu' => [
                            array (
                                'icon' => '',
                                'pageName' => 'side-menu-devtool-example-ex1',
                                'title' => 'components.menu.devtool-playground-ex1'
                            ),
                            array (
                                'icon' => '',
                                'pageName' => 'side-menu-devtool-example-ex2',
                                'title' => 'components.menu.devtool-playground-ex2'
                            )
                        ]
                    )
                ]
            )
        ];
    }
}
