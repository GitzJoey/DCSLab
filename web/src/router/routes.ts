import SideMenu from "../layouts/SideMenu/SideMenu.vue";

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
import WarehouseIndex from "../pages/warehouse/WarehouseIndex.vue";
import WarehouseList from "../pages/warehouse/WarehouseList.vue";
import WarehouseCreate from "../pages/warehouse/WarehouseCreate.vue";
import WarehouseEdit from "../pages/warehouse/WarehouseEdit.vue";
import ProductGroupIndex from "../pages/product_group/ProductGroupIndex.vue";
import ProductGroupList from "../pages/product_group/ProductGroupList.vue";
import ProductGroupCreate from "../pages/product_group/ProductGroupCreate.vue";
import ProductGroupEdit from "../pages/product_group/ProductGroupEdit.vue";
import BrandIndex from "../pages/brand/BrandIndex.vue";
import BrandList from "../pages/brand/BrandList.vue";
import BrandCreate from "../pages/brand/BrandCreate.vue";
import BrandEdit from "../pages/brand/BrandEdit.vue";
import UnitIndex from "../pages/unit/UnitIndex.vue";
import UnitList from "../pages/unit/UnitList.vue";
import UnitCreate from "../pages/unit/UnitCreate.vue";
import UnitEdit from "../pages/unit/UnitEdit.vue";

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
        component: SideMenu,
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
                    },
                ]
            },
            {
                path: "/dashboard/product",
                children: [
                    {
                        path: "/dashboard/product/product_group",
                        name: "side-menu-product-product_group",
                        redirect: "/dashboard/product/product_group/list",
                        component: ProductGroupIndex,
                        children: [
                            {
                                path: "/dashboard/product/product_group/list",
                                name: "side-menu-product-product_group-list",
                                component: ProductGroupList,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/product/product_group/create",
                                name: "side-menu-product-product_group-create",
                                component: ProductGroupCreate,
                                meta: {
                                    remember: true,
                                },
                            },
                            {
                                path: "/dashboard/product/product_group/edit/:ulid",
                                name: "side-menu-product-product_group-edit",
                                component: ProductGroupEdit,
                                meta: {
                                    remember: true,
                                },
                            }
                        ]
                    },
                ]
            },            
            {
                path: "/dashboard/product",
                children: [
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
                ]
            },
            {
                path: "/dashboard/product",
                children: [
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
