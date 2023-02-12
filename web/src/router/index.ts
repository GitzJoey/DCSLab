import { createRouter, createWebHistory } from "vue-router";
import SideMenu from "../layouts/SideMenu/SideMenu.vue";

import Login from "../pages/auth/Login.vue";
import MainDashboard from "../pages/dashboard/MainDashboard.vue";

const routes = [
  {
    path: "/",
    redirect: "/login",
    children: [
      {
        path: "/login",
        name: "login",
        component: Login,
      }
    ],
  },
  {
    path: "/dashboard",
    component: SideMenu,
    children: [
      {
        path: "/dashboard/main",
        name: "side-menu-dashboard-maindashboard",
        component: MainDashboard,
      }
    ],
  }
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    return savedPosition || { left: 0, top: 0 };
  },
});

export default router;
