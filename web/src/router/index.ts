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

router.beforeEach(async (to, from, next) => {
  next();
});

router.afterEach((to, from) => {
  if (to.matched.some(r => r.meta.remember)) {
    sessionStorage.setItem('DCSLAB_LAST_ROUTE', to.name as string);
  }
});

export default router;
