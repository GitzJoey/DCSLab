import { createRouter, createWebHistory } from "vue-router";
import r from "./routes";

const routes = r;

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    return savedPosition || { left: 0, top: 0 };
  },
});

export default router;
