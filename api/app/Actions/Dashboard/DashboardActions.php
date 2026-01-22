<?php

namespace App\Actions\Dashboard;

use App\Enums\UserRolesEnum;
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

        $hasUserRole = $usrRoles->where('name', UserRolesEnum::USER->value)->isNotEmpty() ? true : false;
        $hasOnlyUserRole = $usrRoles->where('name', UserRolesEnum::USER->value)->isNotEmpty() && $usrRoles->count() == 1 ? true : false;

        $hasAdminRole = $usrRoles->where('name', UserRolesEnum::ADMINISTRATOR->value)->isNotEmpty() ? true : false;
        $hasOnlyAdminRole = $usrRoles->where('name', UserRolesEnum::ADMINISTRATOR->value)->isNotEmpty() && $usrRoles->count() == 1 ? true : false;

        $hasDevRole = $usrRoles->where('name', UserRolesEnum::DEVELOPER->value)->isNotEmpty() ? true : false;

        $hasCompany = $usr->companies->count() != 0 ? true : false;

        $showDemoMenu = false;

        $menu = $this->createMenu_Dashboard($menu, $showDemoMenu);
        $menu = $this->createMenu_MasterData($menu, $hasOnlyUserRole, $hasOnlyAdminRole, $hasCompany, $hasDevRole);
        $menu = $this->createMenu_StockAdjustment($menu, $hasOnlyUserRole, $hasOnlyAdminRole);
        $menu = $this->createMenu_Customer($menu, $hasOnlyUserRole, $hasOnlyAdminRole);
        $menu = $this->createMenu_Administrator($menu, $hasAdminRole, $hasDevRole);
        $menu = $this->createMenu_DevTool($menu, $hasDevRole);

        $this->saveToCache($cacheKey, $menu);

        return $menu;
    }

    private function createMenu_Dashboard(array $menu, bool $showDemo): array
    {
        $maindashboard = [
            'icon' => 'Home',
            'pageName' => 'side-menu-dashboard-maindashboard',
            'title' => 'components.menu.dashboard',
        ];

        array_push($menu, $maindashboard);

        if ($showDemo) {
            $demo = [
                'icon' => 'ChevronRight',
                'pageName' => 'side-menu-dashboard-demo',
                'title' => 'components.menu.main-demo',
            ];

            array_push($menu, $demo);
        }

        return $menu;
    }

    private function createMenu_MasterData(array $menu, bool $hasOnlyUserRole, bool $hasOnlyAdminRole, bool $hasCompany, bool $hasDevRole): array
    {
        if ($hasOnlyUserRole || $hasOnlyAdminRole) {
            return $menu;
        }

        $root_array = [
            'icon' => 'Database',
            'pageName' => 'side-menu-master-data',
            'title' => 'components.menu.master-data',
            'subMenu' => [],
        ];

        $companyManagement = [
            'icon' => 'Umbrella',
            'pageName' => 'side-menu-company',
            'title' => 'components.menu.company-management',
            'subMenu' => [],
        ];

        $company = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-company-company',
            'title' => 'components.menu.company',
        ];

        $branches = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-company-branch',
            'title' => 'components.menu.branch',
        ];

        $warehouse = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-company-warehouse',
            'title' => 'components.menu.warehouse',
        ];

        if ($hasCompany || $hasDevRole) {
            array_push($companyManagement['subMenu'], $company, $branches, $warehouse);
        } else {
            array_push($companyManagement['subMenu'], $company);
        }

        $financeManagement = [
            'icon' => 'Wallet',
            'pageName' => 'side-menu-finance',
            'title' => 'components.menu.finance',
            'subMenu' => [],
        ];

        $investor = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-company-investor',
            'title' => 'components.menu.investor',
        ];

        $cashAccount = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-finance-cash-account',
            'title' => 'components.menu.cash-account',
        ];

        array_push($financeManagement['subMenu'], $investor, $cashAccount);

        $productManagement = [
            'icon' => 'Package',
            'pageName' => 'side-menu-product',
            'title' => 'components.menu.product-management',
            'subMenu' => [],
        ];

        $productCategory = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-product-product-category',
            'title' => 'components.menu.product-category',
        ];

        $brand = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-product-brand',
            'title' => 'components.menu.brand',
        ];

        $unit = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-product-unit',
            'title' => 'components.menu.unit',
        ];

        $productService = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-product-product-service',
            'title' => 'components.menu.product-service',
        ];

        $product = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-product-product',
            'title' => 'components.menu.product',
        ];

        array_push($productManagement['subMenu'], $productCategory, $brand, $unit, $product, $productService);

        $customerManagement = [
            'icon' => 'Users',
            'pageName' => 'side-menu-customer',
            'title' => 'components.menu.customer-management',
            'subMenu' => [],
        ];

        $customerGroup = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-customer-group',
            'title' => 'components.menu.customer-group',
        ];

        $customer = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-customer',
            'title' => 'components.menu.customer',
        ];

        array_push($customerManagement['subMenu'], $customerGroup, $customer);

        array_push($root_array['subMenu'], $companyManagement, $financeManagement, $productManagement, $customerManagement);
        array_push($menu, $root_array);

        return $menu;
    }

    private function createMenu_StockAdjustment(array $menu, bool $hasOnlyUserRole, bool $hasOnlyAdminRole): array
    {
        if ($hasOnlyUserRole || $hasOnlyAdminRole) {
            return $menu;
        }

        $stockAdjustmentCategory = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-stock-adjustment-stock-adjustment-category',
            'title' => 'components.menu.stock-adjustment-category',
        ];

        $root_array = [
            'icon' => 'RefreshCw',
            'pageName' => 'side-menu-stock-adjustment',
            'title' => 'components.menu.stock-adjustment',
            'subMenu' => [],
        ];

        array_push($root_array['subMenu'], $stockAdjustmentCategory);
        array_push($menu, $root_array);

        return $menu;
    }

    private function createMenu_Customer(array $menu, bool $hasOnlyUserRole, bool $hasOnlyAdminRole): array
    {
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
