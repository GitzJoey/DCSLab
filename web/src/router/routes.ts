import SideMenu from "../layouts/SideMenu/SideMenu.vue";

import LoginView from "../pages/auth/LoginView.vue";
import RegisterView from "../pages/auth/RegisterView.vue";
import ResetPasswordView from "../pages/auth/ResetPasswordView.vue";
import MainDashboard from "../pages/dashboard/MainDashboard.vue";
import ProfileView from "../pages/dashboard/ProfileView.vue";
import DevTool from "../pages/dev/DevTool.vue";
import PlayOne from "../pages/dev/PlayOne.vue";
import PlayTwo from "../pages/dev/PlayTwo.vue";
import ErrorView from "../pages/dashboard/ErrorView.vue";
import ErrorPage from "../pages/error/ErrorPage.vue";
import UserView from "../pages/administrator/UserView.vue";
import CompanyView from "../pages/company/CompanyView.vue";
import BranchView from "../pages/branch/BranchView.vue";
import EmployeeView from "../pages/employee/EmployeeView.vue";
import WarehouseView from "../pages/warehouse/WarehouseView.vue";
import ProductGroupView from "../pages/product_group/ProductGroupView.vue";
import BrandView from "../pages/brand/BrandView.vue";
import UnitView from "../pages/unit/UnitView.vue";
import ProductView from "../pages/product/ProductView.vue";
import SupplierView from "../pages/supplier/SupplierView.vue";
import CustomerGroupView from "../pages/customer_group/CustomerGroupView.vue";
import CustomerView from "../pages/customer/CustomerView.vue";
import PurchaseOrderView from "../pages/purchase_order/PurchaseOrderView.vue";

export default [
    {
        path: "/",
        redirect: "/auth/login",
    },
    {
        path: "/auth",
        children: [
            {
                path: "/auth/login",
                name: "login",
                component: LoginView,
            },
            {
                path: "/auth/register",
                name: 'register',
                component: RegisterView,
            },
            {
                path: "/auth/reset-password",
                name: 'reset-password',
                component: ResetPasswordView,
            },
        ]
    },
    {
        path: "/dashboard",
        component: SideMenu,
        children: [
            {
                path: "/dashboard/main",
                name: "side-menu-dashboard-maindashboard",
                component: MainDashboard,
                meta: {
                    remember: true,
                    log_route: true,
                    skipBeforeEach: false
                }
            },
            {
                path: "/dashboard/profile",
                name: "side-menu-dashboard-profile",
                component: ProfileView,
                meta: {
                    remember: true,
                    log_route: true,
                    skipBeforeEach: false
                }
            },
            {
                path: "/dashboard/company",
                children: [
                    {
                        path: "/dashboard/company/company",
                        name: "side-menu-company-company",
                        component: CompanyView
                    },
                    {
                        path: "/dashboard/company/branch",
                        name: "side-menu-company-branch",
                        component: BranchView
                    },
                    {
                        path: "/dashboard/company/employee",
                        name: "side-menu-company-employee",
                        component: EmployeeView
                    },
                    {
                        path: "/dashboard/company/warehouse",
                        name: "side-menu-company-warehouse",
                        component: WarehouseView
                    }
                ]
            },
            {
                path: "/dashboard/product",
                children: [
                    {
                        path: "/dashboard/product/product_group",
                        name: "side-menu-product-product_group",
                        component: ProductGroupView
                    },
                    {
                        path: "/dashboard/product/brand",
                        name: "side-menu-product-brand",
                        component: BrandView
                    },
                    {
                        path: "/dashboard/product/unit",
                        name: "side-menu-product-unit",
                        component: UnitView
                    },
                    {
                        path: "/dashboard/product/product",
                        name: "side-menu-product-product",
                        component: ProductView
                    }
                ]
            },
            {
                path: "/dashboard/supplier",
                children: [
                    {
                        path: "/dashboard/supplier/supplier",
                        name: "side-menu-supplier-supplier",
                        component: SupplierView
                    }
                ]
            },
            {
                path: "/dashboard/customer",
                children: [
                    {
                        path: "/dashboard/customer/customer_group",
                        name: "side-menu-customer-customer_group",
                        component: CustomerGroupView
                    },
                    {
                        path: "/dashboard/customer/customer",
                        name: "side-menu-customer-customer",
                        component: CustomerView
                    }
                ]
            },
            {
                path: "/dashboard/purchase_order",
                children: [
                    {
                        path: "/dashboard/purchase_order/purchase_order",
                        name: "side-menu-purchase_order-purchase_order",
                        component: PurchaseOrderView
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
                        component: UserView
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
                        component: DevTool
                    },
                    {
                        path: "/dashboard/devtool/playground",
                        name: "side-menu-devtool-playground",
                        children: [
                            {
                                path: "/dashboard/devtool/playground/p1",
                                name: "side-menu-devtool-playground-p1",
                                component: PlayOne
                            },
                            {
                                path: "/dashboard/devtool/playground/p2",
                                name: "side-menu-devtool-playground-p2",
                                component: PlayTwo
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
                    log_route: false,
                    skipBeforeEach: true
                }
            }
        ],
    },
    {
        path: "/:pathMatch(.*)*",
        component: ErrorPage
    },
    {
        path: "/error-page",
        name: "error-page",
        component: ErrorPage
    }
];
