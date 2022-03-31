<?php

namespace App\Services\Impls;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class DashboardServiceImpl implements DashboardService
{
    public function createMenu(): array
    {
        $usr = Auth::user();
        $usrRoles = $usr->roles;
        $hasCompany = $usr->companies->count() != 0 ? true:false;

        $menu = [];

        $openAllMenu = $usrRoles->where('name', Config::get('const.DEFAULT.ROLE.DEV'))->isNotEmpty() ? true:false;

        array_push($menu, $this->createMenu_Dashboard());

        array_push($menu, $this->createMenu_Company());

        if($hasCompany) {
            array_push($menu, $this->createMenu_Product());
            array_push($menu, $this->createMenu_Supplier());
            //array_push($menu, $this->createMenu_Customer());
            array_push($menu, $this->createMenu_PurchaseOrder());
            //array_push($menu, $this->createMenu_SalesOrder());    
        }

        if ($usrRoles->where('name', Config::get('const.DEFAULT.ROLE.ADMIN'))->isNotEmpty() || $openAllMenu)
            array_push($menu, $this->createMenu_Administrator());
    
        if ($usrRoles->where('name', Config::get('const.DEFAULT.ROLE.DEV'))->isNotEmpty() || $openAllMenu)
            array_push($menu, $this->createMenu_DevTool());    

        return $menu;
    }

    private function createMenu_Dashboard(): array
    {
        $maindashboard = array(
            'icon' => '',
            'pageName' => 'side-menu-dashboard-maindashboard',
            'title' => 'components.menu.main-dashboard'
        );

        $root_array = array(
            'icon' => 'HomeIcon',
            'pageName' => 'side-menu-dashboard',
            'title' => 'components.menu.dashboard',
            'subMenu' => [
            ]
        );

        array_push($root_array['subMenu'], $maindashboard);

        return $root_array;
    }

    private function createMenu_Company(): array
    {
        $company = array(
            'icon' => '',
            'pageName' => 'side-menu-company-company',
            'title' => 'components.menu.company-company'
        );

        $branches = array(
            'icon' => '',
            'pageName' => 'side-menu-company-branch',
            'title' => 'components.menu.company-branch'
        );

        $warehouses = array(
            'icon' => '',
            'pageName' => 'side-menu-company-warehouse',
            'title' => 'components.menu.company-warehouse'
        );

        $employees = array(
            'icon' => '',
            'pageName' => 'side-menu-company-employee',
            'title' => 'components.menu.company-employee'
        );

        $root_array = array(
            'icon' => 'UmbrellaIcon',
            'pageName' => 'side-menu-company',
            'title' => 'components.menu.company',
            'subMenu' => [
            ]
        );

        array_push($root_array['subMenu'], $company, $branches, $warehouses, $employees);

        return $root_array;
    }

    private function createMenu_Administrator(): array
    {
        $user = array(
            'icon' => '',
            'pageName' => 'side-menu-administrator-user',
            'title' => 'components.menu.administrator-user'
        );

        $root_array = array(
            'icon' => 'CpuIcon',
            'pageName' => 'side-menu-administrator',
            'title' => 'components.menu.administrator',
            'subMenu' => [
            ]
        );

        array_push($root_array['subMenu'], $user);

        return $root_array;
    }

    private function createMenu_DevTool(): array
    {
        $dbbackup = array(
            'icon' => '',
            'pageName' => 'side-menu-devtool-backup',
            'title' => 'components.menu.devtool-dbbackup'
        );

        $playground = array(
            'icon' => '',
            'pageName' => 'side-menu-devtool-example',
            'title' => 'components.menu.devtool-playground',
            'subMenu' => [
            ]
        );

        $playground_ex1 = array(
            'icon' => '',
            'pageName' => 'side-menu-devtool-example-ex1',
            'title' => 'components.menu.devtool-playground-ex1'
        );

        $playground_ex2 = array(
            'icon' => '',
            'pageName' => 'side-menu-devtool-example-ex2',
            'title' => 'components.menu.devtool-playground-ex2'
        );

        array_push($playground['subMenu'], $playground_ex1);
        array_push($playground['subMenu'], $playground_ex2);

        $root_array = array(
            'icon' => 'GithubIcon',
            'pageName' => 'side-menu-devtool',
            'title' => 'components.menu.devtool',
            'subMenu' => [
            ]
        );

        array_push($root_array['subMenu'], $dbbackup);
        array_push($root_array['subMenu'], $playground);

        return $root_array;
    }

    private function createMenu_Product(): array
    {
        $product = array(
            'icon' => '',
            'pageName' => 'side-menu-product-product',
            'title' => 'components.menu.product-product'
        );

        $service = array(
            'icon' => '',
            'pageName' => 'side-menu-product-service',
            'title' => 'components.menu.product-service'
        );

        $root_array = array(
            'icon' => 'PackageIcon',
            'pageName' => 'side-menu-product',
            'title' => 'components.menu.product',
            'subMenu' => [
            ]
        );

        array_push($root_array['subMenu'], $product);
        array_push($root_array['subMenu'], $service);

        return $root_array; 
    }

    private function createMenu_Supplier(): array
    {
        $supplier = array(
            'icon' => '',
            'pageName' => 'side-menu-supplier-supplier',
            'title' => 'components.menu.supplier-supplier'
        );

        $root_array = array(
            'icon' => 'TruckIcon',
            'pageName' => 'side-menu-supplier',
            'title' => 'components.menu.supplier',
            'subMenu' => [
            ]
        );

        array_push($root_array['subMenu'], $supplier);

        return $root_array;
    }

    private function createMenu_Customer(): array
    {
        return array(

        );
    }

    private function createMenu_PurchaseOrder(): array
    {
        $po = array(
            'icon' => '',
            'pageName' => 'side-menu-purchase_order-purchaseorder',
            'title' => 'components.menu.purchase_order-purchaseorder'
        );

        $root_array = array(
            'icon' => 'FilePlusIcon',
            'pageName' => 'side-menu-purchase_order',
            'title' => 'components.menu.purchase_order',
            'subMenu' => [
            ]
        );

        array_push($root_array['subMenu'], $po);

        return $root_array;
    }

    private function createMenu_SalesOrder(): array
    {
        return array(

        );
    }
}