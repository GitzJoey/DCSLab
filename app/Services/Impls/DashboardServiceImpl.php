<?php

namespace App\Services\Impls;

use App\Services\DashboardService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;

class DashboardServiceImpl implements DashboardService
{
    public function __construct()
    {
        
    }
    
    public function createMenu(): array
    {
        $menu = [];

        $usr = Auth::user();

        $usrRoles = $usr->roles;
        $hasUserRole = $usrRoles->where('name', Config::get('const.DEFAULT.ROLE.USER'))->isNotEmpty() ? true:false;
        $hasOnlyUserRole = $usrRoles->where('name', Config::get('const.DEFAULT.ROLE.USER'))->isNotEmpty() && $usrRoles->count() == 1 ? true:false;
        $hasDevRole = $usrRoles->where('name', Config::get('const.DEFAULT.ROLE.DEV'))->isNotEmpty() ? true:false;
        $hasAdminRole = $usrRoles->where('name', Config::get('const.DEFAULT.ROLE.ADMIN'))->isNotEmpty() ? true:false;

        $hasCompany = $usr->companies->count() != 0 ? true:false;

        $showDemoMenu = false;

        $menu = $this->createMenu_Dashboard($menu, $showDemoMenu);
        $menu = $this->createMenu_Company($menu, $hasOnlyUserRole, $hasCompany, $hasDevRole);
        $menu = $this->createMenu_Product($menu, $hasCompany, $hasDevRole);
        $menu = $this->createMenu_Supplier($menu, $hasCompany, $hasDevRole);
        $menu = $this->createMenu_Customer($menu, $hasCompany, $hasDevRole);
        $menu = $this->createMenu_PurchaseOrder($menu, $hasCompany, $hasDevRole);
        $menu = $this->createMenu_SalesOrder($menu, $hasCompany, $hasDevRole);
        $menu = $this->createMenu_Administrator($menu, $hasAdminRole, $hasDevRole);
        $menu = $this->createMenu_DevTool($menu, $hasDevRole);

        return $menu;
    }

    private function createMenu_Dashboard(array $menu, bool $showDemo): array
    {
        $maindashboard = array(
            'icon' => '',
            'pageName' => 'side-menu-dashboard-maindashboard',
            'title' => 'components.menu.main-dashboard'
        );

        $demo = array(
            'icon' => '',
            'pageName' => 'side-menu-dashboard-demo',
            'title' => 'components.menu.main-demo'
        );

        $root_array = array(
            'icon' => 'HomeIcon',
            'pageName' => 'side-menu-dashboard',
            'title' => 'components.menu.dashboard',
            'subMenu' => [
            ]
        );

        if ($showDemo) 
            array_push($root_array['subMenu'], $maindashboard, $demo);
        else
            array_push($root_array['subMenu'], $maindashboard);
        
        array_push($menu, $root_array);

        return $menu;
    }

    private function createMenu_Company(array $menu, bool $hasOnlyUserRole, bool $hasCompany, bool $hasDevRole): array
    {
        if ($hasOnlyUserRole) return $menu;

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

        $root_array = array(
            'icon' => 'UmbrellaIcon',
            'pageName' => 'side-menu-company',
            'title' => 'components.menu.company',
            'subMenu' => [
            ]
        );

        if ($hasCompany || $hasDevRole)
            array_push($root_array['subMenu'], $company, $branches, $warehouses);
        else 
            array_push($root_array['subMenu'], $company);

        array_push($menu, $root_array);

        return $menu;
    }

    private function createMenu_Administrator(array $menu, bool $hasAdminRole, bool $hasDevRole): array
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

        if ($hasAdminRole || $hasDevRole)
            array_push($menu, $root_array);

        return $menu;
    }

    private function createMenu_DevTool(array $menu, bool $hasDevRole): array
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

        if ($hasDevRole)
            array_push($menu, $root_array);

        return $menu;
    }

    private function createMenu_Product(array $menu, bool $hasCompany, bool $hasDevRole): array
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

        if ($hasCompany || $hasDevRole)
            array_push($menu, $root_array);

        return $menu;
    }

    private function createMenu_Supplier(array $menu, bool $hasCompany, bool $hasDevRole): array
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

        if ($hasCompany || $hasDevRole)
            array_push($menu, $root_array);

        return $menu;
    }

    private function createMenu_Customer(array $menu, bool $hasCompany, bool $hasDevRole): array
    {
        return $menu;
    }

    private function createMenu_PurchaseOrder(array $menu, bool $hasCompany, bool $hasDevRole): array
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

        if ($hasCompany || $hasDevRole)
            array_push($menu, $root_array);

        return $menu;
    }

    private function createMenu_SalesOrder(array $menu, bool $hasCompany, bool $hasDevRole): array
    {
        return $menu;
    }
}