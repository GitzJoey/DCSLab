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
        $menu = $this->createMenu_Transaction($menu, $hasOnlyUserRole, $hasOnlyAdminRole);
        $menu = $this->createMenu_Report($menu, $hasOnlyUserRole, $hasOnlyAdminRole);

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

        $supplier = [
            'icon' => 'Users',
            'pageName' => 'side-menu-supplier',
            'title' => 'components.menu.supplier',
        ];

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

        $stockAdjustmentCategory = [
            'icon' => 'Tags',
            'pageName' => 'side-menu-stock-adjustment-category',
            'title' => 'components.menu.stock-adjustment-category',
        ];

        array_push($root_array['subMenu'], $companyManagement, $financeManagement, $productManagement, $supplier, $customerManagement, $stockAdjustmentCategory);
        array_push($menu, $root_array);

        return $menu;
    }

    private function createMenu_Transaction(array $menu, bool $hasOnlyUserRole, bool $hasOnlyAdminRole): array
    {
        if ($hasOnlyUserRole || $hasOnlyAdminRole) {
            return $menu;
        }

        $root_array = [
            'icon' => 'ArrowLeftRight',
            'pageName' => 'side-menu-transaction',
            'title' => 'components.menu.transaction',
            'subMenu' => [],
        ];

        $stockAdjustment = [
            'icon' => 'SlidersHorizontal',
            'pageName' => 'side-menu-stock-adjustment',
            'title' => 'components.menu.stock-adjustment',
        ];

        $capitalOpening = [
            'icon' => 'Wallet',
            'pageName' => 'side-menu-finance-capital-opening',
            'title' => 'components.menu.capital-opening',
        ];

        $capitalTransaction = [
            'icon' => 'ArrowLeftRight',
            'pageName' => 'side-menu-finance-capital-transaction',
            'title' => 'components.menu.capital-transaction',
        ];

        $stockTransfer = [
            'icon' => 'Truck',
            'pageName' => 'side-menu-stock-transfer',
            'title' => 'components.menu.stock-transfer',
        ];

        array_push($root_array['subMenu'], $capitalOpening, $capitalTransaction, $stockAdjustment, $stockTransfer);

        array_push($menu, $root_array);

        return $menu;
    }

    private function createMenu_Report(array $menu, bool $hasOnlyUserRole, bool $hasOnlyAdminRole): array
    {
        if ($hasOnlyUserRole || $hasOnlyAdminRole) {
            return $menu;
        }

        $root_array = [
            'icon' => 'BarChart3',
            'pageName' => 'side-menu-report',
            'title' => 'components.menu.report',
            'subMenu' => [],
        ];

        $cashAccount = [
            'icon' => 'Wallet',
            'pageName' => 'side-menu-report-cash-account',
            'title' => 'components.menu.cash-account',
            'subMenu' => [],
        ];

        $cashAccountWithRemainingBalance = [
            'icon' => 'WalletCards',
            'pageName' => 'side-menu-cash-account-with-remaining-balance',
            'title' => 'components.menu.cash-account-with-remaining-balance',
        ];

        array_push($cashAccount['subMenu'], $cashAccountWithRemainingBalance);

        $product = [
            'icon' => 'Package',
            'pageName' => 'side-menu-report-product',
            'title' => 'components.menu.product',
            'subMenu' => [],
        ];

        $productWithRemainingStock = [
            'icon' => 'Boxes',
            'pageName' => 'side-menu-product-with-remaining-stock',
            'title' => 'components.menu.product-with-remaining-stock',
        ];

        array_push($product['subMenu'], $productWithRemainingStock);

        $stockAdjustment = [
            'icon' => 'SlidersHorizontal',
            'pageName' => 'side-menu-stock-adjustment',
            'title' => 'components.menu.stock-adjustment',
            'subMenu' => [],
        ];

        $stockAdjustmentInProduct = [
            'icon' => 'PackagePlus',
            'pageName' => 'side-menu-stock-adjustment-in-product',
            'title' => 'components.menu.stock-adjustment-in-product',
        ];

        $stockAdjustmentInProductSerial = [
            'icon' => 'ScanLine',
            'pageName' => 'side-menu-stock-adjustment-in-product-serial',
            'title' => 'components.menu.stock-adjustment-in-product-serial',
        ];

        $stockAdjustmentOutProduct = [
            'icon' => 'PackageMinus',
            'pageName' => 'side-menu-stock-adjustment-out-product',
            'title' => 'components.menu.stock-adjustment-out-product',
        ];

        $stockAdjustmentOutProductSerial = [
            'icon' => 'ScanLine',
            'pageName' => 'side-menu-stock-adjustment-out-product-serial',
            'title' => 'components.menu.stock-adjustment-out-product-serial',
        ];

        $stockTransfer = [
            'icon' => 'Truck',
            'pageName' => 'side-menu-report-stock-transfer',
            'title' => 'components.menu.stock-transfer',
            'subMenu' => [],
        ];

        $stockTransferProductUnit = [
            'icon' => 'Boxes',
            'pageName' => 'side-menu-stock-transfer-product-unit',
            'title' => 'components.menu.stock-transfer-product-unit',
        ];

        $stockTransferProductUnitSerial = [
            'icon' => 'ScanLine',
            'pageName' => 'side-menu-stock-transfer-product-unit-serial',
            'title' => 'components.menu.stock-transfer-product-unit-serial',
        ];

        array_push(
            $stockAdjustment['subMenu'],
            $stockAdjustmentInProduct,
            $stockAdjustmentInProductSerial,
            $stockAdjustmentOutProduct,
            $stockAdjustmentOutProductSerial
        );

        array_push(
            $stockTransfer['subMenu'],
            $stockTransferProductUnit,
            $stockTransferProductUnitSerial
        );

        array_push($root_array['subMenu'], $cashAccount, $product, $stockAdjustment, $stockTransfer);

        array_push($menu, $root_array);

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
