import SideMenu from "../layouts/SideMenu/SideMenu.vue";

import Login from "../pages/auth/Login.vue";
import Register from "../pages/auth/Register.vue";
import MainDashboard from "../pages/dashboard/MainDashboard.vue";
import Profile from "../pages/dashboard/Profile.vue";
import DevTool from "../pages/dev/DevTool.vue";
import Play1 from "../pages/dev/Play1.vue";
import Play2 from "../pages/dev/Play2.vue";
import Error from "../pages/dashboard/Error.vue";
import ErrorPage from "../pages/error/ErrorPage.vue";
import User from "../pages/administrator/User.vue";

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
                component: Login,
            },
            {
                path: "/auth/register",
                name: 'register',
                component: Register,
            },
            {
                path: "/auth/password-reset",
                name: 'password-reset',
                component: Register,
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
                component: Profile,
                meta: { 
                    remember: true,
                    log_route: true,
                    skipBeforeEach: false
                }
            },
            {
                path: "/dashboard/administrator",
                name: "side-menu-administrator",
                children: [
                    {
                        path: "/dashboard/administrator/user",
                        name: "side-menu-administrator-user",
                        component: User
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
                                component: Play1
                            },
                            {
                                path: "/dashboard/devtool/playground/p2",
                                name: "side-menu-devtool-playground-p2",
                                component: Play2
                            }
                        ]
                    }
                ]
            },
            {
                path: "/dashboard/error" + "/:code",
                name: "side-menu-error-code",
                component: Error,
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
  