import Login from "@/views/auth/Login.vue";
import Register from "@/views/auth/Register.vue";
import ResetPassword from "@/views/auth/ResetPassword.vue";

function login() {
    return {
        path: "/login",
        name: "login",
        component: Login    
    };
}

function register() {
    return {
        path: "/register",
        name: "register",
        component: Register
    };
}

function resetPassword() {
    return {
        path: "/password/reset",
        name: "reset-password",
        component: ResetPassword
    }
}

export { login, register, resetPassword }