import Layout from '@/themes';

import LoginPage from '../pages/auth/LoginPage.vue';
import RegisterPage from '../pages/auth/RegisterPage.vue';
import ForgotPasswordPage from '../pages/auth/ForgotPasswordPage.vue';
import ResetPasswordPage from '../pages/auth/ResetPasswordPage.vue';
import MainDashboard from '../pages/dashboard/MainDashboard.vue';
import ProfileView from '../pages/dashboard/ProfileView.vue';
import DevTool from '../pages/dev/DevTool.vue';
import PlayOne from '../pages/dev/PlayOne.vue';
import PlayTwo from '../pages/dev/PlayTwo.vue';
import ErrorView from '../pages/error/ErrorView.vue';
import ErrorPage from '../pages/error/ErrorPage.vue';
import UserIndex from '../pages/administrator/UserIndex.vue';
import UserList from '../pages/administrator/UserList.vue';
import UserCreate from '../pages/administrator/UserCreate.vue';
import UserEdit from '../pages/administrator/UserEdit.vue';

import CompanyIndex from '../pages/company/CompanyIndex.vue';
import CompanyList from '../pages/company/CompanyList.vue';
import CompanyCreate from '../pages/company/CompanyCreate.vue';
import CompanyEdit from '../pages/company/CompanyEdit.vue';
import BranchIndex from '../pages/branch/BranchIndex.vue';
import BranchList from '../pages/branch/BranchList.vue';
import BranchCreate from '../pages/branch/BranchCreate.vue';
import BranchEdit from '../pages/branch/BranchEdit.vue';
import WarehouseIndex from '@/pages/warehouse/WarehouseIndex.vue';
import WarehouseList from '@/pages/warehouse/WarehouseList.vue';
import WarehouseCreate from '@/pages/warehouse/WarehouseCreate.vue';
import WarehouseEdit from '@/pages/warehouse/WarehouseEdit.vue';
import InvestorIndex from '@/pages/investor/InvestorIndex.vue';
import InvestorList from '@/pages/investor/InvestorList.vue';
import InvestorCreate from '@/pages/investor/InvestorCreate.vue';
import InvestorEdit from '@/pages/investor/InvestorEdit.vue';
import CashAccountIndex from '@/pages/cash-account/CashAccountIndex.vue';
import CashAccountList from '@/pages/cash-account/CashAccountList.vue';
import CashAccountCreate from '@/pages/cash-account/CashAccountCreate.vue';
import CashAccountEdit from '@/pages/cash-account/CashAccountEdit.vue';
import CashAccountWithRemainingBalanceIndex from '@/pages/cash-account/CashAccountWithRemainingBalanceIndex.vue';
import CashAccountWithRemainingBalanceList from '@/pages/cash-account/CashAccountWithRemainingBalanceList.vue';
import CapitalOpeningIndex from '@/pages/capital-opening/CapitalOpeningIndex.vue';
import CapitalOpeningList from '@/pages/capital-opening/CapitalOpeningList.vue';
import CapitalOpeningCreate from '@/pages/capital-opening/CapitalOpeningCreate.vue';
import CapitalOpeningEdit from '@/pages/capital-opening/CapitalOpeningEdit.vue';
import CapitalTransactionIndex from '@/pages/capital-transaction/CapitalTransactionIndex.vue';
import CapitalTransactionList from '@/pages/capital-transaction/CapitalTransactionList.vue';
import CapitalTransactionCreate from '@/pages/capital-transaction/CapitalTransactionCreate.vue';
import CapitalTransactionEdit from '@/pages/capital-transaction/CapitalTransactionEdit.vue';
import CashTransferIndex from '@/pages/cash-transfer/CashTransferIndex.vue';
import CashTransferList from '@/pages/cash-transfer/CashTransferList.vue';
import CashTransferCreate from '@/pages/cash-transfer/CashTransferCreate.vue';
import CashTransferEdit from '@/pages/cash-transfer/CashTransferEdit.vue';
import ExpenseIndex from '@/pages/expense/ExpenseIndex.vue';
import ExpenseList from '@/pages/expense/ExpenseList.vue';
import ExpenseCreate from '@/pages/expense/ExpenseCreate.vue';
import ExpenseEdit from '@/pages/expense/ExpenseEdit.vue';
import DebtIndex from '@/pages/debt/DebtIndex.vue';
import DebtList from '@/pages/debt/DebtList.vue';
import DebtCreate from '@/pages/debt/DebtCreate.vue';
import DebtEdit from '@/pages/debt/DebtEdit.vue';
import PrepaidExpenseIndex from '@/pages/prepaid-expense/PrepaidExpenseIndex.vue';
import PrepaidExpenseList from '@/pages/prepaid-expense/PrepaidExpenseList.vue';
import PrepaidExpenseCreate from '@/pages/prepaid-expense/PrepaidExpenseCreate.vue';
import PrepaidExpenseEdit from '@/pages/prepaid-expense/PrepaidExpenseEdit.vue';
import ExpensePaymentIndex from '@/pages/expense-payment/ExpensePaymentIndex.vue';
import ExpensePaymentList from '@/pages/expense-payment/ExpensePaymentList.vue';
import ExpensePaymentCreate from '@/pages/expense-payment/ExpensePaymentCreate.vue';
import ExpensePaymentEdit from '@/pages/expense-payment/ExpensePaymentEdit.vue';
import IncomePaymentIndex from '@/pages/income-payment/IncomePaymentIndex.vue';
import IncomePaymentList from '@/pages/income-payment/IncomePaymentList.vue';
import IncomePaymentCreate from '@/pages/income-payment/IncomePaymentCreate.vue';
import IncomePaymentEdit from '@/pages/income-payment/IncomePaymentEdit.vue';
import ExpenseCategoryIndex from '@/pages/expense-category/ExpenseCategoryIndex.vue';
import ExpenseCategoryList from '@/pages/expense-category/ExpenseCategoryList.vue';
import ExpenseCategoryCreate from '@/pages/expense-category/ExpenseCategoryCreate.vue';
import ExpenseCategoryEdit from '@/pages/expense-category/ExpenseCategoryEdit.vue';
import DebtCategoryIndex from '@/pages/debt-category/DebtCategoryIndex.vue';
import DebtCategoryList from '@/pages/debt-category/DebtCategoryList.vue';
import DebtCategoryCreate from '@/pages/debt-category/DebtCategoryCreate.vue';
import DebtCategoryEdit from '@/pages/debt-category/DebtCategoryEdit.vue';
import DebtCreditorIndex from '@/pages/debt-creditor/DebtCreditorIndex.vue';
import DebtCreditorList from '@/pages/debt-creditor/DebtCreditorList.vue';
import DebtCreditorCreate from '@/pages/debt-creditor/DebtCreditorCreate.vue';
import DebtCreditorEdit from '@/pages/debt-creditor/DebtCreditorEdit.vue';
import IncomeCategoryIndex from '@/pages/income-category/IncomeCategoryIndex.vue';
import IncomeCategoryList from '@/pages/income-category/IncomeCategoryList.vue';
import IncomeCategoryCreate from '@/pages/income-category/IncomeCategoryCreate.vue';
import IncomeCategoryEdit from '@/pages/income-category/IncomeCategoryEdit.vue';
import IncomeIndex from '@/pages/income/IncomeIndex.vue';
import IncomeList from '@/pages/income/IncomeList.vue';
import IncomeCreate from '@/pages/income/IncomeCreate.vue';
import IncomeEdit from '@/pages/income/IncomeEdit.vue';
import PrepaidIncomeIndex from '@/pages/prepaid-income/PrepaidIncomeIndex.vue';
import PrepaidIncomeList from '@/pages/prepaid-income/PrepaidIncomeList.vue';
import PrepaidIncomeCreate from '@/pages/prepaid-income/PrepaidIncomeCreate.vue';
import PrepaidIncomeEdit from '@/pages/prepaid-income/PrepaidIncomeEdit.vue';
import ProductCategoryIndex from '@/pages/product-category/ProductCategoryIndex.vue';
import ProductCategoryList from '@/pages/product-category/ProductCategoryList.vue';
import ProductCategoryCreate from '@/pages/product-category/ProductCategoryCreate.vue';
import ProductCategoryEdit from '@/pages/product-category/ProductCategoryEdit.vue';
import BrandIndex from '@/pages/brand/BrandIndex.vue';
import BrandList from '@/pages/brand/BrandList.vue';
import BrandCreate from '@/pages/brand/BrandCreate.vue';
import BrandEdit from '@/pages/brand/BrandEdit.vue';
import UnitIndex from '@/pages/unit/UnitIndex.vue';
import UnitList from '@/pages/unit/UnitList.vue';
import UnitCreate from '@/pages/unit/UnitCreate.vue';
import UnitEdit from '@/pages/unit/UnitEdit.vue';
import VatProfileIndex from '@/pages/vat-profile/VatProfileIndex.vue';
import VatProfileList from '@/pages/vat-profile/VatProfileList.vue';
import VatProfileCreate from '@/pages/vat-profile/VatProfileCreate.vue';
import VatProfileEdit from '@/pages/vat-profile/VatProfileEdit.vue';
import ProductIndex from '@/pages/product/ProductIndex.vue';
import ProductList from '@/pages/product/ProductList.vue';
import ProductCreate from '@/pages/product/ProductCreate.vue';
import ProductEdit from '@/pages/product/ProductEdit.vue';
import ProductServiceIndex from '@/pages/product-service/ProductServiceIndex.vue';
import ProductServiceList from '@/pages/product-service/ProductServiceList.vue';
import ProductServiceCreate from '@/pages/product-service/ProductServiceCreate.vue';
import ProductServiceEdit from '@/pages/product-service/ProductServiceEdit.vue';
import SupplierIndex from '@/pages/supplier/SupplierIndex.vue';
import SupplierList from '@/pages/supplier/SupplierList.vue';
import SupplierCreate from '@/pages/supplier/SupplierCreate.vue';
import SupplierEdit from '@/pages/supplier/SupplierEdit.vue';
import CustomerGroupIndex from '@/pages/customer-group/CustomerGroupIndex.vue';
import CustomerGroupList from '@/pages/customer-group/CustomerGroupList.vue';
import CustomerGroupCreate from '@/pages/customer-group/CustomerGroupCreate.vue';
import CustomerGroupEdit from '@/pages/customer-group/CustomerGroupEdit.vue';
import CustomerIndex from '@/pages/customer/CustomerIndex.vue';
import CustomerList from '@/pages/customer/CustomerList.vue';
import CustomerCreate from '@/pages/customer/CustomerCreate.vue';
import CustomerEdit from '@/pages/customer/CustomerEdit.vue';
import StockAdjustmentCategoryIndex from '@/pages/stock-adjustment-category/StockAdjustmentCategoryIndex.vue';
import StockAdjustmentCategoryList from '@/pages/stock-adjustment-category/StockAdjustmentCategoryList.vue';
import StockAdjustmentCategoryCreate from '@/pages/stock-adjustment-category/StockAdjustmentCategoryCreate.vue';
import StockAdjustmentCategoryEdit from '@/pages/stock-adjustment-category/StockAdjustmentCategoryEdit.vue';
import PurchaseAdditionalCostCategoryIndex from '@/pages/purchase-additional-cost-category/PurchaseAdditionalCostCategoryIndex.vue';
import PurchaseAdditionalCostCategoryList from '@/pages/purchase-additional-cost-category/PurchaseAdditionalCostCategoryList.vue';
import PurchaseAdditionalCostCategoryCreate from '@/pages/purchase-additional-cost-category/PurchaseAdditionalCostCategoryCreate.vue';
import PurchaseAdditionalCostCategoryEdit from '@/pages/purchase-additional-cost-category/PurchaseAdditionalCostCategoryEdit.vue';
import PurchaseAdditionalCostIndex from '@/pages/purchase-additional-cost/PurchaseAdditionalCostIndex.vue';
import PurchaseAdditionalCostList from '@/pages/purchase-additional-cost/PurchaseAdditionalCostList.vue';
import PurchaseAdditionalCostCreate from '@/pages/purchase-additional-cost/PurchaseAdditionalCostCreate.vue';
import PurchaseAdditionalCostEdit from '@/pages/purchase-additional-cost/PurchaseAdditionalCostEdit.vue';
import PurchaseAdditionalCostPaymentIndex from '@/pages/purchase-additional-cost-payment/PurchaseAdditionalCostPaymentIndex.vue';
import PurchaseAdditionalCostPaymentList from '@/pages/purchase-additional-cost-payment/PurchaseAdditionalCostPaymentList.vue';
import PurchaseAdditionalCostPaymentCreate from '@/pages/purchase-additional-cost-payment/PurchaseAdditionalCostPaymentCreate.vue';
import PurchaseAdditionalCostPaymentEdit from '@/pages/purchase-additional-cost-payment/PurchaseAdditionalCostPaymentEdit.vue';
import StockAdjustmentIndex from '@/pages/stock-adjustment/StockAdjustmentIndex.vue';
import StockAdjustmentList from '@/pages/stock-adjustment/StockAdjustmentList.vue';
import StockAdjustmentCreate from '@/pages/stock-adjustment/StockAdjustmentCreate.vue';
import StockAdjustmentEdit from '@/pages/stock-adjustment/StockAdjustmentEdit.vue';
import StockAdjustmentInItemIndex from '@/pages/stock-adjustment/StockAdjustmentInItemIndex.vue';
import StockAdjustmentInItemList from '@/pages/stock-adjustment/StockAdjustmentInItemList.vue';
import StockAdjustmentOutItemIndex from '@/pages/stock-adjustment/StockAdjustmentOutItemIndex.vue';
import StockAdjustmentOutItemList from '@/pages/stock-adjustment/StockAdjustmentOutItemList.vue';
import StockAdjustmentInItemSerialIndex from '@/pages/stock-adjustment/StockAdjustmentInItemSerialIndex.vue';
import StockAdjustmentInItemSerialList from '@/pages/stock-adjustment/StockAdjustmentInItemSerialList.vue';
import StockAdjustmentOutItemSerialIndex from '@/pages/stock-adjustment/StockAdjustmentOutItemSerialIndex.vue';
import StockAdjustmentOutItemSerialList from '@/pages/stock-adjustment/StockAdjustmentOutItemSerialList.vue';
import PurchaseOrderIndex from '@/pages/purchase-order/PurchaseOrderIndex.vue';
import PurchaseOrderList from '@/pages/purchase-order/PurchaseOrderList.vue';
import PurchaseOrderCreate from '@/pages/purchase-order/PurchaseOrderCreate.vue';
import PurchaseOrderEdit from '@/pages/purchase-order/PurchaseOrderEdit.vue';
import PurchaseOrderItemIndex from '@/pages/purchase-order/PurchaseOrderItemIndex.vue';
import PurchaseOrderItemList from '@/pages/purchase-order/PurchaseOrderItemList.vue';
import PurchaseOrderDownPaymentIndex from '@/pages/purchase-order/PurchaseOrderDownPaymentIndex.vue';
import PurchaseOrderDownPaymentList from '@/pages/purchase-order/PurchaseOrderDownPaymentList.vue';
import PurchaseOrderDownPaymentNotFullyAllocatedIndex from '@/pages/purchase-order/PurchaseOrderDownPaymentNotFullyAllocatedIndex.vue';
import PurchaseOrderDownPaymentRefundIndex from '@/pages/purchase-order/PurchaseOrderDownPaymentRefundIndex.vue';
import PurchaseOrderDownPaymentRefundList from '@/pages/purchase-order/PurchaseOrderDownPaymentRefundList.vue';
import StockTransferIndex from '@/pages/stock-transfer/StockTransferIndex.vue';
import StockTransferList from '@/pages/stock-transfer/StockTransferList.vue';
import StockTransferCreate from '@/pages/stock-transfer/StockTransferCreate.vue';
import StockTransferEdit from '@/pages/stock-transfer/StockTransferEdit.vue';
import StockTransferItemList from '@/pages/stock-transfer/StockTransferItemList.vue';
import StockTransferItemSerialList from '@/pages/stock-transfer/StockTransferItemSerialList.vue';
import ProductWithRemainingStockIndex from '@/pages/product/ProductWithRemainingStockIndex.vue';
import ProductWithRemainingStockList from '@/pages/product/ProductWithRemainingStockList.vue';


export default [
  // login
  {
    path: '/',
    redirect: '/auth/login',
  },
  // home
  {
    path: '/home',
    redirect: '/dashboard/main',
  },
  // auth
  {
    path: '/auth',
    children: [
      {
        path: '/auth/login',
        name: 'login',
        component: LoginPage,
      },
      {
        path: '/auth/register',
        name: 'register',
        component: RegisterPage,
      },
      {
        path: '/auth/forgot-password',
        name: 'forgot-password',
        component: ForgotPasswordPage,
      },
      {
        path: '/auth/reset-password',
        name: 'reset-password',
        component: ResetPasswordPage,
      },
    ],
  },
  // menu
  {
    path: '/dashboard',
    component: Layout,
    children: [
      // dashboard
      {
        path: '/dashboard/main',
        name: 'side-menu-dashboard-maindashboard',
        component: MainDashboard,
        meta: {
          remember: true,
        },
      },
      // profile
      {
        path: '/dashboard/profile',
        name: 'side-menu-dashboard-profile',
        component: ProfileView,
        meta: {
          remember: true,
        },
      },

      // Company Management
      {
        path: '/dashboard/company',
        children: [
          // Company
          {
            path: '/dashboard/company/company',
            name: 'side-menu-company-company',
            redirect: '/dashboard/company/company/list',
            component: CompanyIndex,
            children: [
              {
                path: '/dashboard/company/company/list',
                name: 'side-menu-company-company-list',
                component: CompanyList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/company/company/create',
                name: 'side-menu-company-company-create',
                component: CompanyCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/company/company/edit/:ulid',
                name: 'side-menu-company-company-edit',
                component: CompanyEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Branch
          {
            path: '/dashboard/company/branch',
            name: 'side-menu-company-branch',
            redirect: '/dashboard/company/branch/list',
            component: BranchIndex,
            children: [
              {
                path: '/dashboard/company/branch/list',
                name: 'side-menu-company-branch-list',
                component: BranchList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/company/branch/create',
                name: 'side-menu-company-branch-create',
                component: BranchCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/company/branch/edit/:ulid',
                name: 'side-menu-company-branch-edit',
                component: BranchEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Warehouse
          {
            path: '/dashboard/company/warehouse',
            name: 'side-menu-company-warehouse',
            redirect: '/dashboard/company/warehouse/list',
            component: WarehouseIndex,
            children: [
              {
                path: '/dashboard/company/warehouse/list',
                name: 'side-menu-company-warehouse-list',
                component: WarehouseList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/company/warehouse/create',
                name: 'side-menu-company-warehouse-create',
                component: WarehouseCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/company/warehouse/edit/:ulid',
                name: 'side-menu-company-warehouse-edit',
                component: WarehouseEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
        ],
      },
      // Finance Management
      {
        path: '/dashboard/finance',
        children: [
          // Investor
          {
            path: '/dashboard/finance/investor',
            name: 'side-menu-company-investor',
            redirect: '/dashboard/finance/investor/list',
            component: InvestorIndex,
            children: [
              {
                path: '/dashboard/finance/investor/list',
                name: 'side-menu-company-investor-list',
                component: InvestorList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/finance/investor/create',
                name: 'side-menu-company-investor-create',
                component: InvestorCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/finance/investor/edit/:ulid',
                name: 'side-menu-company-investor-edit',
                component: InvestorEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Cash Account
          {
            path: '/dashboard/finance/cash-account',
            name: 'side-menu-finance-cash-account',
            redirect: '/dashboard/finance/cash-account/list',
            component: CashAccountIndex,
            children: [
              {
                path: '/dashboard/finance/cash-account/list',
                name: 'side-menu-finance-cash-account-list',
                component: CashAccountList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/finance/cash-account/create',
                name: 'side-menu-finance-cash-account-create',
                component: CashAccountCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/finance/cash-account/edit/:ulid',
                name: 'side-menu-finance-cash-account-edit',
                component: CashAccountEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
        ],
      },
      // Product Management
      {
        path: '/dashboard/product',
        children: [
          {
            path: '/dashboard/product/product-category',
            name: 'side-menu-product-product-category',
            redirect: '/dashboard/product/product-category/list',
            component: ProductCategoryIndex,
            children: [
              {
                path: '/dashboard/product/product-category/list',
                name: 'side-menu-product-product-category-list',
                component: ProductCategoryList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/product/product-category/create',
                name: 'side-menu-product-product-category-create',
                component: ProductCategoryCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/product/product-category/edit/:ulid',
                name: 'side-menu-product-product-category-edit',
                component: ProductCategoryEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/dashboard/product/brand',
            name: 'side-menu-product-brand',
            redirect: '/dashboard/product/brand/list',
            component: BrandIndex,
            children: [
              {
                path: '/dashboard/product/brand/list',
                name: 'side-menu-product-brand-list',
                component: BrandList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/product/brand/create',
                name: 'side-menu-product-brand-create',
                component: BrandCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/product/brand/edit/:ulid',
                name: 'side-menu-product-brand-edit',
                component: BrandEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/dashboard/product/unit',
            name: 'side-menu-product-unit',
            redirect: '/dashboard/product/unit/list',
            component: UnitIndex,
            children: [
              {
                path: '/dashboard/product/unit/list',
                name: 'side-menu-product-unit-list',
                component: UnitList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/product/unit/create',
                name: 'side-menu-product-unit-create',
                component: UnitCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/product/unit/edit/:ulid',
                name: 'side-menu-product-unit-edit',
                component: UnitEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/dashboard/product/vat-profile',
            name: 'side-menu-product-vat-profile',
            redirect: '/dashboard/product/vat-profile/list',
            component: VatProfileIndex,
            children: [
              {
                path: '/dashboard/product/vat-profile/list',
                name: 'side-menu-product-vat-profile-list',
                component: VatProfileList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/product/vat-profile/create',
                name: 'side-menu-product-vat-profile-create',
                component: VatProfileCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/product/vat-profile/edit/:ulid',
                name: 'side-menu-product-vat-profile-edit',
                component: VatProfileEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/dashboard/product/product',
            name: 'side-menu-product-product',
            redirect: '/dashboard/product/product/list',
            component: ProductIndex,
            children: [
              {
                path: '/dashboard/product/product/list',
                name: 'side-menu-product-product-list',
                component: ProductList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/product/product/create',
                name: 'side-menu-product-product-create',
                component: ProductCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/product/product/edit/:ulid',
                name: 'side-menu-product-product-edit',
                component: ProductEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/dashboard/product/product-service',
            name: 'side-menu-product-product-service',
            redirect: '/dashboard/product/product-service/list',
            component: ProductServiceIndex,
            children: [
              {
                path: '/dashboard/product/product-service/list',
                name: 'side-menu-product-product-service-list',
                component: ProductServiceList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/product/product-service/create',
                name: 'side-menu-product-product-service-create',
                component: ProductServiceCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/product/product-service/edit/:ulid',
                name: 'side-menu-product-product-service-edit',
                component: ProductServiceEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
        ],
      },
      // Supplier
      {
        path: '/dashboard/supplier',
        children: [
          {
            path: '/dashboard/supplier/supplier',
            name: 'side-menu-supplier',
            redirect: '/dashboard/supplier/supplier/list',
            component: SupplierIndex,
            children: [
              {
                path: '/dashboard/supplier/supplier/list',
                name: 'side-menu-supplier-supplier-list',
                component: SupplierList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/supplier/supplier/create',
                name: 'side-menu-supplier-supplier-create',
                component: SupplierCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/supplier/supplier/edit/:ulid',
                name: 'side-menu-supplier-supplier-edit',
                component: SupplierEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
        ],
      },
      // Customer Management
      {
        path: '/dashboard/customer',
        children: [
          // Customer Group
          {
            path: '/dashboard/customer/customer-group',
            name: 'side-menu-customer-group',
            redirect: '/dashboard/customer/customer-group/list',
            component: CustomerGroupIndex,
            children: [
              {
                path: '/dashboard/customer/customer-group/list',
                name: 'side-menu-customer-group-list',
                component: CustomerGroupList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/customer/customer-group/create',
                name: 'side-menu-customer-group-create',
                component: CustomerGroupCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/customer/customer-group/edit/:ulid',
                name: 'side-menu-customer-group-edit',
                component: CustomerGroupEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Customer
          {
            path: '/dashboard/customer/customer',
            name: 'side-menu-customer',
            redirect: '/dashboard/customer/customer/list',
            component: CustomerIndex,
            children: [
              {
                path: '/dashboard/customer/customer/list',
                name: 'side-menu-customer-list',
                component: CustomerList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/customer/customer/create',
                name: 'side-menu-customer-create',
                component: CustomerCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/customer/customer/edit/:ulid',
                name: 'side-menu-customer-edit',
                component: CustomerEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
        ],
      },
      // Stock Adjustment Category
      {
        path: '/dashboard/stock-adjustment-category',
        children: [
          {
            path: '/dashboard/stock-adjustment-category',
            name: 'side-menu-stock-adjustment-category',
            redirect: '/dashboard/stock-adjustment-category/list',
            component: StockAdjustmentCategoryIndex,
            children: [
              {
                path: '/dashboard/stock-adjustment-category/list',
                name: 'side-menu-stock-adjustment-category-list',
                component: StockAdjustmentCategoryList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/stock-adjustment-category/create',
                name: 'side-menu-stock-adjustment-category-create',
                component: StockAdjustmentCategoryCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/stock-adjustment-category/edit/:ulid',
                name: 'side-menu-stock-adjustment-category-edit',
                component: StockAdjustmentCategoryEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
        ],
      },
      // Purchase Additional Cost Category
      {
        path: '/dashboard/expense-category',
        children: [
          {
            path: '/dashboard/expense-category',
            name: 'side-menu-expense-category',
            redirect: '/dashboard/expense-category/list',
            component: ExpenseCategoryIndex,
            children: [
              {
                path: '/dashboard/expense-category/list',
                name: 'side-menu-expense-category-list',
                component: ExpenseCategoryList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/expense-category/create',
                name: 'side-menu-expense-category-create',
                component: ExpenseCategoryCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/expense-category/edit/:ulid',
                name: 'side-menu-expense-category-edit',
                component: ExpenseCategoryEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
        ],
      },
      {
        path: '/dashboard/debt-category',
        children: [
          {
            path: '/dashboard/debt-category',
            name: 'side-menu-debt-category',
            redirect: '/dashboard/debt-category/list',
            component: DebtCategoryIndex,
            children: [
              {
                path: '/dashboard/debt-category/list',
                name: 'side-menu-debt-category-list',
                component: DebtCategoryList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/debt-category/create',
                name: 'side-menu-debt-category-create',
                component: DebtCategoryCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/debt-category/edit/:ulid',
                name: 'side-menu-debt-category-edit',
                component: DebtCategoryEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
        ],
      },
      {
        path: '/dashboard/debt-creditor',
        children: [
          {
            path: '/dashboard/debt-creditor',
            name: 'side-menu-debt-creditor',
            redirect: '/dashboard/debt-creditor/list',
            component: DebtCreditorIndex,
            children: [
              {
                path: '/dashboard/debt-creditor/list',
                name: 'side-menu-debt-creditor-list',
                component: DebtCreditorList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/debt-creditor/create',
                name: 'side-menu-debt-creditor-create',
                component: DebtCreditorCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/debt-creditor/edit/:ulid',
                name: 'side-menu-debt-creditor-edit',
                component: DebtCreditorEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
        ],
      },
      {
        path: '/dashboard/income-category',
        children: [
          {
            path: '/dashboard/income-category',
            name: 'side-menu-income-category',
            redirect: '/dashboard/income-category/list',
            component: IncomeCategoryIndex,
            children: [
              {
                path: '/dashboard/income-category/list',
                name: 'side-menu-income-category-list',
                component: IncomeCategoryList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/income-category/create',
                name: 'side-menu-income-category-create',
                component: IncomeCategoryCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/income-category/edit/:ulid',
                name: 'side-menu-income-category-edit',
                component: IncomeCategoryEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
        ],
      },
      // Purchase Additional Cost Category
      {
        path: '/dashboard/purchase-additional-cost-category',
        children: [
          {
            path: '/dashboard/purchase-additional-cost-category',
            name: 'side-menu-purchase-additional-cost-category',
            redirect: '/dashboard/purchase-additional-cost-category/list',
            component: PurchaseAdditionalCostCategoryIndex,
            children: [
              {
                path: '/dashboard/purchase-additional-cost-category/list',
                name: 'side-menu-purchase-additional-cost-category-list',
                component: PurchaseAdditionalCostCategoryList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/purchase-additional-cost-category/create',
                name: 'side-menu-purchase-additional-cost-category-create',
                component: PurchaseAdditionalCostCategoryCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/purchase-additional-cost-category/edit/:ulid',
                name: 'side-menu-purchase-additional-cost-category-edit',
                component: PurchaseAdditionalCostCategoryEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
        ],
      },
      // Purchase Additional Cost
      {
        path: '/dashboard/purchase-additional-cost',
        children: [
          {
            path: '/dashboard/purchase-additional-cost',
            name: 'side-menu-purchase-additional-cost',
            redirect: '/dashboard/purchase-additional-cost/list',
            component: PurchaseAdditionalCostIndex,
            children: [
              {
                path: '/dashboard/purchase-additional-cost/list',
                name: 'side-menu-purchase-additional-cost-list',
                component: PurchaseAdditionalCostList,
                meta: { remember: true },
              },
              {
                path: '/dashboard/purchase-additional-cost/create',
                name: 'side-menu-purchase-additional-cost-create',
                component: PurchaseAdditionalCostCreate,
                meta: { remember: true },
              },
              {
                path: '/dashboard/purchase-additional-cost/edit/:ulid',
                name: 'side-menu-purchase-additional-cost-edit',
                component: PurchaseAdditionalCostEdit,
                meta: { remember: true },
              },
            ],
          },
        ],
      },
      // Purchase Additional Cost Payment
      {
        path: '/dashboard/purchase-additional-cost-payment',
        children: [
          {
            path: '/dashboard/purchase-additional-cost-payment',
            name: 'side-menu-purchase-additional-cost-payment',
            redirect: '/dashboard/purchase-additional-cost-payment/list',
            component: PurchaseAdditionalCostPaymentIndex,
            children: [
              {
                path: '/dashboard/purchase-additional-cost-payment/list',
                name: 'side-menu-purchase-additional-cost-payment-list',
                component: PurchaseAdditionalCostPaymentList,
                meta: { remember: true },
              },
              {
                path: '/dashboard/purchase-additional-cost-payment/create',
                name: 'side-menu-purchase-additional-cost-payment-create',
                component: PurchaseAdditionalCostPaymentCreate,
                meta: { remember: true },
              },
              {
                path: '/dashboard/purchase-additional-cost-payment/edit/:ulid',
                name: 'side-menu-purchase-additional-cost-payment-edit',
                component: PurchaseAdditionalCostPaymentEdit,
                meta: { remember: true },
              },
            ],
          },
        ],
      },
      // Transaction
      {
        path: '/dashboard/transaction',
        name: 'side-menu-transaction',
        children: [
          // Stock Adjustment
          {
            path: '/dashboard/stock-adjustment',
            name: 'side-menu-stock-adjustment',
            redirect: '/dashboard/stock-adjustment/list',
            component: StockAdjustmentIndex,
            children: [
              {
                path: '/dashboard/stock-adjustment/list',
                name: 'side-menu-stock-adjustment-list',
                component: StockAdjustmentList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/stock-adjustment/create',
                name: 'side-menu-stock-adjustment-create',
                component: StockAdjustmentCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/stock-adjustment/edit/:ulid',
                name: 'side-menu-stock-adjustment-edit',
                component: StockAdjustmentEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Purchase Order
          {
            path: '/dashboard/purchase-order',
            name: 'side-menu-purchase-order',
            redirect: '/dashboard/purchase-order/list',
            component: PurchaseOrderIndex,
            children: [
              {
                path: '/dashboard/purchase-order/list',
                name: 'side-menu-purchase-order-list',
                component: PurchaseOrderList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/purchase-order/create',
                name: 'side-menu-purchase-order-create',
                component: PurchaseOrderCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/purchase-order/edit/:ulid',
                name: 'side-menu-purchase-order-edit',
                component: PurchaseOrderEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/dashboard/purchase-order-item',
            name: 'side-menu-purchase-order-item',
            redirect: '/dashboard/purchase-order-item/list',
            component: PurchaseOrderItemIndex,
            children: [
              {
                path: '/dashboard/purchase-order-item/list',
                name: 'side-menu-purchase-order-item-list',
                component: PurchaseOrderItemList,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/dashboard/purchase-order-down-payment',
            name: 'side-menu-purchase-order-down-payment',
            redirect: '/dashboard/purchase-order-down-payment/list',
            component: PurchaseOrderDownPaymentIndex,
            children: [
              {
                path: '/dashboard/purchase-order-down-payment/list',
                name: 'side-menu-purchase-order-down-payment-list',
                component: PurchaseOrderDownPaymentList,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/dashboard/purchase-order-down-payment-not-fully-allocated',
            name: 'side-menu-purchase-order-down-payment-not-fully-allocated',
            component: PurchaseOrderDownPaymentNotFullyAllocatedIndex,
            meta: {
              remember: true,
            },
          },
          {
            path: '/dashboard/purchase-order-down-payment-refund',
            name: 'side-menu-purchase-order-down-payment-refund',
            redirect: '/dashboard/purchase-order-down-payment-refund/list',
            component: PurchaseOrderDownPaymentRefundIndex,
            children: [
              {
                path: '/dashboard/purchase-order-down-payment-refund/list',
                name: 'side-menu-purchase-order-down-payment-refund-list',
                component: PurchaseOrderDownPaymentRefundList,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Capital Opening
          {
            path: '/dashboard/finance/capital-opening',
            name: 'side-menu-finance-capital-opening',
            redirect: '/dashboard/finance/capital-opening/list',
            component: CapitalOpeningIndex,
            children: [
              {
                path: '/dashboard/finance/capital-opening/list',
                name: 'side-menu-finance-capital-opening-list',
                component: CapitalOpeningList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/finance/capital-opening/create',
                name: 'side-menu-finance-capital-opening-create',
                component: CapitalOpeningCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/finance/capital-opening/edit/:ulid',
                name: 'side-menu-finance-capital-opening-edit',
                component: CapitalOpeningEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Capital Transaction
          {
            path: '/dashboard/finance/capital-transaction',
            name: 'side-menu-finance-capital-transaction',
            redirect: '/dashboard/finance/capital-transaction/list',
            component: CapitalTransactionIndex,
            children: [
              {
                path: '/dashboard/finance/capital-transaction/list',
                name: 'side-menu-finance-capital-transaction-list',
                component: CapitalTransactionList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/finance/capital-transaction/create',
                name: 'side-menu-finance-capital-transaction-create',
                component: CapitalTransactionCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/finance/capital-transaction/edit/:ulid',
                name: 'side-menu-finance-capital-transaction-edit',
                component: CapitalTransactionEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Cash Account Transfer
          {
            path: '/dashboard/finance/cash-transfer',
            name: 'side-menu-finance-cash-transfer',
            redirect: '/dashboard/finance/cash-transfer/list',
            component: CashTransferIndex,
            children: [
              {
                path: '/dashboard/finance/cash-transfer/list',
                name: 'side-menu-finance-cash-transfer-list',
                component: CashTransferList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/finance/cash-transfer/create',
                name: 'side-menu-finance-cash-transfer-create',
                component: CashTransferCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/finance/cash-transfer/edit/:ulid',
                name: 'side-menu-finance-cash-transfer-edit',
                component: CashTransferEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Expense
          {
            path: '/dashboard/expense',
            name: 'side-menu-expense',
            redirect: '/dashboard/expense/list',
            component: ExpenseIndex,
            children: [
              {
                path: '/dashboard/expense/list',
                name: 'side-menu-expense-list',
                component: ExpenseList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/expense/create',
                name: 'side-menu-expense-create',
                component: ExpenseCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/expense/edit/:ulid',
                name: 'side-menu-expense-edit',
                component: ExpenseEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Debt
          {
            path: '/dashboard/debt',
            name: 'side-menu-debt',
            redirect: '/dashboard/debt/list',
            component: DebtIndex,
            children: [
              {
                path: '/dashboard/debt/list',
                name: 'side-menu-debt-list',
                component: DebtList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/debt/create',
                name: 'side-menu-debt-create',
                component: DebtCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/debt/edit/:ulid',
                name: 'side-menu-debt-edit',
                component: DebtEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Prepaid Expense
          {
            path: '/dashboard/prepaid-expense',
            name: 'side-menu-prepaid-expense',
            redirect: '/dashboard/prepaid-expense/list',
            component: PrepaidExpenseIndex,
            children: [
              {
                path: '/dashboard/prepaid-expense/list',
                name: 'side-menu-prepaid-expense-list',
                component: PrepaidExpenseList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/prepaid-expense/create',
                name: 'side-menu-prepaid-expense-create',
                component: PrepaidExpenseCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/prepaid-expense/edit/:ulid',
                name: 'side-menu-prepaid-expense-edit',
                component: PrepaidExpenseEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Expense Payment
          {
            path: '/dashboard/expense-payment',
            name: 'side-menu-expense-payment',
            redirect: '/dashboard/expense-payment/list',
            component: ExpensePaymentIndex,
            children: [
              {
                path: '/dashboard/expense-payment/list',
                name: 'side-menu-expense-payment-list',
                component: ExpensePaymentList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/expense-payment/create',
                name: 'side-menu-expense-payment-create',
                component: ExpensePaymentCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/expense-payment/edit/:ulid',
                name: 'side-menu-expense-payment-edit',
                component: ExpensePaymentEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/dashboard/income',
            name: 'side-menu-income',
            redirect: '/dashboard/income/list',
            component: IncomeIndex,
            children: [
              {
                path: '/dashboard/income/list',
                name: 'side-menu-income-list',
                component: IncomeList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/income/create',
                name: 'side-menu-income-create',
                component: IncomeCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/income/edit/:ulid',
                name: 'side-menu-income-edit',
                component: IncomeEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/dashboard/prepaid-income',
            name: 'side-menu-prepaid-income',
            redirect: '/dashboard/prepaid-income/list',
            component: PrepaidIncomeIndex,
            children: [
              {
                path: '/dashboard/prepaid-income/list',
                name: 'side-menu-prepaid-income-list',
                component: PrepaidIncomeList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/prepaid-income/create',
                name: 'side-menu-prepaid-income-create',
                component: PrepaidIncomeCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/prepaid-income/edit/:ulid',
                name: 'side-menu-prepaid-income-edit',
                component: PrepaidIncomeEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/dashboard/income-payment',
            name: 'side-menu-income-payment',
            redirect: '/dashboard/income-payment/list',
            component: IncomePaymentIndex,
            children: [
              {
                path: '/dashboard/income-payment/list',
                name: 'side-menu-income-payment-list',
                component: IncomePaymentList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/income-payment/create',
                name: 'side-menu-income-payment-create',
                component: IncomePaymentCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/income-payment/edit/:ulid',
                name: 'side-menu-income-payment-edit',
                component: IncomePaymentEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },

          // Stock Transfer
          {
            path: '/dashboard/stock-transfer',
            name: 'side-menu-stock-transfer',
            redirect: '/dashboard/stock-transfer/list',
            component: StockTransferIndex,
            children: [
              {
                path: '/dashboard/stock-transfer/list',
                name: 'side-menu-stock-transfer-list',
                component: StockTransferList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/stock-transfer/create',
                name: 'side-menu-stock-transfer-create',
                component: StockTransferCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/stock-transfer/edit/:ulid',
                name: 'side-menu-stock-transfer-edit',
                component: StockTransferEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Stock Adjustment In Item
          {
            path: '/dashboard/stock-adjustment-in-item',
            name: 'side-menu-stock-adjustment-in-item',
            redirect: '/dashboard/stock-adjustment-in-item/list',
            component: StockAdjustmentInItemIndex,
            children: [
              {
                path: '/dashboard/stock-adjustment-in-item/list',
                name: 'side-menu-stock-adjustment-in-item-list',
                component: StockAdjustmentInItemList,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Stock Adjustment In Item Serial
          {
            path: '/dashboard/stock-adjustment-in-item-serial',
            name: 'side-menu-stock-adjustment-in-item-serial',
            redirect: '/dashboard/stock-adjustment-in-item-serial/list',
            component: StockAdjustmentInItemSerialIndex,
            children: [
              {
                path: '/dashboard/stock-adjustment-in-item-serial/list',
                name: 'side-menu-stock-adjustment-in-item-serial-list',
                component: StockAdjustmentInItemSerialList,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Stock Adjustment Out Item
          {
            path: '/dashboard/stock-adjustment-out-item',
            name: 'side-menu-stock-adjustment-out-item',
            redirect: '/dashboard/stock-adjustment-out-item/list',
            component: StockAdjustmentOutItemIndex,
            children: [
              {
                path: '/dashboard/stock-adjustment-out-item/list',
                name: 'side-menu-stock-adjustment-out-item-list',
                component: StockAdjustmentOutItemList,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Stock Adjustment Out Item Serial
          {
            path: '/dashboard/stock-adjustment-out-item-serial',
            name: 'side-menu-stock-adjustment-out-item-serial',
            redirect: '/dashboard/stock-adjustment-out-item-serial/list',
            component: StockAdjustmentOutItemSerialIndex,
            children: [
              {
                path: '/dashboard/stock-adjustment-out-item-serial/list',
                name: 'side-menu-stock-adjustment-out-item-serial-list',
                component: StockAdjustmentOutItemSerialList,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Stock Transfer Product Unit
          {
            path: '/dashboard/stock-transfer-item',
            name: 'side-menu-stock-transfer-item',
            component: StockTransferItemList,
            meta: {
              remember: true,
            },
          },
          // Stock Transfer Product Unit Serial
          {
            path: '/dashboard/stock-transfer-item-serial',
            name: 'side-menu-stock-transfer-item-serial',
            component: StockTransferItemSerialList,
            meta: {
              remember: true,
            },
          },
        ],
      },

      // Cash Account With Remaining Balance
      {
        path: '/dashboard/finance/cash-account-with-remaining-balance',
        name: 'side-menu-cash-account-with-remaining-balance',
        redirect: '/dashboard/finance/cash-account-with-remaining-balance/list',
        component: CashAccountWithRemainingBalanceIndex,
        children: [
          {
            path: '/dashboard/finance/cash-account-with-remaining-balance/list',
            name: 'side-menu-cash-account-with-remaining-balance-list',
            component: CashAccountWithRemainingBalanceList,
            meta: {
              remember: true,
            },
          },
        ],
      },
      // Product With Remaining Stock
      {
        path: '/dashboard/product/product-with-remaining-stock',
        name: 'side-menu-product-with-remaining-stock',
        redirect: '/dashboard/product/product-with-remaining-stock/list',
        component: ProductWithRemainingStockIndex,
        children: [
          {
            path: '/dashboard/product/product-with-remaining-stock/list',
            name: 'side-menu-product-with-remaining-stock-list',
            component: ProductWithRemainingStockList,
            meta: {
              remember: true,
            },
          },
        ],
      },
      // user
      {
        path: '/dashboard/administrator',
        name: 'side-menu-administrator',
        children: [
          {
            path: '/dashboard/administrator/user',
            name: 'side-menu-administrator-user',
            redirect: '/dashboard/administrator/user/list',
            component: UserIndex,
            children: [
              {
                path: '/dashboard/administrator/user/list',
                name: 'side-menu-administrator-user-list',
                component: UserList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/administrator/user/create',
                name: 'side-menu-administrator-user-create',
                component: UserCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/administrator/user/edit/:ulid',
                name: 'side-menu-administrator-user-edit',
                component: UserEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
        ],
      },
      // devtool
      {
        path: '/dashboard/devtool',
        name: 'side-menu-devtool',
        children: [
          {
            path: '/dashboard/devtool/devtool',
            name: 'side-menu-devtool-devtool',
            component: DevTool,
            meta: {
              remember: false,
            },
          },
          {
            path: '/dashboard/devtool/playground',
            name: 'side-menu-devtool-playground',
            children: [
              {
                path: '/dashboard/devtool/playground/p1',
                name: 'side-menu-devtool-playground-p1',
                component: PlayOne,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/dashboard/devtool/playground/p2',
                name: 'side-menu-devtool-playground-p2',
                component: PlayTwo,
                meta: {
                  remember: true,
                },
              },
            ],
          },
        ],
      },
      // error
      {
        path: '/dashboard/error' + '/:code',
        name: 'side-menu-error-code',
        component: ErrorView,
        meta: {
          remember: false,
        },
      },
    ],
  },
  // not found
  {
    path: '/:pathMatch(.*)*',
    component: ErrorPage,
    meta: {
      remember: false,
    },
  },
  // error page
  {
    path: '/error-page',
    name: 'error-page',
    component: ErrorPage,
    meta: {
      remember: false,
    },
  },
];
