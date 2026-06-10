import Layout from "@/themes"

import Login from "@/views/auth/Login.vue"
import Register from "@/views/auth/Register.vue"
import ForgotPassword from "@/views/auth/ForgotPassword.vue"
import ResetPassword from "@/views/auth/ResetPassword.vue"
import MainDashboard from "@/views/dashboard/MainDashboard.vue"
import Error from "@/views/error/ErrorPage.vue"

export default [
    {
        path: "/",
        redirect: "/dashboard/main",
    },
    {
        path: "/auth",
        redirect: { name: "login" },
        meta: {
            public: true,
        },
        children: [
            {
                path: "register",
                name: "register",
                component: Register,
                meta: { 
                    public: true,
                },
            },
            {
                path: "login",
                name: "login",
                component: Login,
                meta: { 
                    public: true,
                },
            },
            {
                path: "forgot-password",
                name: 'forgot-password',
                component: ForgotPassword,
                meta: { 
                    public: true,
                },
            },
            {
                path: "reset-password",
                name: 'reset-password',
                component: ResetPassword,
                meta: { 
                    public: true,
                },
            },
        ]
    },
    {
        path: "/dashboard",
        component: Layout,
        redirect: "/dashboard/main",
        children: [
            {
                path: "main",
                name: "dashboard-maindashboard",
                component: MainDashboard,
            }
        ],
    },
    {
        path: "/error-page",
        name: "error-page",
        component: Error,
        meta: { 
            public: true,
        },
    },
    {
        path: "/:pathMatch(.*)*",
        redirect: "/error-page",
    }
];