import SideMenu from "../layouts/SideMenu/SideMenu.vue";

import LoginView from "../pages/auth/LoginView.vue";
import RegisterView from "../pages/auth/RegisterView.vue";
import MainDashboard from "../pages/dashboard/MainDashboard.vue";
import ProfileView from "../pages/dashboard/ProfileView.vue";
import DevTool from "../pages/dev/DevTool.vue";
import PlayOne from "../pages/dev/PlayOne.vue";
import PlayTwo from "../pages/dev/PlayTwo.vue";
import ErrorView from "../pages/dashboard/ErrorView.vue";
import ErrorPage from "../pages/error/ErrorPage.vue";
import UserView from "../pages/administrator/UserView.vue";
import CompanyView from "../pages/company/CompanyView.vue";

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
                path: "/auth/password-reset",
                name: 'password-reset',
                component: RegisterView,
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
  