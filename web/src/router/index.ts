import { createRouter, createWebHistory } from 'vue-router'
import r from './routes'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: r,
})

router.beforeEach(async (to, from, next) => {
  next();
});

router.afterEach((to, from) => {
  if (to.matched.some(r => r.meta.remember)) {
    sessionStorage.setItem('DCSLAB_LAST_ROUTE', to.name as string);
  }
});

export default router
