import Login from "@/views/login/Main.vue";
import Register from "@/views/register/Main.vue";

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
        component: Register,
    };
}

export { login, register }