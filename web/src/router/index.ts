import { createRouter, createWebHistory } from 'vue-router';
import { Config } from 'ziggy-js';
import r from './routes';
import DashboardService from '@/services/DashboardService';
import { useZiggyRouteStore } from '@/stores/ziggy-route';

const routes = r;

const router = createRouter({
  history: createWebHistory(),
  routes,
});

const publicRouteNames = ['login', 'register', 'forgot-password', 'reset-password'];

let pendingZiggyRequest: Promise<void> | null = null;

// The Ziggy route list lives in sessionStorage, which is empty in a freshly
// opened tab. Without this, a page can call route() before the side menu has
// fetched the list and fail with "route is not in the route list".
const ensureZiggyLoaded = async (): Promise<void> => {
  if (sessionStorage.getItem('ziggyRoute')) return;

  if (!pendingZiggyRequest) {
    pendingZiggyRequest = (async () => {
      const dashboardService = new DashboardService();
      const result = await dashboardService.readUserApi();

      if (result.success && result.data) {
        useZiggyRouteStore().setZiggy(result.data as Config);
      }
    })().finally(() => {
      pendingZiggyRequest = null;
    });
  }

  await pendingZiggyRequest;
};

router.beforeEach(async (to, from, next) => {
  if (!publicRouteNames.includes(to.name as string)) {
    await ensureZiggyLoaded();
  }

  next();
});

router.afterEach((to, from) => {
  if (to.matched.some((r) => r.meta.remember)) {
    sessionStorage.setItem('DCSLAB_LAST_ROUTE', to.name as string);
  }
});

export default router;
