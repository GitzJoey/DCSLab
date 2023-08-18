import SideMenu from "../layouts/SideMenu/SideMenu.vue";

import LoginPage from "../pages/auth/LoginPage.vue";
//import RegisterPage from "../pages/auth/RegisterPage.vue";
//import ForgotPasswordPage from "../pages/auth/ForgotPasswordPage.vue";
//import ResetPasswordPage from "../pages/auth/ResetPasswordPage.vue";
//import MainDashboard from "../pages/dashboard/MainDashboard.vue";
//import ProfileView from "../pages/dashboard/ProfileView.vue";
//import DevTool from "../pages/dev/DevTool.vue";
//import PlayOne from "../pages/dev/PlayOne.vue";
//import PlayTwo from "../pages/dev/PlayTwo.vue";
//import ErrorView from "../pages/dashboard/ErrorView.vue";
//import ErrorPage from "../pages/error/ErrorPage.vue";
//import UserView from "../pages/administrator/UserView.vue";
//import CompanyView from "../pages/company/CompanyView.vue";
//import BranchView from "../pages/branch/BranchView.vue";

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
                component: LoginPage,
            },
            /*
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
            */
        ]
    },
    /*
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
    */
];
