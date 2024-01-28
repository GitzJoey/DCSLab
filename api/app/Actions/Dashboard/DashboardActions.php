<?php

namespace App\Actions\Dashboard;

use App\Enums\UserRoles;
use App\Traits\CacheHelper;
use Illuminate\Support\Facades\Auth;

class DashboardActions
{
    use CacheHelper;

    public function __construct()
    {
    }

    public function createUserMenu(bool $useCache = true): array
    {
        $cacheKey = '';
        if ($useCache) {
            $cacheKey = 'menu_'.Auth::id();
            $cacheResult = $this->readFromCache($cacheKey);

            if (! is_null($cacheResult)) {
                return $cacheResult;
            }
        }

        $menu = [];

        $usr = Auth::user();

        $usrRoles = $usr->roles;

        $hasUserRole = $usrRoles->where('name', UserRoles::USER->value)->isNotEmpty() ? true : false;
        $hasOnlyUserRole = $usrRoles->where('name', UserRoles::USER->value)->isNotEmpty() && $usrRoles->count() == 1 ? true : false;

        $hasAdminRole = $usrRoles->where('name', UserRoles::ADMINISTRATOR->value)->isNotEmpty() ? true : false;
        $hasOnlyAdminRole = $usrRoles->where('name', UserRoles::ADMINISTRATOR->value)->isNotEmpty() && $usrRoles->count() == 1 ? true : false;

        $hasDevRole = $usrRoles->where('name', UserRoles::DEVELOPER->value)->isNotEmpty() ? true : false;

        $hasCompany = false; //$usr->companies->count() != 0 ? true : false;

        $showDemoMenu = false;

        $menu = $this->createMenu_Dashboard($menu, $showDemoMenu);
        $menu = $this->createMenu_Company($menu, $hasOnlyUserRole, $hasOnlyAdminRole, $hasCompany, $hasDevRole);
        $menu = $this->createMenu_PurchaseOrder($menu, $hasCompany, $hasDevRole);
        $menu = $this->createMenu_Administrator($menu, $hasAdminRole, $hasDevRole);
        $menu = $this->createMenu_DevTool($menu, $hasDevRole);

        $this->saveToCache($cacheKey, $menu);

        return $menu;
    }

    private function createMenu_Dashboard(array $menu, bool $showDemo): array
    {
        $maindashboard = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-dashboard-maindashboard',
            'title' => 'components.menu.main-dashboard',
        ];

        $demo = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-dashboard-demo',
            'title' => 'components.menu.main-demo',
        ];

        $root_array = [
            'icon' => 'Home',
            'pageName' => 'side-menu-dashboard',
            'title' => 'components.menu.dashboard',
            'subMenu' => [],
        ];

        if ($showDemo) {
            array_push($root_array['subMenu'], $maindashboard, $demo);
        } else {
            array_push($root_array['subMenu'], $maindashboard);
        }

        array_push($menu, $root_array);

        return $menu;
    }

    private function createMenu_Company(array $menu, bool $hasOnlyUserRole, bool $hasOnlyAdminRole, bool $hasCompany, bool $hasDevRole): array
    {
        if ($hasOnlyUserRole || $hasOnlyAdminRole) {
            return $menu;
        }

        $company = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-company-company',
            'title' => 'components.menu.company-company',
        ];

        $branches = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-company-branch',
            'title' => 'components.menu.company-branch',
        ];

        $employees = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-company-employee',
            'title' => 'components.menu.company-employee',
        ];

        $root_array = [
            'icon' => 'Umbrella',
            'pageName' => 'side-menu-company',
            'title' => 'components.menu.company',
            'subMenu' => [],
        ];

        if ($hasCompany || $hasDevRole) {
            array_push($root_array['subMenu'], $company, $branches, $employees);
        } else {
            array_push($root_array['subMenu'], $company);
        }

        array_push($menu, $root_array);

        return $menu;
    }

    private function createMenu_Product(array $menu, bool $hasCompany, bool $hasDevRole): array
    {
        $product_group = [
            'icon' => '',
            'pageName' => 'side-menu-product-product_group',
            'title' => 'components.menu.product-product_group',
        ];

        $brand = [
            'icon' => '',
            'pageName' => 'side-menu-product-brand',
            'title' => 'components.menu.product-brand',
        ];

        $unit = [
            'icon' => '',
            'pageName' => 'side-menu-product-unit',
            'title' => 'components.menu.product-unit',
        ];

        $product = [
            'icon' => '',
            'pageName' => 'side-menu-product-product',
            'title' => 'components.menu.product-product',
        ];

        $root_array = [
            'icon' => 'Package',
            'pageName' => 'side-menu-product',
            'title' => 'components.menu.product',
            'subMenu' => [],
        ];

        array_push($root_array['subMenu'], $product_group);
        array_push($root_array['subMenu'], $brand);
        array_push($root_array['subMenu'], $unit);
        array_push($root_array['subMenu'], $product);

        if ($hasCompany || $hasDevRole) {
            array_push($menu, $root_array);
        }

        return $menu;
    }

    private function createMenu_Customer(array $menu, bool $hasCompany, bool $hasDevRole): array
    {
        $customer_group = [
            'icon' => '',
            'pageName' => 'side-menu-customer-customer_group',
            'title' => 'components.menu.customer-customer_group',
        ];

        $customer = [
            'icon' => '',
            'pageName' => 'side-menu-customer-customer',
            'title' => 'components.menu.customer-customer',
        ];

        $root_array = [
            'icon' => 'Truck',
            'pageName' => 'side-menu-customer',
            'title' => 'components.menu.customer',
            'subMenu' => [],
        ];

        array_push($root_array['subMenu'], $customer_group);
        array_push($root_array['subMenu'], $customer);

        if ($hasCompany || $hasDevRole) {
            array_push($menu, $root_array);
        }

        return $menu;
    }

    private function createMenu_PurchaseOrder(array $menu, bool $hasCompany, bool $hasDevRole): array
    {
        $purchase_order = [
            'icon' => '',
            'pageName' => 'side-menu-purchase_order-purchaseorder',
            'title' => 'components.menu.purchase_order-purchaseorder',
        ];

        $root_array = [
            'icon' => 'FilePlus',
            'pageName' => 'side-menu-purchase_order',
            'title' => 'components.menu.purchase_order',
            'subMenu' => [],
        ];

        array_push($root_array['subMenu'], $purchase_order);

        if ($hasCompany || $hasDevRole) {
            array_push($menu, $root_array);
        }

        return $menu;
    }

    private function createMenu_Administrator(array $menu, bool $hasAdminRole, bool $hasDevRole): array
    {
        $user = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-administrator-user',
            'title' => 'components.menu.administrator-user',
        ];

        $root_array = [
            'icon' => 'Cpu',
            'pageName' => 'side-menu-administrator',
            'title' => 'components.menu.administrator',
            'subMenu' => [],
        ];

        array_push($root_array['subMenu'], $user);

        if ($hasAdminRole || $hasDevRole) {
            array_push($menu, $root_array);
        }

        return $menu;
    }

    private function createMenu_DevTool(array $menu, bool $hasDevRole): array
    {
        $devtool = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-devtool-devtool',
            'title' => 'components.menu.devtool-devtool',
        ];

        $playground = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-devtool-playground',
            'title' => 'components.menu.devtool-playground',
            'subMenu' => [],
        ];

        $playground_ex1 = [
            'icon' => 'ChevronsRight',
            'pageName' => 'side-menu-devtool-playground-p1',
            'title' => 'components.menu.devtool-playground-p1',
        ];

        $playground_ex2 = [
            'icon' => 'ChevronsRight',
            'pageName' => 'side-menu-devtool-playground-p2',
            'title' => 'components.menu.devtool-playground-p2',
        ];

        array_push($playground['subMenu'], $playground_ex1);
        array_push($playground['subMenu'], $playground_ex2);

        $root_array = [
            'icon' => 'Github',
            'pageName' => 'side-menu-devtool',
            'title' => 'components.menu.devtool',
            'subMenu' => [],
        ];

        array_push($root_array['subMenu'], $devtool);
        array_push($root_array['subMenu'], $playground);

        if ($hasDevRole) {
            array_push($menu, $root_array);
        }

        return $menu;
    }
}
