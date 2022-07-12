import { createRouter, createWebHistory } from "vue-router";

import Root from "@/views/root/Main.vue";
import Login from "@/views/login/Main.vue";
import Register from "@/views/register/Main.vue";
import ErrorPage from "@/views/error-page/Main.vue";

import SideMenu from "@/layouts/side-menu/Main.vue";
import MainDashboard from "@/views/dashboard/MainDashboard.vue";

import RouteDashboard from "./route-dashboard";
import RouteError from "./route-error";

const routes = [
    {
        path: "/",
        name: "root",
        component: Root,
    },
    {
        path: "/login",
        name: "login",
        component: Login    
    },
    {
        path: "/register",
        name: "register",
        component: Register,
    },
    {
        path: '/dashboard',
        component: SideMenu,
        children: [
            {
                path: '/dashboard',
                name: 'side-menu-dashboard-maindashboard',
                component: MainDashboard,
                meta: {
                    remember: false,
                    log_route: true 
                }
            },
        ]
    },
    RouteError,
    {
        path: "/error-page",
        name: "error-page",
        component: ErrorPage,
    },
    {
        path: "/:pathMatch(.*)*",
        component: ErrorPage,
    },
];

const router = createRouter({
    history: createWebHistory(), 
    routes, 
    scrollBehavior(to, from, savedPosition) {
        return savedPosition || { left: 0, top: 0 };
    },
});

export default router;
  