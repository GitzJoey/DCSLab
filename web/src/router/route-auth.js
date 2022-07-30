import Login from "@/views/auth/Login.vue";
import Register from "@/views/auth/Register.vue";
import PasswordReset from "@/views/auth/PasswordReset.vue";

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
        path: "/password-reset/:token?",
        name: "password-reset",
        component: PasswordReset
    }
}

export { login, register, resetPassword }