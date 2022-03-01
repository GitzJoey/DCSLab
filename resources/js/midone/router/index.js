import { createRouter, createWebHistory } from "vue-router";

import SideMenu from "../layouts/side-menu/Main.vue";

import MainDashboard from "@/views/dashboard/MainDashboard.vue";

const root = '/dashboard';

const routes = [
    {
        path: root,
        component: SideMenu,
        children: [
            {
                path: root,
                name: 'side-menu-dashboard-maindashboard',
                component: MainDashboard,
                meta: {
                    middleware: ['canUserAccess'], 
                    remember: false 
                }
            }
        ]
    },
];

const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    return savedPosition || { left: 0, top: 0 };
  },
});

router.beforeEach(async (to, from) => {
  /*
  if (to.matched.some(r => r.meta.middleware)) {
      if (to.meta.middleware.includes('canUserAccess')) {
          const canAccess = await canUserAccess(to);
          if (!canAccess) return '/error/403';
      }
  }
  */
});

router.afterEach((to, from) => {
  /*
  axios.post('/api/post/dashboard/core/activity/log/route', {
      to: to.name,
      params: to.params
  }).catch(e => { });

  if (to.matched.some(r => r.meta.remember))
      sessionStorage.setItem('DCSLAB_LAST_ROUTE', to.name);
  */
});

export default router;
