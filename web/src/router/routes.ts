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
                        redirect: "/dashboard/company/company/list",
                        component: CompanyIndex,
                        children: [
                            {
                                path: "/dashboard/company/company/list",
                                name: "side-menu-company-company-list",
                                component: CompanyList,
                            },
                            {
                                path: "/dashboard/company/company/create",
                                name: "side-menu-company-company-create",
                                component: CompanyCreate,
                            },
                            {
                                path: "/dashboard/company/company/edit/:ulid",
                                name: "side-menu-company-company-edit",
                                component: CompanyEdit,
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
                            },
                            {
                                path: "/dashboard/company/branch/create",
                                name: "side-menu-company-branch-create",
                                component: BranchCreate,
                            },
                            {
                                path: "/dashboard/company/branch/edit/:ulid",
                                name: "side-menu-company-branch-edit",
                                component: BranchEdit,
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
                            },
                            {
                                path: "/dashboard/administrator/user/create",
                                name: "side-menu-administrator-user-create",
                                component: UserCreate,
                            },
                            {
                                path: "/dashboard/administrator/user/edit/:ulid",
                                name: "side-menu-administrator-user-edit",
                                component: UserEdit,
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
    /*
    {
        path: "/:pathMatch(.*)*",
        component: ErrorPage
    },
    {
        path: "/error-page",
        name: "error-page",
        component: ErrorPage
    }
    */
];
