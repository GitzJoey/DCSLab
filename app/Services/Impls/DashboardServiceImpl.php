<?php

namespace App\Services\Impls;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class DashboardServiceImpl implements DashboardService
{
    public function createMenu(): array
    {
        $usrRoles = Auth::user()->roles;
        
        $menu = [];

        $openAllMenu = $usrRoles->where('name', Config::get('const.DEFAULT.ROLE.DEV'))->isNotEmpty() ? true:false;

        array_push($menu, $this->createMenu_Dashboard());

        array_push($menu, $this->createMenu_Company());
        array_push($menu, $this->createMenu_Product());
        array_push($menu, $this->createMenu_Supplier());
        array_push($menu, $this->createMenu_PurchaseOrder());
        array_push($menu, $this->createMenu_SalesOrder());

        if ($usrRoles->where('name', Config::get('const.DEFAULT.ROLE.ADMIN'))->isNotEmpty() || $openAllMenu)
            array_push($menu, $this->createMenu_Administrator());
        
        if ($usrRoles->where('name', Config::get('const.DEFAULT.ROLE.DEV'))->isNotEmpty() || $openAllMenu)
            array_push($menu, $this->createMenu_DevTool());

        return $menu;
    }

    private function createMenu_Dashboard(): array
    {
        return array(
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
        );
    }

    private function createMenu_Company(): array
    {
        return array(
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
        );
    }

    private function createMenu_Administrator(): array
    {
        return array(
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
        );
    }

    private function createMenu_DevTool(): array
    {
        return array(
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
        );
    }

    private function createMenu_Product(): array
    {
        return array(
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
        );
    }

    private function createMenu_Supplier(): array
    {
        return array(
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
        );
    }

    private function createMenu_Customer(): array
    {
        return array(

        );
    }

    private function createMenu_PurchaseOrder(): array
    {
        return array(
            'icon' => 'FilePlusIcon',
            'pageName' => 'side-menu-purchase_order',
            'title' => 'components.menu.purchase_order',
            'subMenu' => [
                array (
                    'icon' => '',
                    'pageName' => 'side-menu-purchase_order-purchaseorder',
                    'title' => 'components.menu.purchase_order-purchaseorder'
                )
            ]

        );
    }

    private function createMenu_SalesOrder(): array
    {
        return array(

        );
    }
}