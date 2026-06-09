import Layout from "@/themes"

import LoginPage from "@/views/auth/Login.vue"
import RegisterPage from "@/views/auth/Register.vue"
import ForgotPasswordPage from "@/views/auth/ForgotPassword.vue"
import ResetPasswordPage from "@/views/auth/ResetPassword.vue"
import MainDashboard from "@/views/dashboard/MainDashboard.vue"

export default [
    {
        path: "/",
        redirect: "/dashboard/main",
    },
    {
        path: "/register",
        name: "register",
        component: RegisterPage,
        meta: { 
            public: true,
            persistLastRoute: false,
         },
    },
    {
        path: "/login",
        name: "login",
        component: LoginPage,
        meta: { 
            public: true,
            persistLastRoute: false,
         },
    },
    {
        path: "/dashboard",
        component: Layout,
        children: [
            {
                path: "/dashboard/main",
                name: "dashboard-maindashboard",
                component: MainDashboard,
            }
        ],
    }
];