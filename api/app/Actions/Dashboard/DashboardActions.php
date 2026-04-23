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

        $vatProfile = [
            'icon' => 'ChevronRight',
            'pageName' => 'side-menu-product-vat-profile',
            'title' => 'components.menu.vat-profile',
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

        array_push($productManagement['subMenu'], $productCategory, $brand, $unit, $vatProfile, $product, $productService);

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

        $expenseCategory = [
            'icon' => 'Tags',
            'pageName' => 'side-menu-expense-category',
            'title' => 'components.menu.expense-category',
        ];

        $purchaseAdditionalCostCategory = [
            'icon' => 'Tags',
            'pageName' => 'side-menu-purchase-additional-cost-category',
            'title' => 'components.menu.purchase-additional-cost-category',
        ];

        array_push(
            $root_array['subMenu'],
            $companyManagement,
            $financeManagement,
            $productManagement,
            $supplier,
            $customerManagement,
            $stockAdjustmentCategory,
            $expenseCategory,
            $purchaseAdditionalCostCategory
        );
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

        $purchaseOrder = [
            'icon' => 'FileSpreadsheet',
            'pageName' => 'side-menu-purchase-order',
            'title' => 'components.menu.purchase-order',
        ];

        $purchaseAdditionalCost = [
            'icon' => 'Wallet',
            'pageName' => 'side-menu-purchase-additional-cost',
            'title' => 'components.menu.purchase-additional-cost',
        ];

        $purchaseAdditionalCostPayment = [
            'icon' => 'WalletCards',
            'pageName' => 'side-menu-purchase-additional-cost-payment',
            'title' => 'components.menu.purchase-additional-cost-payment',
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

        $cashTransfer = [
            'icon' => 'Repeat',
            'pageName' => 'side-menu-finance-cash-transfer',
            'title' => 'components.menu.cash-transfer',
        ];

        $expense = [
            'icon' => 'WalletCards',
            'pageName' => 'side-menu-expense',
            'title' => 'components.menu.expense',
        ];

        $prepaidExpense = [
            'icon' => 'WalletCards',
            'pageName' => 'side-menu-prepaid-expense',
            'title' => 'components.menu.prepaid-expense',
        ];

        $stockTransfer = [
            'icon' => 'Truck',
            'pageName' => 'side-menu-stock-transfer',
            'title' => 'components.menu.stock-transfer',
        ];

        array_push(
            $root_array['subMenu'],
            $capitalOpening,
            $capitalTransaction,
            $cashTransfer,
            $expense,
            $prepaidExpense,
            $stockAdjustment,
            $purchaseOrder,
            $purchaseAdditionalCost,
            $purchaseAdditionalCostPayment,
            $stockTransfer
        );

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

        $stockAdjustmentInItem = [
            'icon' => 'PackagePlus',
            'pageName' => 'side-menu-stock-adjustment-in-item',
            'title' => 'components.menu.stock-adjustment-in-item',
        ];

        $stockAdjustmentInItemSerial = [
            'icon' => 'ScanLine',
            'pageName' => 'side-menu-stock-adjustment-in-item-serial',
            'title' => 'components.menu.stock-adjustment-in-item-serial',
        ];

        $stockAdjustmentOutItem = [
            'icon' => 'PackageMinus',
            'pageName' => 'side-menu-stock-adjustment-out-item',
            'title' => 'components.menu.stock-adjustment-out-item',
        ];

        $stockAdjustmentOutItemSerial = [
            'icon' => 'ScanLine',
            'pageName' => 'side-menu-stock-adjustment-out-item-serial',
            'title' => 'components.menu.stock-adjustment-out-item-serial',
        ];

        $stockTransfer = [
            'icon' => 'Truck',
            'pageName' => 'side-menu-report-stock-transfer',
            'title' => 'components.menu.stock-transfer',
            'subMenu' => [],
        ];

        $purchaseOrder = [
            'icon' => 'FileSpreadsheet',
            'pageName' => 'side-menu-report-purchase-order',
            'title' => 'components.menu.purchase-order',
            'subMenu' => [],
        ];

        $purchaseOrderItem = [
            'icon' => 'PackageSearch',
            'pageName' => 'side-menu-purchase-order-item',
            'title' => 'components.menu.purchase-order-item',
        ];

        $purchaseOrderDownPayment = [
            'icon' => 'WalletCards',
            'pageName' => 'side-menu-purchase-order-down-payment',
            'title' => 'components.menu.purchase-order-down-payment',
        ];

        $purchaseOrderDownPaymentNotFullyAllocated = [
            'icon' => 'WalletCards',
            'pageName' => 'side-menu-purchase-order-down-payment-not-fully-allocated',
            'title' => 'components.menu.purchase-order-down-payment-not-fully-allocated',
        ];

        $purchaseOrderDownPaymentRefund = [
            'icon' => 'Undo2',
            'pageName' => 'side-menu-purchase-order-down-payment-refund',
            'title' => 'components.menu.purchase-order-down-payment-refund',
        ];

        $stockTransferItem = [
            'icon' => 'Boxes',
            'pageName' => 'side-menu-stock-transfer-item',
            'title' => 'components.menu.stock-transfer-item',
        ];

        $stockTransferItemSerial = [
            'icon' => 'ScanLine',
            'pageName' => 'side-menu-stock-transfer-item-serial',
            'title' => 'components.menu.stock-transfer-item-serial',
        ];

        array_push(
            $stockAdjustment['subMenu'],
            $stockAdjustmentInItem,
            $stockAdjustmentInItemSerial,
            $stockAdjustmentOutItem,
            $stockAdjustmentOutItemSerial
        );

        array_push(
            $stockTransfer['subMenu'],
            $stockTransferItem,
            $stockTransferItemSerial
        );

        array_push(
            $purchaseOrder['subMenu'],
            $purchaseOrderItem,
            $purchaseOrderDownPayment,
            $purchaseOrderDownPaymentNotFullyAllocated,
            $purchaseOrderDownPaymentRefund
        );

        array_push($root_array['subMenu'], $cashAccount, $product, $stockAdjustment, $purchaseOrder, $stockTransfer);

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
