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
import StockAdjustmentInProductIndex from '@/pages/stock-adjustment/StockAdjustmentInProductIndex.vue';
import StockAdjustmentInProductList from '@/pages/stock-adjustment/StockAdjustmentInProductList.vue';
import StockAdjustmentOutProductIndex from '@/pages/stock-adjustment/StockAdjustmentOutProductIndex.vue';
import StockAdjustmentOutProductList from '@/pages/stock-adjustment/StockAdjustmentOutProductList.vue';
import StockAdjustmentInProductSerialIndex from '@/pages/stock-adjustment/StockAdjustmentInProductSerialIndex.vue';
import StockAdjustmentInProductSerialList from '@/pages/stock-adjustment/StockAdjustmentInProductSerialList.vue';
import StockAdjustmentOutProductSerialIndex from '@/pages/stock-adjustment/StockAdjustmentOutProductSerialIndex.vue';
import StockAdjustmentOutProductSerialList from '@/pages/stock-adjustment/StockAdjustmentOutProductSerialList.vue';
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
      // Stock Adjustment In Product
      {
        path: '/dashboard/stock-adjustment-in-product',
        name: 'side-menu-stock-adjustment-in-product',
        redirect: '/dashboard/stock-adjustment-in-product/list',
        component: StockAdjustmentInProductIndex,
        children: [
          {
            path: '/dashboard/stock-adjustment-in-product/list',
            name: 'side-menu-stock-adjustment-in-product-list',
            component: StockAdjustmentInProductList,
            meta: {
              remember: true,
            },
          },
        ],
      },
      // Stock Adjustment In Product Serial
      {
        path: '/dashboard/stock-adjustment-in-product-serial',
        name: 'side-menu-stock-adjustment-in-product-serial',
        redirect: '/dashboard/stock-adjustment-in-product-serial/list',
        component: StockAdjustmentInProductSerialIndex,
        children: [
          {
            path: '/dashboard/stock-adjustment-in-product-serial/list',
            name: 'side-menu-stock-adjustment-in-product-serial-list',
            component: StockAdjustmentInProductSerialList,
            meta: {
              remember: true,
            },
          },
        ],
      },
      // Stock Adjustment Out Product
      {
        path: '/dashboard/stock-adjustment-out-product',
        name: 'side-menu-stock-adjustment-out-product',
        redirect: '/dashboard/stock-adjustment-out-product/list',
        component: StockAdjustmentOutProductIndex,
        children: [
          {
            path: '/dashboard/stock-adjustment-out-product/list',
            name: 'side-menu-stock-adjustment-out-product-list',
            component: StockAdjustmentOutProductList,
            meta: {
              remember: true,
            },
          },
        ],
      },
      // Stock Adjustment Out Product Serial
      {
        path: '/dashboard/stock-adjustment-out-product-serial',
        name: 'side-menu-stock-adjustment-out-product-serial',
        redirect: '/dashboard/stock-adjustment-out-product-serial/list',
        component: StockAdjustmentOutProductSerialIndex,
        children: [
          {
            path: '/dashboard/stock-adjustment-out-product-serial/list',
            name: 'side-menu-stock-adjustment-out-product-serial-list',
            component: StockAdjustmentOutProductSerialList,
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
