import { createRouter, createWebHistory } from 'vue-router'
import r from './routes'
import { persistLastRoute } from '@/middleware/persistLastRoute'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: r,
})

router.afterEach((to, from) => {
  persistLastRoute(to);
})

export default router
