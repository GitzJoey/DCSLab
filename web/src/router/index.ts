import { createRouter, createWebHistory } from "vue-router";
import SideMenu from "../layouts/SideMenu/SideMenu.vue";
import Page1 from "../pages/Page1.vue";
import Page2 from "../pages/Page2.vue";

import Login from "../pages/auth/Login.vue";

const routes = [
  {
    path: "/",
    redirect: "/login",
    children: [
      {
        path: "/login",
        name: "side-menu-page-1",
        component: Login,
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
