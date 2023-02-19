import { createRouter, createWebHistory } from "vue-router";
import SideMenu from "../layouts/SideMenu/SideMenu.vue";

import Login from "../pages/auth/Login.vue";
import Register from "../pages/auth/Register.vue";
import MainDashboard from "../pages/dashboard/MainDashboard.vue";
import Profile from "../pages/dashboard/Profile.vue";
import Error from "../pages/dashboard/Error.vue";
import ErrorPage from "../pages/error/ErrorPage.vue";

const routes = [
  {
    path: "/",
    redirect: "/auth/login",
  },
  {
    path: "/auth",
    children: [
      {
        path: "/auth/login",
        name: "login",
        component: Login,
      },
      {
        path: "/auth/register",
        name: 'register',
        component: Register,
      },
      {
        path: "/auth/password-reset",
        name: 'password-reset',
        component: Register,
      },
    ]
  },
  {
    path: "/dashboard",
    component: SideMenu,
    children: [
      {
        path: "/dashboard/main",
        name: "side-menu-dashboard-maindashboard",
        component: MainDashboard,
        meta: { 
          remember: true,
          log_route: true,
          skipBeforeEach: false
        }
      },
      {
        path: "/dashboard/profile",
        name: "side-menu-dashboard-profile",
        component: Profile,
        meta: { 
          remember: true,
          log_route: true,
          skipBeforeEach: false
        }
      },
      {
        path: "/dashboard/error" + "/:code",
        name: "side-menu-error-code",
        component: Error,
        meta: { 
          remember: false,
          log_route: false,
          skipBeforeEach: true
        }
      }
    ],
  },
  {
    path: "/:pathMatch(.*)*",
    component: ErrorPage
  },
  {
    path: "/error-page",
    name: "error-page",
    component: ErrorPage
  }
];

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
});

export default router;
