<?php

namespace App\Services\Impls;

use App\Enums\UserRoles;
use App\Services\DashboardService;
use App\Traits\CacheHelper;
use Illuminate\Support\Facades\Auth;

class DashboardServiceImpl implements DashboardService
{
    use CacheHelper;

    public function __construct()
    {
    }

    public function clearUserCache($userId): bool
    {
        $this->flushCache($userId);

        return true;
    }

    public function createMenu(bool $useCache = true): array
    {
        $cacheKey = '';
        if ($useCache) {
            $cacheKey = 'menu_'.Auth::id();
            $cacheResult = $this->readFromCache($cacheKey);

            if (!is_null($cacheResult)) {
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

        $hasCompany = $usr->companies->count() != 0 ? true : false;

        $showDemoMenu = false;

        $menu = $this->createMenu_Dashboard($menu, $showDemoMenu);
        $menu = $this->createMenu_Company($menu, $hasOnlyUserRole, $hasOnlyAdminRole, $hasCompany, $hasDevRole);
        $menu = $this->createMenu_Product($menu, $hasCompany, $hasDevRole);
        $menu = $this->createMenu_Supplier($menu, $hasCompany, $hasDevRole);
        $menu = $this->createMenu_Customer($menu, $hasCompany, $hasDevRole);
        $menu = $this->createMenu_PurchaseOrder($menu, $hasCompany, $hasDevRole);
        $menu = $this->createMenu_SalesOrder($menu, $hasCompany, $hasDevRole);
        $menu = $this->createMenu_Administrator($menu, $hasAdminRole, $hasDevRole);
        $menu = $this->createMenu_DevTool($menu, $hasDevRole);

        if ($useCache) {
            $this->saveToCache($cacheKey, $menu);
        }

        return $menu;
    }

    private function createMenu_Dashboard(array $menu, bool $showDemo): array
    {
        $maindashboard = [
            'icon' => '',
            'pageName' => 'side-menu-dashboard-maindashboard',
            'title' => 'components.menu.main-dashboard',
        ];

        $demo = [
            'icon' => '',
            'pageName' => 'side-menu-dashboard-demo',
            'title' => 'components.menu.main-demo',
        ];

        $root_array = [
            'icon' => 'HomeIcon',
            'pageName' => 'side-menu-dashboard',
            'title' => 'components.menu.dashboard',
            'subMenu' => [
            ],
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
            'icon' => '',
            'pageName' => 'side-menu-company-company',
            'title' => 'components.menu.company-company',
        ];

        $branches = [
            'icon' => '',
            'pageName' => 'side-menu-company-branch',
            'title' => 'components.menu.company-branch',
        ];

        $employees = [
            'icon' => '',
            'pageName' => 'side-menu-company-employee',
            'title' => 'components.menu.company-employee',
        ];

        $warehouses = [
            'icon' => '',
            'pageName' => 'side-menu-company-warehouse',
            'title' => 'components.menu.company-warehouse',
        ];

        $root_array = [
            'icon' => 'UmbrellaIcon',
            'pageName' => 'side-menu-company',
            'title' => 'components.menu.company',
            'subMenu' => [
            ],
        ];

        if ($hasCompany || $hasDevRole) {
            array_push($root_array['subMenu'], $company, $branches, $employees, $warehouses);
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

        $service = [
            'icon' => '',
            'pageName' => 'side-menu-product-service',
            'title' => 'components.menu.product-service',
        ];

        $root_array = [
            'icon' => 'PackageIcon',
            'pageName' => 'side-menu-product',
            'title' => 'components.menu.product',
            'subMenu' => [
            ],
        ];

        array_push($root_array['subMenu'], $product_group);
        array_push($root_array['subMenu'], $brand);
        array_push($root_array['subMenu'], $unit);
        array_push($root_array['subMenu'], $product);
        array_push($root_array['subMenu'], $service);

        if ($hasCompany || $hasDevRole) {
            array_push($menu, $root_array);
        }

        return $menu;
    }

    private function createMenu_Supplier(array $menu, bool $hasCompany, bool $hasDevRole): array
    {
        $supplier = [
            'icon' => '',
            'pageName' => 'side-menu-supplier-supplier',
            'title' => 'components.menu.supplier-supplier',
        ];

        $root_array = [
            'icon' => 'TruckIcon',
            'pageName' => 'side-menu-supplier',
            'title' => 'components.menu.supplier',
            'subMenu' => [
            ],
        ];

        array_push($root_array['subMenu'], $supplier);

        if ($hasCompany || $hasDevRole) {
            array_push($menu, $root_array);
        }

        return $menu;
    }

    private function createMenu_Customer(array $menu, bool $hasCompany, bool $hasDevRole): array
    {
        return $menu;
    }

    private function createMenu_PurchaseOrder(array $menu, bool $hasCompany, bool $hasDevRole): array
    {
        $po = [
            'icon' => '',
            'pageName' => 'side-menu-purchase_order-purchaseorder',
            'title' => 'components.menu.purchase_order-purchaseorder',
        ];

        $root_array = [
            'icon' => 'FilePlusIcon',
            'pageName' => 'side-menu-purchase_order',
            'title' => 'components.menu.purchase_order',
            'subMenu' => [
            ],
        ];

        array_push($root_array['subMenu'], $po);

        if ($hasCompany || $hasDevRole) {
            array_push($menu, $root_array);
        }

        return $menu;
    }

    private function createMenu_SalesOrder(array $menu, bool $hasCompany, bool $hasDevRole): array
    {
        return $menu;
    }

    private function createMenu_Administrator(array $menu, bool $hasAdminRole, bool $hasDevRole): array
    {
        $user = [
            'icon' => '',
            'pageName' => 'side-menu-administrator-user',
            'title' => 'components.menu.administrator-user',
        ];

        $root_array = [
            'icon' => 'CpuIcon',
            'pageName' => 'side-menu-administrator',
            'title' => 'components.menu.administrator',
            'subMenu' => [
            ],
        ];

        array_push($root_array['subMenu'], $user);

        if ($hasAdminRole || $hasDevRole) {
            array_push($menu, $root_array);
        }

        return $menu;
    }

    private function createMenu_DevTool(array $menu, bool $hasDevRole): array
    {
        $dbbackup = [
            'icon' => '',
            'pageName' => 'side-menu-devtool-backup',
            'title' => 'components.menu.devtool-dbbackup',
        ];

        $playground = [
            'icon' => '',
            'pageName' => 'side-menu-devtool-example',
            'title' => 'components.menu.devtool-playground',
            'subMenu' => [
            ],
        ];

        $playground_ex1 = [
            'icon' => '',
            'pageName' => 'side-menu-devtool-example-ex1',
            'title' => 'components.menu.devtool-playground-ex1',
        ];

        $playground_ex2 = [
            'icon' => '',
            'pageName' => 'side-menu-devtool-example-ex2',
            'title' => 'components.menu.devtool-playground-ex2',
        ];

        array_push($playground['subMenu'], $playground_ex1);
        array_push($playground['subMenu'], $playground_ex2);

        $root_array = [
            'icon' => 'GithubIcon',
            'pageName' => 'side-menu-devtool',
            'title' => 'components.menu.devtool',
            'subMenu' => [
            ],
        ];

        array_push($root_array['subMenu'], $dbbackup);
        array_push($root_array['subMenu'], $playground);

        if ($hasDevRole) {
            array_push($menu, $root_array);
        }

        return $menu;
    }
}