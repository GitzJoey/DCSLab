import { createRouter, createWebHistory } from 'vue-router'
import r from './routes'
import { persistLastRoute } from '@/middleware/persistLastRoute'
import { validateUser } from '@/middleware/validateUser'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: r,
})

router.beforeEach(validateUser);

router.afterEach((to, from) => {
  persistLastRoute(to);
})

export default router
