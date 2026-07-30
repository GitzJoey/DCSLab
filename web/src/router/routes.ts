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
import ChartOfAccountIndex from '@/pages/chart-of-account/ChartOfAccountIndex.vue';
import ChartOfAccountList from '@/pages/chart-of-account/ChartOfAccountList.vue';
import ChartOfAccountCreate from '@/pages/chart-of-account/ChartOfAccountCreate.vue';
import ChartOfAccountEdit from '@/pages/chart-of-account/ChartOfAccountEdit.vue';
import JournalEntryIndex from '@/pages/journal-entry/JournalEntryIndex.vue';
import JournalEntryList from '@/pages/journal-entry/JournalEntryList.vue';
import JournalEntryCreate from '@/pages/journal-entry/JournalEntryCreate.vue';
import JournalEntryEdit from '@/pages/journal-entry/JournalEntryEdit.vue';
import JournalEntryDetailIndex from '@/pages/journal-entry/JournalEntryDetailIndex.vue';
import JournalEntryDetailList from '@/pages/journal-entry/JournalEntryDetailList.vue';
import BalanceSheetIndex from '@/pages/balance-sheet/BalanceSheetIndex.vue';
import BalanceSheetList from '@/pages/balance-sheet/BalanceSheetList.vue';
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
import AssetCategoryIndex from '@/pages/asset-category/AssetCategoryIndex.vue';
import AssetCategoryList from '@/pages/asset-category/AssetCategoryList.vue';
import AssetCategoryCreate from '@/pages/asset-category/AssetCategoryCreate.vue';
import AssetCategoryEdit from '@/pages/asset-category/AssetCategoryEdit.vue';
import AssetUnitIndex from '@/pages/asset-unit/AssetUnitIndex.vue';
import AssetUnitList from '@/pages/asset-unit/AssetUnitList.vue';
import AssetUnitCreate from '@/pages/asset-unit/AssetUnitCreate.vue';
import AssetUnitEdit from '@/pages/asset-unit/AssetUnitEdit.vue';
import AssetIndex from '@/pages/asset/AssetIndex.vue';
import AssetList from '@/pages/asset/AssetList.vue';
import AssetCreate from '@/pages/asset/AssetCreate.vue';
import AssetEdit from '@/pages/asset/AssetEdit.vue';
import ReceivableCategoryIndex from '@/pages/receivable-category/ReceivableCategoryIndex.vue';
import ReceivableCategoryList from '@/pages/receivable-category/ReceivableCategoryList.vue';
import ReceivableCategoryCreate from '@/pages/receivable-category/ReceivableCategoryCreate.vue';
import ReceivableCategoryEdit from '@/pages/receivable-category/ReceivableCategoryEdit.vue';
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
import ReceivableIndex from '@/pages/receivable/ReceivableIndex.vue';
import ReceivableList from '@/pages/receivable/ReceivableList.vue';
import ReceivableCreate from '@/pages/receivable/ReceivableCreate.vue';
import ReceivableEdit from '@/pages/receivable/ReceivableEdit.vue';
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
import StockAdjustmentIndex from '@/pages/stock-adjustment/StockAdjustmentIndex.vue';
import StockAdjustmentList from '@/pages/stock-adjustment/StockAdjustmentList.vue';
import StockAdjustmentCreate from '@/pages/stock-adjustment/StockAdjustmentCreate.vue';
import StockAdjustmentEdit from '@/pages/stock-adjustment/StockAdjustmentEdit.vue';
import AssetAdjustmentIndex from '@/pages/asset-adjustment/AssetAdjustmentIndex.vue';
import AssetAdjustmentList from '@/pages/asset-adjustment/AssetAdjustmentList.vue';
import AssetAdjustmentCreate from '@/pages/asset-adjustment/AssetAdjustmentCreate.vue';
import AssetAdjustmentEdit from '@/pages/asset-adjustment/AssetAdjustmentEdit.vue';
import AssetPurchaseIndex from '@/pages/asset-purchase/AssetPurchaseIndex.vue';
import AssetPurchaseList from '@/pages/asset-purchase/AssetPurchaseList.vue';
import AssetPurchaseCreate from '@/pages/asset-purchase/AssetPurchaseCreate.vue';
import AssetPurchaseEdit from '@/pages/asset-purchase/AssetPurchaseEdit.vue';
import AssetSaleIndex from '@/pages/asset-sale/AssetSaleIndex.vue';
import AssetSaleList from '@/pages/asset-sale/AssetSaleList.vue';
import AssetSaleCreate from '@/pages/asset-sale/AssetSaleCreate.vue';
import AssetSaleEdit from '@/pages/asset-sale/AssetSaleEdit.vue';
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
import PurchaseReceiptIndex from '@/pages/purchase-receipt/PurchaseReceiptIndex.vue';
import PurchaseReceiptList from '@/pages/purchase-receipt/PurchaseReceiptList.vue';
import PurchaseReceiptCreate from '@/pages/purchase-receipt/PurchaseReceiptCreate.vue';
import PurchaseReceiptEdit from '@/pages/purchase-receipt/PurchaseReceiptEdit.vue';
import PurchaseInvoiceIndex from '@/pages/purchase-invoice/PurchaseInvoiceIndex.vue';
import PurchaseInvoiceList from '@/pages/purchase-invoice/PurchaseInvoiceList.vue';
import PurchaseInvoiceCreate from '@/pages/purchase-invoice/PurchaseInvoiceCreate.vue';
import PurchaseInvoiceEdit from '@/pages/purchase-invoice/PurchaseInvoiceEdit.vue';
import PurchaseInvoicePaymentIndex from '@/pages/purchase-invoice/PurchaseInvoicePaymentIndex.vue';
import PurchaseInvoicePaymentList from '@/pages/purchase-invoice/PurchaseInvoicePaymentList.vue';
import PurchaseReturnIndex from '@/pages/purchase-return/PurchaseReturnIndex.vue';
import PurchaseReturnList from '@/pages/purchase-return/PurchaseReturnList.vue';
import PurchaseReturnCreate from '@/pages/purchase-return/PurchaseReturnCreate.vue';
import PurchaseReturnEdit from '@/pages/purchase-return/PurchaseReturnEdit.vue';
import PurchaseOrderItemIndex from '@/pages/purchase-order/PurchaseOrderItemIndex.vue';
import PurchaseOrderItemList from '@/pages/purchase-order/PurchaseOrderItemList.vue';
import PurchaseOrderPaymentIndex from '@/pages/purchase-order/PurchaseOrderPaymentIndex.vue';
import PurchaseOrderPaymentList from '@/pages/purchase-order/PurchaseOrderPaymentList.vue';
import PurchaseOrderPaymentNotFullyAllocatedIndex from '@/pages/purchase-order/PurchaseOrderPaymentNotFullyAllocatedIndex.vue';
import PurchaseOrderPaymentRefundIndex from '@/pages/purchase-order/PurchaseOrderPaymentRefundIndex.vue';
import PurchaseOrderPaymentRefundList from '@/pages/purchase-order/PurchaseOrderPaymentRefundList.vue';
import SalesOrderIndex from '@/pages/sales-order/SalesOrderIndex.vue';
import SalesOrderList from '@/pages/sales-order/SalesOrderList.vue';
import SalesOrderCreate from '@/pages/sales-order/SalesOrderCreate.vue';
import SalesOrderEdit from '@/pages/sales-order/SalesOrderEdit.vue';
import SalesOrderDeliveryIndex from '@/pages/sales-delivery/SalesOrderDeliveryIndex.vue';
import SalesOrderDeliveryList from '@/pages/sales-delivery/SalesOrderDeliveryList.vue';
import SalesOrderDeliveryCreate from '@/pages/sales-delivery/SalesOrderDeliveryCreate.vue';
import SalesOrderDeliveryEdit from '@/pages/sales-delivery/SalesOrderDeliveryEdit.vue';
import SalesInvoiceIndex from '@/pages/sales-invoice/SalesInvoiceIndex.vue';
import SalesInvoiceList from '@/pages/sales-invoice/SalesInvoiceList.vue';
import SalesInvoiceCreate from '@/pages/sales-invoice/SalesInvoiceCreate.vue';
import SalesInvoiceEdit from '@/pages/sales-invoice/SalesInvoiceEdit.vue';
import SalesReturnIndex from '@/pages/sales-return/SalesReturnIndex.vue';
import SalesReturnList from '@/pages/sales-return/SalesReturnList.vue';
import SalesReturnCreate from '@/pages/sales-return/SalesReturnCreate.vue';
import SalesReturnEdit from '@/pages/sales-return/SalesReturnEdit.vue';
import SalesOrderItemIndex from '@/pages/sales-order/SalesOrderItemIndex.vue';
import SalesOrderItemList from '@/pages/sales-order/SalesOrderItemList.vue';
import SalesOrderPaymentIndex from '@/pages/sales-order/SalesOrderPaymentIndex.vue';
import SalesOrderPaymentList from '@/pages/sales-order/SalesOrderPaymentList.vue';
import SalesOrderPaymentNotFullyAllocatedIndex from '@/pages/sales-order/SalesOrderPaymentNotFullyAllocatedIndex.vue';
import SalesOrderPaymentRefundIndex from '@/pages/sales-order/SalesOrderPaymentRefundIndex.vue';
import SalesOrderPaymentRefundList from '@/pages/sales-order/SalesOrderPaymentRefundList.vue';
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
        path: '/company',
        children: [
          // Company
          {
            path: '/company',
            name: 'side-menu-company-company',
            redirect: '/company/list',
            component: CompanyIndex,
            children: [
              {
                path: '/company/list',
                name: 'side-menu-company-company-list',
                component: CompanyList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/company/create',
                name: 'side-menu-company-company-create',
                component: CompanyCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/company/edit/:ulid',
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
            path: '/company/branch',
            name: 'side-menu-company-branch',
            redirect: '/company/branch/list',
            component: BranchIndex,
            children: [
              {
                path: '/company/branch/list',
                name: 'side-menu-company-branch-list',
                component: BranchList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/company/branch/create',
                name: 'side-menu-company-branch-create',
                component: BranchCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/company/branch/edit/:ulid',
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
            path: '/company/warehouse',
            name: 'side-menu-company-warehouse',
            redirect: '/company/warehouse/list',
            component: WarehouseIndex,
            children: [
              {
                path: '/company/warehouse/list',
                name: 'side-menu-company-warehouse-list',
                component: WarehouseList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/company/warehouse/create',
                name: 'side-menu-company-warehouse-create',
                component: WarehouseCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/company/warehouse/edit/:ulid',
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
        path: '/finance',
        children: [
          // COA
          {
            path: '/chart-of-account',
            children: [
              {
                path: '/chart-of-account',
                name: 'side-menu-chart-of-account',
                redirect: '/chart-of-account/list',
                component: ChartOfAccountIndex,
                children: [
                  {
                    path: '/chart-of-account/list',
                    name: 'side-menu-chart-of-account-list',
                    component: ChartOfAccountList,
                    meta: {
                      remember: true,
                    },
                  },
                  {
                    path: '/chart-of-account/create',
                    name: 'side-menu-chart-of-account-create',
                    component: ChartOfAccountCreate,
                    meta: {
                      remember: true,
                    },
                  },
                  {
                    path: '/chart-of-account/edit/:ulid',
                    name: 'side-menu-chart-of-account-edit',
                    component: ChartOfAccountEdit,
                    meta: {
                      remember: true,
                    },
                  },
                ],
              },
            ],
          },
          // Journal Entry
          {
            path: '/journal-entry',
            children: [
              {
                path: '/journal-entry',
                name: 'side-menu-journal-entry',
                redirect: '/journal-entry/list',
                component: JournalEntryIndex,
                children: [
                  {
                    path: '/journal-entry/list',
                    name: 'side-menu-journal-entry-list',
                    component: JournalEntryList,
                    meta: {
                      remember: true,
                    },
                  },
                  {
                    path: '/journal-entry/create',
                    name: 'side-menu-journal-entry-create',
                    component: JournalEntryCreate,
                    meta: {
                      remember: true,
                    },
                  },
                  {
                    path: '/journal-entry/edit/:ulid',
                    name: 'side-menu-journal-entry-edit',
                    component: JournalEntryEdit,
                    meta: {
                      remember: true,
                    },
                  },
                ],
              },
            ],
          },
          // Investor
          {
            path: '/finance/investor',
            name: 'side-menu-company-investor',
            redirect: '/finance/investor/list',
            component: InvestorIndex,
            children: [
              {
                path: '/finance/investor/list',
                name: 'side-menu-company-investor-list',
                component: InvestorList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/finance/investor/create',
                name: 'side-menu-company-investor-create',
                component: InvestorCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/finance/investor/edit/:ulid',
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
            path: '/finance/cash-account',
            name: 'side-menu-finance-cash-account',
            redirect: '/finance/cash-account/list',
            component: CashAccountIndex,
            children: [
              {
                path: '/finance/cash-account/list',
                name: 'side-menu-finance-cash-account-list',
                component: CashAccountList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/finance/cash-account/create',
                name: 'side-menu-finance-cash-account-create',
                component: CashAccountCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/finance/cash-account/edit/:ulid',
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
        path: '/product',
        children: [
          {
            path: '/product/product-category',
            name: 'side-menu-product-product-category',
            redirect: '/product/product-category/list',
            component: ProductCategoryIndex,
            children: [
              {
                path: '/product/product-category/list',
                name: 'side-menu-product-product-category-list',
                component: ProductCategoryList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/product/product-category/create',
                name: 'side-menu-product-product-category-create',
                component: ProductCategoryCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/product/product-category/edit/:ulid',
                name: 'side-menu-product-product-category-edit',
                component: ProductCategoryEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/product/brand',
            name: 'side-menu-product-brand',
            redirect: '/product/brand/list',
            component: BrandIndex,
            children: [
              {
                path: '/product/brand/list',
                name: 'side-menu-product-brand-list',
                component: BrandList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/product/brand/create',
                name: 'side-menu-product-brand-create',
                component: BrandCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/product/brand/edit/:ulid',
                name: 'side-menu-product-brand-edit',
                component: BrandEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/product/unit',
            name: 'side-menu-product-unit',
            redirect: '/product/unit/list',
            component: UnitIndex,
            children: [
              {
                path: '/product/unit/list',
                name: 'side-menu-product-unit-list',
                component: UnitList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/product/unit/create',
                name: 'side-menu-product-unit-create',
                component: UnitCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/product/unit/edit/:ulid',
                name: 'side-menu-product-unit-edit',
                component: UnitEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/product/vat-profile',
            name: 'side-menu-product-vat-profile',
            redirect: '/product/vat-profile/list',
            component: VatProfileIndex,
            children: [
              {
                path: '/product/vat-profile/list',
                name: 'side-menu-product-vat-profile-list',
                component: VatProfileList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/product/vat-profile/create',
                name: 'side-menu-product-vat-profile-create',
                component: VatProfileCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/product/vat-profile/edit/:ulid',
                name: 'side-menu-product-vat-profile-edit',
                component: VatProfileEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/product',
            name: 'side-menu-product-product',
            redirect: '/product/list',
            component: ProductIndex,
            children: [
              {
                path: '/product/list',
                name: 'side-menu-product-product-list',
                component: ProductList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/product/create',
                name: 'side-menu-product-product-create',
                component: ProductCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/product/edit/:ulid',
                name: 'side-menu-product-product-edit',
                component: ProductEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/product/product-service',
            name: 'side-menu-product-product-service',
            redirect: '/product/product-service/list',
            component: ProductServiceIndex,
            children: [
              {
                path: '/product/product-service/list',
                name: 'side-menu-product-product-service-list',
                component: ProductServiceList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/product/product-service/create',
                name: 'side-menu-product-product-service-create',
                component: ProductServiceCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/product/product-service/edit/:ulid',
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
        path: '/supplier',
        children: [
          {
            path: '/supplier',
            name: 'side-menu-supplier',
            redirect: '/supplier/list',
            component: SupplierIndex,
            children: [
              {
                path: '/supplier/list',
                name: 'side-menu-supplier-supplier-list',
                component: SupplierList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/supplier/create',
                name: 'side-menu-supplier-supplier-create',
                component: SupplierCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/supplier/edit/:ulid',
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
        path: '/customer',
        children: [
          // Customer Group
          {
            path: '/customer/customer-group',
            name: 'side-menu-customer-group',
            redirect: '/customer/customer-group/list',
            component: CustomerGroupIndex,
            children: [
              {
                path: '/customer/customer-group/list',
                name: 'side-menu-customer-group-list',
                component: CustomerGroupList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/customer/customer-group/create',
                name: 'side-menu-customer-group-create',
                component: CustomerGroupCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/customer/customer-group/edit/:ulid',
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
            path: '/customer',
            name: 'side-menu-customer',
            redirect: '/customer/list',
            component: CustomerIndex,
            children: [
              {
                path: '/customer/list',
                name: 'side-menu-customer-list',
                component: CustomerList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/customer/create',
                name: 'side-menu-customer-create',
                component: CustomerCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/customer/edit/:ulid',
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
        path: '/stock-adjustment-category',
        children: [
          {
            path: '/stock-adjustment-category',
            name: 'side-menu-stock-adjustment-category',
            redirect: '/stock-adjustment-category/list',
            component: StockAdjustmentCategoryIndex,
            children: [
              {
                path: '/stock-adjustment-category/list',
                name: 'side-menu-stock-adjustment-category-list',
                component: StockAdjustmentCategoryList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/stock-adjustment-category/create',
                name: 'side-menu-stock-adjustment-category-create',
                component: StockAdjustmentCategoryCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/stock-adjustment-category/edit/:ulid',
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
      // Expense Category
      {
        path: '/expense-category',
        children: [
          {
            path: '/expense-category',
            name: 'side-menu-expense-category',
            redirect: '/expense-category/list',
            component: ExpenseCategoryIndex,
            children: [
              {
                path: '/expense-category/list',
                name: 'side-menu-expense-category-list',
                component: ExpenseCategoryList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/expense-category/create',
                name: 'side-menu-expense-category-create',
                component: ExpenseCategoryCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/expense-category/edit/:ulid',
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
        path: '/debt-category',
        children: [
          {
            path: '/debt-category',
            name: 'side-menu-debt-category',
            redirect: '/debt-category/list',
            component: DebtCategoryIndex,
            children: [
              {
                path: '/debt-category/list',
                name: 'side-menu-debt-category-list',
                component: DebtCategoryList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/debt-category/create',
                name: 'side-menu-debt-category-create',
                component: DebtCategoryCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/debt-category/edit/:ulid',
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
        path: '/asset-category',
        children: [
          {
            path: '/asset-category',
            name: 'side-menu-asset-category',
            redirect: '/asset-category/list',
            component: AssetCategoryIndex,
            children: [
              {
                path: '/asset-category/list',
                name: 'side-menu-asset-category-list',
                component: AssetCategoryList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/asset-category/create',
                name: 'side-menu-asset-category-create',
                component: AssetCategoryCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/asset-category/edit/:ulid',
                name: 'side-menu-asset-category-edit',
                component: AssetCategoryEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
        ],
      },
      {
        path: '/asset-unit',
        children: [
          {
            path: '/asset-unit',
            name: 'side-menu-asset-unit',
            redirect: '/asset-unit/list',
            component: AssetUnitIndex,
            children: [
              {
                path: '/asset-unit/list',
                name: 'side-menu-asset-unit-list',
                component: AssetUnitList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/asset-unit/create',
                name: 'side-menu-asset-unit-create',
                component: AssetUnitCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/asset-unit/edit/:ulid',
                name: 'side-menu-asset-unit-edit',
                component: AssetUnitEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
        ],
      },
      {
        path: '/asset',
        children: [
          {
            path: '/asset',
            name: 'side-menu-asset',
            redirect: '/asset/list',
            component: AssetIndex,
            children: [
              {
                path: '/asset/list',
                name: 'side-menu-asset-list',
                component: AssetList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/asset/create',
                name: 'side-menu-asset-create',
                component: AssetCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/asset/edit/:ulid',
                name: 'side-menu-asset-edit',
                component: AssetEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
        ],
      },
      {
        path: '/receivable-category',
        children: [
          {
            path: '/receivable-category',
            name: 'side-menu-receivable-category',
            redirect: '/receivable-category/list',
            component: ReceivableCategoryIndex,
            children: [
              {
                path: '/receivable-category/list',
                name: 'side-menu-receivable-category-list',
                component: ReceivableCategoryList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/receivable-category/create',
                name: 'side-menu-receivable-category-create',
                component: ReceivableCategoryCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/receivable-category/edit/:ulid',
                name: 'side-menu-receivable-category-edit',
                component: ReceivableCategoryEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
        ],
      },
      {
        path: '/debt-creditor',
        children: [
          {
            path: '/debt-creditor',
            name: 'side-menu-debt-creditor',
            redirect: '/debt-creditor/list',
            component: DebtCreditorIndex,
            children: [
              {
                path: '/debt-creditor/list',
                name: 'side-menu-debt-creditor-list',
                component: DebtCreditorList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/debt-creditor/create',
                name: 'side-menu-debt-creditor-create',
                component: DebtCreditorCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/debt-creditor/edit/:ulid',
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
        path: '/income-category',
        children: [
          {
            path: '/income-category',
            name: 'side-menu-income-category',
            redirect: '/income-category/list',
            component: IncomeCategoryIndex,
            children: [
              {
                path: '/income-category/list',
                name: 'side-menu-income-category-list',
                component: IncomeCategoryList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/income-category/create',
                name: 'side-menu-income-category-create',
                component: IncomeCategoryCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/income-category/edit/:ulid',
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
      // Transaction
      {
        path: '/transaction',
        name: 'side-menu-transaction',
        children: [
          // Stock Adjustment
          {
            path: '/stock-adjustment',
            name: 'side-menu-stock-adjustment',
            redirect: '/stock-adjustment/list',
            component: StockAdjustmentIndex,
            children: [
              {
                path: '/stock-adjustment/list',
                name: 'side-menu-stock-adjustment-list',
                component: StockAdjustmentList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/stock-adjustment/create',
                name: 'side-menu-stock-adjustment-create',
                component: StockAdjustmentCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/stock-adjustment/edit/:ulid',
                name: 'side-menu-stock-adjustment-edit',
                component: StockAdjustmentEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Asset Adjustment
          {
            path: '/asset-adjustment',
            name: 'side-menu-asset-adjustment',
            redirect: '/asset-adjustment/list',
            component: AssetAdjustmentIndex,
            children: [
              {
                path: '/asset-adjustment/list',
                name: 'side-menu-asset-adjustment-list',
                component: AssetAdjustmentList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/asset-adjustment/create',
                name: 'side-menu-asset-adjustment-create',
                component: AssetAdjustmentCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/asset-adjustment/edit/:ulid',
                name: 'side-menu-asset-adjustment-edit',
                component: AssetAdjustmentEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/asset-purchase',
            name: 'side-menu-asset-purchase',
            redirect: '/asset-purchase/list',
            component: AssetPurchaseIndex,
            children: [
              {
                path: '/asset-purchase/list',
                name: 'side-menu-asset-purchase-list',
                component: AssetPurchaseList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/asset-purchase/create',
                name: 'side-menu-asset-purchase-create',
                component: AssetPurchaseCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/asset-purchase/edit/:ulid',
                name: 'side-menu-asset-purchase-edit',
                component: AssetPurchaseEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/asset-sale',
            name: 'side-menu-asset-sale',
            redirect: '/asset-sale/list',
            component: AssetSaleIndex,
            children: [
              {
                path: '/asset-sale/list',
                name: 'side-menu-asset-sale-list',
                component: AssetSaleList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/asset-sale/create',
                name: 'side-menu-asset-sale-create',
                component: AssetSaleCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/asset-sale/edit/:ulid',
                name: 'side-menu-asset-sale-edit',
                component: AssetSaleEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Purchase Order
          {
            path: '/purchase-order',
            name: 'side-menu-purchase-order',
            redirect: '/purchase-order/list',
            component: PurchaseOrderIndex,
            children: [
              {
                path: '/purchase-order/list',
                name: 'side-menu-purchase-order-list',
                component: PurchaseOrderList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/purchase-order/create',
                name: 'side-menu-purchase-order-create',
                component: PurchaseOrderCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/purchase-order/edit/:ulid',
                name: 'side-menu-purchase-order-edit',
                component: PurchaseOrderEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/purchase-receipt',
            name: 'side-menu-purchase-receipt',
            redirect: '/purchase-receipt/list',
            component: PurchaseReceiptIndex,
            children: [
              {
                path: '/purchase-receipt/list',
                name: 'side-menu-purchase-receipt-list',
                component: PurchaseReceiptList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/purchase-receipt/create',
                name: 'side-menu-purchase-receipt-create',
                component: PurchaseReceiptCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/purchase-receipt/edit/:ulid',
                name: 'side-menu-purchase-receipt-edit',
                component: PurchaseReceiptEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Purchase Invoice
          {
            path: '/purchase-invoice',
            name: 'side-menu-purchase-invoice',
            redirect: '/purchase-invoice/list',
            component: PurchaseInvoiceIndex,
            children: [
              {
                path: '/purchase-invoice/list',
                name: 'side-menu-purchase-invoice-list',
                component: PurchaseInvoiceList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/purchase-invoice/create',
                name: 'side-menu-purchase-invoice-create',
                component: PurchaseInvoiceCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/purchase-invoice/edit/:ulid',
                name: 'side-menu-purchase-invoice-edit',
                component: PurchaseInvoiceEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Purchase Return
          {
            path: '/purchase-return',
            name: 'side-menu-purchase-return',
            redirect: '/purchase-return/list',
            component: PurchaseReturnIndex,
            children: [
              {
                path: '/purchase-return/list',
                name: 'side-menu-purchase-return-list',
                component: PurchaseReturnList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/purchase-return/create',
                name: 'side-menu-purchase-return-create',
                component: PurchaseReturnCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/purchase-return/edit/:ulid',
                name: 'side-menu-purchase-return-edit',
                component: PurchaseReturnEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Sales Order
          {
            path: '/sales-order',
            name: 'side-menu-sales-order',
            redirect: '/sales-order/list',
            component: SalesOrderIndex,
            children: [
              {
                path: '/sales-order/list',
                name: 'side-menu-sales-order-list',
                component: SalesOrderList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/sales-order/create',
                name: 'side-menu-sales-order-create',
                component: SalesOrderCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/sales-order/edit/:ulid',
                name: 'side-menu-sales-order-edit',
                component: SalesOrderEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Sales Delivery
          {
            path: '/sales-delivery',
            name: 'side-menu-sales-delivery',
            redirect: '/sales-delivery/list',
            component: SalesOrderDeliveryIndex,
            children: [
              {
                path: '/sales-delivery/list',
                name: 'side-menu-sales-delivery-list',
                component: SalesOrderDeliveryList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/sales-delivery/create',
                name: 'side-menu-sales-delivery-create',
                component: SalesOrderDeliveryCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/sales-delivery/edit/:ulid',
                name: 'side-menu-sales-delivery-edit',
                component: SalesOrderDeliveryEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Sales Invoice
          {
            path: '/sales-invoice',
            name: 'side-menu-sales-invoice',
            redirect: '/sales-invoice/list',
            component: SalesInvoiceIndex,
            children: [
              {
                path: '/sales-invoice/list',
                name: 'side-menu-sales-invoice-list',
                component: SalesInvoiceList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/sales-invoice/create',
                name: 'side-menu-sales-invoice-create',
                component: SalesInvoiceCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/sales-invoice/edit/:ulid',
                name: 'side-menu-sales-invoice-edit',
                component: SalesInvoiceEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Sales Return
          {
            path: '/sales-return',
            name: 'side-menu-sales-return',
            redirect: '/sales-return/list',
            component: SalesReturnIndex,
            children: [
              {
                path: '/sales-return/list',
                name: 'side-menu-sales-return-list',
                component: SalesReturnList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/sales-return/create',
                name: 'side-menu-sales-return-create',
                component: SalesReturnCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/sales-return/edit/:ulid',
                name: 'side-menu-sales-return-edit',
                component: SalesReturnEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/purchase-order-item',
            name: 'side-menu-purchase-order-item',
            redirect: '/purchase-order-item/list',
            component: PurchaseOrderItemIndex,
            children: [
              {
                path: '/purchase-order-item/list',
                name: 'side-menu-purchase-order-item-list',
                component: PurchaseOrderItemList,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/purchase-order-payment',
            name: 'side-menu-purchase-order-payment',
            redirect: '/purchase-order-payment/list',
            component: PurchaseOrderPaymentIndex,
            children: [
              {
                path: '/purchase-order-payment/list',
                name: 'side-menu-purchase-order-payment-list',
                component: PurchaseOrderPaymentList,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/purchase-order-payment-not-fully-allocated',
            name: 'side-menu-purchase-order-payment-not-fully-allocated',
            component: PurchaseOrderPaymentNotFullyAllocatedIndex,
            meta: {
              remember: true,
            },
          },
          {
            path: '/purchase-order-payment-refund',
            name: 'side-menu-purchase-order-payment-refund',
            redirect: '/purchase-order-payment-refund/list',
            component: PurchaseOrderPaymentRefundIndex,
            children: [
              {
                path: '/purchase-order-payment-refund/list',
                name: 'side-menu-purchase-order-payment-refund-list',
                component: PurchaseOrderPaymentRefundList,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/purchase-invoice-payment',
            name: 'side-menu-purchase-invoice-payment',
            redirect: '/purchase-invoice-payment/list',
            component: PurchaseInvoicePaymentIndex,
            children: [
              {
                path: '/purchase-invoice-payment/list',
                name: 'side-menu-purchase-invoice-payment-list',
                component: PurchaseInvoicePaymentList,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/sales-order-item',
            name: 'side-menu-sales-order-item',
            redirect: '/sales-order-item/list',
            component: SalesOrderItemIndex,
            children: [
              {
                path: '/sales-order-item/list',
                name: 'side-menu-sales-order-item-list',
                component: SalesOrderItemList,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/sales-order-payment',
            name: 'side-menu-sales-order-payment',
            redirect: '/sales-order-payment/list',
            component: SalesOrderPaymentIndex,
            children: [
              {
                path: '/sales-order-payment/list',
                name: 'side-menu-sales-order-payment-list',
                component: SalesOrderPaymentList,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/sales-order-payment-not-fully-allocated',
            name: 'side-menu-sales-order-payment-not-fully-allocated',
            component: SalesOrderPaymentNotFullyAllocatedIndex,
            meta: {
              remember: true,
            },
          },
          {
            path: '/sales-order-payment-refund',
            name: 'side-menu-sales-order-payment-refund',
            redirect: '/sales-order-payment-refund/list',
            component: SalesOrderPaymentRefundIndex,
            children: [
              {
                path: '/sales-order-payment-refund/list',
                name: 'side-menu-sales-order-payment-refund-list',
                component: SalesOrderPaymentRefundList,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          // Capital Opening
          {
            path: '/finance/capital-opening',
            name: 'side-menu-finance-capital-opening',
            redirect: '/finance/capital-opening/list',
            component: CapitalOpeningIndex,
            children: [
              {
                path: '/finance/capital-opening/list',
                name: 'side-menu-finance-capital-opening-list',
                component: CapitalOpeningList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/finance/capital-opening/create',
                name: 'side-menu-finance-capital-opening-create',
                component: CapitalOpeningCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/finance/capital-opening/edit/:ulid',
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
            path: '/finance/capital-transaction',
            name: 'side-menu-finance-capital-transaction',
            redirect: '/finance/capital-transaction/list',
            component: CapitalTransactionIndex,
            children: [
              {
                path: '/finance/capital-transaction/list',
                name: 'side-menu-finance-capital-transaction-list',
                component: CapitalTransactionList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/finance/capital-transaction/create',
                name: 'side-menu-finance-capital-transaction-create',
                component: CapitalTransactionCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/finance/capital-transaction/edit/:ulid',
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
            path: '/finance/cash-transfer',
            name: 'side-menu-finance-cash-transfer',
            redirect: '/finance/cash-transfer/list',
            component: CashTransferIndex,
            children: [
              {
                path: '/finance/cash-transfer/list',
                name: 'side-menu-finance-cash-transfer-list',
                component: CashTransferList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/finance/cash-transfer/create',
                name: 'side-menu-finance-cash-transfer-create',
                component: CashTransferCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/finance/cash-transfer/edit/:ulid',
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
            path: '/expense',
            name: 'side-menu-expense',
            redirect: '/expense/list',
            component: ExpenseIndex,
            children: [
              {
                path: '/expense/list',
                name: 'side-menu-expense-list',
                component: ExpenseList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/expense/create',
                name: 'side-menu-expense-create',
                component: ExpenseCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/expense/edit/:ulid',
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
            path: '/debt',
            name: 'side-menu-debt',
            redirect: '/debt/list',
            component: DebtIndex,
            children: [
              {
                path: '/debt/list',
                name: 'side-menu-debt-list',
                component: DebtList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/debt/create',
                name: 'side-menu-debt-create',
                component: DebtCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/debt/edit/:ulid',
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
            path: '/prepaid-expense',
            name: 'side-menu-prepaid-expense',
            redirect: '/prepaid-expense/list',
            component: PrepaidExpenseIndex,
            children: [
              {
                path: '/prepaid-expense/list',
                name: 'side-menu-prepaid-expense-list',
                component: PrepaidExpenseList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/prepaid-expense/create',
                name: 'side-menu-prepaid-expense-create',
                component: PrepaidExpenseCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/prepaid-expense/edit/:ulid',
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
            path: '/expense-payment',
            name: 'side-menu-expense-payment',
            redirect: '/expense-payment/list',
            component: ExpensePaymentIndex,
            children: [
              {
                path: '/expense-payment/list',
                name: 'side-menu-expense-payment-list',
                component: ExpensePaymentList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/expense-payment/create',
                name: 'side-menu-expense-payment-create',
                component: ExpensePaymentCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/expense-payment/edit/:ulid',
                name: 'side-menu-expense-payment-edit',
                component: ExpensePaymentEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/income',
            name: 'side-menu-income',
            redirect: '/income/list',
            component: IncomeIndex,
            children: [
              {
                path: '/income/list',
                name: 'side-menu-income-list',
                component: IncomeList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/income/create',
                name: 'side-menu-income-create',
                component: IncomeCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/income/edit/:ulid',
                name: 'side-menu-income-edit',
                component: IncomeEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/receivable',
            name: 'side-menu-receivable',
            redirect: '/receivable/list',
            component: ReceivableIndex,
            children: [
              {
                path: '/receivable/list',
                name: 'side-menu-receivable-list',
                component: ReceivableList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/receivable/create',
                name: 'side-menu-receivable-create',
                component: ReceivableCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/receivable/edit/:ulid',
                name: 'side-menu-receivable-edit',
                component: ReceivableEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/prepaid-income',
            name: 'side-menu-prepaid-income',
            redirect: '/prepaid-income/list',
            component: PrepaidIncomeIndex,
            children: [
              {
                path: '/prepaid-income/list',
                name: 'side-menu-prepaid-income-list',
                component: PrepaidIncomeList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/prepaid-income/create',
                name: 'side-menu-prepaid-income-create',
                component: PrepaidIncomeCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/prepaid-income/edit/:ulid',
                name: 'side-menu-prepaid-income-edit',
                component: PrepaidIncomeEdit,
                meta: {
                  remember: true,
                },
              },
            ],
          },
          {
            path: '/income-payment',
            name: 'side-menu-income-payment',
            redirect: '/income-payment/list',
            component: IncomePaymentIndex,
            children: [
              {
                path: '/income-payment/list',
                name: 'side-menu-income-payment-list',
                component: IncomePaymentList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/income-payment/create',
                name: 'side-menu-income-payment-create',
                component: IncomePaymentCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/income-payment/edit/:ulid',
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
            path: '/stock-transfer',
            name: 'side-menu-stock-transfer',
            redirect: '/stock-transfer/list',
            component: StockTransferIndex,
            children: [
              {
                path: '/stock-transfer/list',
                name: 'side-menu-stock-transfer-list',
                component: StockTransferList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/stock-transfer/create',
                name: 'side-menu-stock-transfer-create',
                component: StockTransferCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/stock-transfer/edit/:ulid',
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
            path: '/stock-adjustment-in-item',
            name: 'side-menu-stock-adjustment-in-item',
            redirect: '/stock-adjustment-in-item/list',
            component: StockAdjustmentInItemIndex,
            children: [
              {
                path: '/stock-adjustment-in-item/list',
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
            path: '/stock-adjustment-in-item-serial',
            name: 'side-menu-stock-adjustment-in-item-serial',
            redirect: '/stock-adjustment-in-item-serial/list',
            component: StockAdjustmentInItemSerialIndex,
            children: [
              {
                path: '/stock-adjustment-in-item-serial/list',
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
            path: '/stock-adjustment-out-item',
            name: 'side-menu-stock-adjustment-out-item',
            redirect: '/stock-adjustment-out-item/list',
            component: StockAdjustmentOutItemIndex,
            children: [
              {
                path: '/stock-adjustment-out-item/list',
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
            path: '/stock-adjustment-out-item-serial',
            name: 'side-menu-stock-adjustment-out-item-serial',
            redirect: '/stock-adjustment-out-item-serial/list',
            component: StockAdjustmentOutItemSerialIndex,
            children: [
              {
                path: '/stock-adjustment-out-item-serial/list',
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
            path: '/stock-transfer-item',
            name: 'side-menu-stock-transfer-item',
            component: StockTransferItemList,
            meta: {
              remember: true,
            },
          },
          // Stock Transfer Product Unit Serial
          {
            path: '/stock-transfer-item-serial',
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
        path: '/finance/cash-account-with-remaining-balance',
        name: 'side-menu-cash-account-with-remaining-balance',
        redirect: '/finance/cash-account-with-remaining-balance/list',
        component: CashAccountWithRemainingBalanceIndex,
        children: [
          {
            path: '/finance/cash-account-with-remaining-balance/list',
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
        path: '/product/product-with-remaining-stock',
        name: 'side-menu-product-with-remaining-stock',
        redirect: '/product/product-with-remaining-stock/list',
        component: ProductWithRemainingStockIndex,
        children: [
          {
            path: '/product/product-with-remaining-stock/list',
            name: 'side-menu-product-with-remaining-stock-list',
            component: ProductWithRemainingStockList,
            meta: {
              remember: true,
            },
          },
        ],
      },
      // Journal Entry Detail Report
      {
        path: '/report/balance-sheet',
        name: 'side-menu-report-balance-sheet',
        redirect: '/report/balance-sheet/list',
        component: BalanceSheetIndex,
        children: [
          {
            path: '/report/balance-sheet/list',
            name: 'side-menu-report-balance-sheet-list',
            component: BalanceSheetList,
            meta: {
              remember: true,
            },
          },
        ],
      },
      // Journal Entry Detail Report
      {
        path: '/report/journal-entry-detail',
        name: 'side-menu-report-journal-entry-detail',
        redirect: '/report/journal-entry-detail/list',
        component: JournalEntryDetailIndex,
        children: [
          {
            path: '/report/journal-entry-detail/list',
            name: 'side-menu-report-journal-entry-detail-list',
            component: JournalEntryDetailList,
            meta: {
              remember: true,
            },
          },
        ],
      },
      // user
      {
        path: '/administrator',
        name: 'side-menu-administrator',
        children: [
          {
            path: '/administrator/user',
            name: 'side-menu-administrator-user',
            redirect: '/administrator/user/list',
            component: UserIndex,
            children: [
              {
                path: '/administrator/user/list',
                name: 'side-menu-administrator-user-list',
                component: UserList,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/administrator/user/create',
                name: 'side-menu-administrator-user-create',
                component: UserCreate,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/administrator/user/edit/:ulid',
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
        path: '/devtool',
        name: 'side-menu-devtool',
        children: [
          {
            path: '/devtool',
            name: 'side-menu-devtool-devtool',
            component: DevTool,
            meta: {
              remember: false,
            },
          },
          {
            path: '/devtool/playground',
            name: 'side-menu-devtool-playground',
            children: [
              {
                path: '/devtool/playground/p1',
                name: 'side-menu-devtool-playground-p1',
                component: PlayOne,
                meta: {
                  remember: true,
                },
              },
              {
                path: '/devtool/playground/p2',
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
        path: '/error' + '/:code',
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

