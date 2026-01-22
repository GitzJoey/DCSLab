import Layout from "@/themes";

import LoginPage from "../pages/auth/LoginPage.vue";
import RegisterPage from "../pages/auth/RegisterPage.vue";
import ForgotPasswordPage from "../pages/auth/ForgotPasswordPage.vue";
import ResetPasswordPage from "../pages/auth/ResetPasswordPage.vue";
import MainDashboard from "../pages/dashboard/MainDashboard.vue";
import ProfileView from "../pages/dashboard/ProfileView.vue";
import DevTool from "../pages/dev/DevTool.vue";
import PlayOne from "../pages/dev/PlayOne.vue";
import PlayTwo from "../pages/dev/PlayTwo.vue";
import ErrorView from "../pages/error/ErrorView.vue";
import ErrorPage from "../pages/error/ErrorPage.vue";
import UserIndex from "../pages/administrator/UserIndex.vue";
import UserList from "../pages/administrator/UserList.vue";
import UserCreate from "../pages/administrator/UserCreate.vue";
import UserEdit from "../pages/administrator/UserEdit.vue";
import CompanyIndex from "../pages/company/CompanyIndex.vue";
import CompanyList from "../pages/company/CompanyList.vue";
import CompanyCreate from "../pages/company/CompanyCreate.vue";
import CompanyEdit from "../pages/company/CompanyEdit.vue";
import BranchIndex from "../pages/branch/BranchIndex.vue";
import BranchList from "../pages/branch/BranchList.vue";
import BranchCreate from "../pages/branch/BranchCreate.vue";
import BranchEdit from "../pages/branch/BranchEdit.vue";
import WarehouseIndex from "@/pages/warehouse/WarehouseIndex.vue";
import WarehouseList from "@/pages/warehouse/WarehouseList.vue";
import WarehouseCreate from "@/pages/warehouse/WarehouseCreate.vue";
import WarehouseEdit from "@/pages/warehouse/WarehouseEdit.vue";
import StockAdjustmentCategoryIndex from "@/pages/stock-adjustment-category/StockAdjustmentCategoryIndex.vue";
import StockAdjustmentCategoryList from "@/pages/stock-adjustment-category/StockAdjustmentCategoryList.vue";
import StockAdjustmentCategoryCreate from "@/pages/stock-adjustment-category/StockAdjustmentCategoryCreate.vue";
import StockAdjustmentCategoryEdit from "@/pages/stock-adjustment-category/StockAdjustmentCategoryEdit.vue";
import ProductCategoryIndex from "@/pages/product-category/ProductCategoryIndex.vue";
import ProductCategoryList from "@/pages/product-category/ProductCategoryList.vue";
import ProductCategoryCreate from "@/pages/product-category/ProductCategoryCreate.vue";
import ProductCategoryEdit from "@/pages/product-category/ProductCategoryEdit.vue";
import ProductServiceIndex from "@/pages/product-service/ProductServiceIndex.vue";
import ProductServiceList from "@/pages/product-service/ProductServiceList.vue";
import ProductServiceCreate from "@/pages/product-service/ProductServiceCreate.vue";
import ProductServiceEdit from "@/pages/product-service/ProductServiceEdit.vue";
import ProductIndex from "@/pages/product/ProductIndex.vue";
import ProductList from "@/pages/product/ProductList.vue";
import ProductCreate from "@/pages/product/ProductCreate.vue";
import ProductEdit from "@/pages/product/ProductEdit.vue";
import BrandIndex from "@/pages/brand/BrandIndex.vue";
import BrandList from "@/pages/brand/BrandList.vue";
import BrandCreate from "@/pages/brand/BrandCreate.vue";
import BrandEdit from "@/pages/brand/BrandEdit.vue";
import UnitIndex from "@/pages/unit/UnitIndex.vue";
import UnitList from "@/pages/unit/UnitList.vue";
import UnitCreate from "@/pages/unit/UnitCreate.vue";
import UnitEdit from "@/pages/unit/UnitEdit.vue";
import CustomerGroupIndex from "@/pages/customer-group/CustomerGroupIndex.vue";
import CustomerGroupList from "@/pages/customer-group/CustomerGroupList.vue";
import CustomerGroupCreate from "@/pages/customer-group/CustomerGroupCreate.vue";
import CustomerGroupEdit from "@/pages/customer-group/CustomerGroupEdit.vue";
import CustomerIndex from "@/pages/customer/CustomerIndex.vue";
import CustomerList from "@/pages/customer/CustomerList.vue";
import CustomerCreate from "@/pages/customer/CustomerCreate.vue";
import CustomerEdit from "@/pages/customer/CustomerEdit.vue";
import InvestorIndex from "@/pages/investor/InvestorIndex.vue";
import InvestorList from "@/pages/investor/InvestorList.vue";
import InvestorCreate from "@/pages/investor/InvestorCreate.vue";
import InvestorEdit from "@/pages/investor/InvestorEdit.vue";
import CashAccountIndex from "@/pages/cash-account/CashAccountIndex.vue";
import CashAccountList from "@/pages/cash-account/CashAccountList.vue";
import CashAccountCreate from "@/pages/cash-account/CashAccountCreate.vue";
import CashAccountEdit from "@/pages/cash-account/CashAccountEdit.vue";

export default [
    {
        path: "/",
        redirect: "/auth/login",
    },
    {
        path: "/home",
        redirect: "/dashboard/main",
    },
    {
        path: "/auth",
        children: [
            {
                path: "/auth/login",
                name: "login",
                component: LoginPage,
            },

            {
                path: "/auth/register",
                name: 'register',
                component: RegisterPage,
            },
            {
                path: "/auth/forgot-password",
                name: 'forgot-password',
                component: ForgotPasswordPage,
            },
            {
                path: "/auth/reset-password",
                name: 'reset-password',
                component: ResetPasswordPage,
            },
        ]
    },
    {
        path: "/dashboard",
        component: Layout,
        children: [
            {
                path: "/dashboard/main",
                name: "side-menu-dashboard-maindashboard",
                component: MainDashboard,
                meta: {
                    remember: true,
                },
            },
            {
                path: "/dashboard/profile",
                name: "side-menu-dashboard-profile",
                component: ProfileView,
                meta: {
                    remember: true,
                },
            },
            {
                path: "/dashboard/company",
                children: [
                    {
                        path: "/dashboard/company/company",
                        name: "side-menu-company-company",
                        redirect: "/dashboard/company/company/list",
                        component: CompanyIndex,
                        children: [
                            {
                                path: "/dashboard/company/company/list",
                                name: "side-menu-company-company-list",
                                component: CompanyList,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/company/company/create",
                                name: "side-menu-company-company-create",
                                component: CompanyCreate,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/company/company/edit/:ulid",
                                name: "side-menu-company-company-edit",
                                component: CompanyEdit,
                                meta: {
                                    remember: true,
                                },
                            }
                        ]
                    },
                    {
                        path: "/dashboard/company/branch",
                        name: "side-menu-company-branch",
                        redirect: "/dashboard/company/branch/list",
                        component: BranchIndex,
                        children: [
                            {
                                path: "/dashboard/company/branch/list",
                                name: "side-menu-company-branch-list",
                                component: BranchList,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/company/branch/create",
                                name: "side-menu-company-branch-create",
                                component: BranchCreate,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/company/branch/edit/:ulid",
                                name: "side-menu-company-branch-edit",
                                component: BranchEdit,
                                meta: {
                                    remember: true,
                                },
                            }
                        ]
                    },
                    {
                        path: "/dashboard/company/warehouse",
                        name: "side-menu-company-warehouse",
                        redirect: "/dashboard/company/warehouse/list",
                        component: WarehouseIndex,
                        children: [
                            {
                                path: "/dashboard/company/warehouse/list",
                                name: "side-menu-company-warehouse-list",
                                component: WarehouseList,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/company/warehouse/create",
                                name: "side-menu-company-warehouse-create",
                                component: WarehouseCreate,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/company/warehouse/edit/:ulid",
                                name: "side-menu-company-warehouse-edit",
                                component: WarehouseEdit,
                                meta: {
                                    remember: true,
                                },
                            }
                        ]
                    }
                ]
            },
            {
                path: "/dashboard/product",
                children: [
                    {
                        path: "/dashboard/product/product-category",
                        name: "side-menu-product-product-category",
                        redirect: "/dashboard/product/product-category/list",
                        component: ProductCategoryIndex,
                        children: [
                            {
                                path: "/dashboard/product/product-category/list",
                                name: "side-menu-product-product-category-list",
                                component: ProductCategoryList,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/product/product-category/create",
                                name: "side-menu-product-product-category-create",
                                component: ProductCategoryCreate,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/product/product-category/edit/:ulid",
                                name: "side-menu-product-product-category-edit",
                                component: ProductCategoryEdit,
                                meta: {
                                    remember: true,
                                },
                            }
                        ]
                    },
                    {
                        path: "/dashboard/product/brand",
                        name: "side-menu-product-brand",
                        redirect: "/dashboard/product/brand/list",
                        component: BrandIndex,
                        children: [
                            {
                                path: "/dashboard/product/brand/list",
                                name: "side-menu-product-brand-list",
                                component: BrandList,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/product/brand/create",
                                name: "side-menu-product-brand-create",
                                component: BrandCreate,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/product/brand/edit/:ulid",
                                name: "side-menu-product-brand-edit",
                                component: BrandEdit,
                                meta: {
                                    remember: true,
                                },
                            }
                        ]
                    },
                    {
                        path: "/dashboard/product/unit",
                        name: "side-menu-product-unit",
                        redirect: "/dashboard/product/unit/list",
                        component: UnitIndex,
                        children: [
                            {
                                path: "/dashboard/product/unit/list",
                                name: "side-menu-product-unit-list",
                                component: UnitList,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/product/unit/create",
                                name: "side-menu-product-unit-create",
                                component: UnitCreate,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/product/unit/edit/:ulid",
                                name: "side-menu-product-unit-edit",
                                component: UnitEdit,
                                meta: {
                                    remember: true,
                                },
                            }
                        ]
                    },
                    {
                        path: "/dashboard/product/product",
                        name: "side-menu-product-product",
                        redirect: "/dashboard/product/product/list",
                        component: ProductIndex,
                        children: [
                            {
                                path: "/dashboard/product/product/list",
                                name: "side-menu-product-product-list",
                                component: ProductList,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/product/product/create",
                                name: "side-menu-product-product-create",
                                component: ProductCreate,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/product/product/edit/:ulid",
                                name: "side-menu-product-product-edit",
                                component: ProductEdit,
                                meta: {
                                    remember: true,
                                },
                            }
                        ]
                    },
                    {
                        path: "/dashboard/product/product-service",
                        name: "side-menu-product-product-service",
                        redirect: "/dashboard/product/product-service/list",
                        component: ProductServiceIndex,
                        children: [
                            {
                                path: "/dashboard/product/product-service/list",
                                name: "side-menu-product-product-service-list",
                                component: ProductServiceList,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/product/product-service/create",
                                name: "side-menu-product-product-service-create",
                                component: ProductServiceCreate,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/product/product-service/edit/:ulid",
                                name: "side-menu-product-product-service-edit",
                                component: ProductServiceEdit,
                                meta: {
                                    remember: true,
                                },
                            }
                        ]
                    }
                ]
            },
            {
                path: "/dashboard/finance",
                children: [
                    {
                        path: "/dashboard/finance/investor",
                        name: "side-menu-company-investor",
                        redirect: "/dashboard/finance/investor/list",
                        component: InvestorIndex,
                        children: [
                            {
                                path: "/dashboard/finance/investor/list",
                                name: "side-menu-company-investor-list",
                                component: InvestorList,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/finance/investor/create",
                                name: "side-menu-company-investor-create",
                                component: InvestorCreate,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/finance/investor/edit/:ulid",
                                name: "side-menu-company-investor-edit",
                                component: InvestorEdit,
                                meta: {
                                    remember: true,
                                },
                            }
                        ]
                    },
                    {
                        path: "/dashboard/finance/cash-account",
                        name: "side-menu-finance-cash-account",
                        redirect: "/dashboard/finance/cash-account/list",
                        component: CashAccountIndex,
                        children: [
                            {
                                path: "/dashboard/finance/cash-account/list",
                                name: "side-menu-finance-cash-account-list",
                                component: CashAccountList,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/finance/cash-account/create",
                                name: "side-menu-finance-cash-account-create",
                                component: CashAccountCreate,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/finance/cash-account/edit/:ulid",
                                name: "side-menu-finance-cash-account-edit",
                                component: CashAccountEdit,
                                meta: {
                                    remember: true,
                                },
                            }
                        ]
                    }
                ]
            },
            // Stock Adjustment Category
            {
                path: "/dashboard/stock-adjustment",
                children: [
                    {
                        path: "/dashboard/stock-adjustment/stock-adjustment-category",
                        name: "side-menu-stock-adjustment-stock-adjustment-category",
                        redirect: "/dashboard/stock-adjustment/stock-adjustment-category/list",
                        component: StockAdjustmentCategoryIndex,
                        children: [
                            {
                                path: "/dashboard/stock-adjustment/stock-adjustment-category/list",
                                name: "side-menu-stock-adjustment-stock-adjustment-category-list",
                                component: StockAdjustmentCategoryList,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/stock-adjustment/stock-adjustment-category/create",
                                name: "side-menu-stock-adjustment-stock-adjustment-category-create",
                                component: StockAdjustmentCategoryCreate,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/stock-adjustment/stock-adjustment-category/edit/:ulid",
                                name: "side-menu-stock-adjustment-stock-adjustment-category-edit",
                                component: StockAdjustmentCategoryEdit,
                                meta: {
                                    remember: true,
                                },
                            },
                        ],
                    },
                ],
            },

            {
                path: "/dashboard/customer",
                children: [
                    {
                        path: "/dashboard/customer/customer-group",
                        name: "side-menu-customer-group",
                        redirect: "/dashboard/customer/customer-group/list",
                        component: CustomerGroupIndex,
                        children: [
                            {
                                path: "/dashboard/customer/customer-group/list",
                                name: "side-menu-customer-group-list",
                                component: CustomerGroupList,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/customer/customer-group/create",
                                name: "side-menu-customer-group-create",
                                component: CustomerGroupCreate,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/customer/customer-group/edit/:ulid",
                                name: "side-menu-customer-group-edit",
                                component: CustomerGroupEdit,
                                meta: {
                                    remember: true,
                                },
                            }
                        ]
                    },
                    {
                        path: "/dashboard/customer/customer",
                        name: "side-menu-customer",
                        redirect: "/dashboard/customer/customer/list",
                        component: CustomerIndex,
                        children: [
                            {
                                path: "/dashboard/customer/customer/list",
                                name: "side-menu-customer-list",
                                component: CustomerList,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/customer/customer/create",
                                name: "side-menu-customer-create",
                                component: CustomerCreate,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/customer/customer/edit/:ulid",
                                name: "side-menu-customer-edit",
                                component: CustomerEdit,
                                meta: {
                                    remember: true,
                                },
                            },
                        ]
                    }
                ]
            },

            {
                path: "/dashboard/administrator",
                name: "side-menu-administrator",
                children: [
                    {
                        path: "/dashboard/administrator/user",
                        name: "side-menu-administrator-user",
                        redirect: "/dashboard/administrator/user/list",
                        component: UserIndex,
                        children: [
                            {
                                path: "/dashboard/administrator/user/list",
                                name: "side-menu-administrator-user-list",
                                component: UserList,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/administrator/user/create",
                                name: "side-menu-administrator-user-create",
                                component: UserCreate,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/administrator/user/edit/:ulid",
                                name: "side-menu-administrator-user-edit",
                                component: UserEdit,
                                meta: {
                                    remember: true,
                                },
                            }
                        ]
                    }

                ]
            },
            {
                path: "/dashboard/devtool",
                name: "side-menu-devtool",
                children: [
                    {
                        path: "/dashboard/devtool/devtool",
                        name: "side-menu-devtool-devtool",
                        component: DevTool,
                        meta: {
                            remember: false,
                        },
                    },
                    {
                        path: "/dashboard/devtool/playground",
                        name: "side-menu-devtool-playground",
                        children: [
                            {
                                path: "/dashboard/devtool/playground/p1",
                                name: "side-menu-devtool-playground-p1",
                                component: PlayOne,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/devtool/playground/p2",
                                name: "side-menu-devtool-playground-p2",
                                component: PlayTwo,
                                meta: {
                                    remember: true,
                                },
                            }
                        ]
                    }
                ]
            },
            {
                path: "/dashboard/error" + "/:code",
                name: "side-menu-error-code",
                component: ErrorView,
                meta: {
                    remember: false,
                },
            }
        ],
    },
    {
        path: "/:pathMatch(.*)*",
        component: ErrorPage,
        meta: {
            remember: false,
        },
    },
    {
        path: "/error-page",
        name: "error-page",
        component: ErrorPage,
        meta: {
            remember: false,
        },
    }
];
