import Layout from '@/themes'

import Login from '@/views/auth/Login.vue'
import Register from '@/views/auth/Register.vue'
import ForgotPassword from '@/views/auth/ForgotPassword.vue'
import ResetPassword from '@/views/auth/ResetPassword.vue'
import MainDashboard from '@/views/dashboard/MainDashboard.vue'
import Error from '@/views/error/ErrorPage.vue'

import Company from '@/views/company/Company.vue'
import Branch from '@/views/branch/Branch.vue'
import User from '@/views/user/User.vue'
import PlayOne from '@/views/devtools/PlayOne.vue'
import PlayTwo from '@/views/devtools/PlayTwo.vue'

export default [
  {
    path: '/',
    redirect: '/dashboard/main',
  },
  {
    path: '/auth',
    redirect: { name: 'login' },
    meta: {
      public: true,
    },
    children: [
      {
        path: 'register',
        name: 'register',
        component: Register,
        meta: {
          public: true,
        },
      },
      {
        path: 'login',
        name: 'login',
        component: Login,
        meta: {
          public: true,
        },
      },
      {
        path: 'forgot-password',
        name: 'forgot-password',
        component: ForgotPassword,
        meta: {
          public: true,
        },
      },
      {
        path: 'reset-password',
        name: 'reset-password',
        component: ResetPassword,
        meta: {
          public: true,
        },
      },
    ],
  },
  {
    path: '/dashboard',
    component: Layout,
    redirect: '/dashboard/main',
    children: [
      {
        path: 'main',
        name: 'dashboard-maindashboard',
        component: MainDashboard,
      },
      {
        path: 'organization',
        name: 'dashboard-organization',
        redirect: '/dashboard/organization',
        children: [
          {
            path: 'company',
            name: 'dashboard-organization-company',
            component: Company,
          },
          {
            path: 'branch',
            name: 'dashboard-organization-branch',
            component: Branch,
          },
        ],
      },
      {
        path: 'administrator',
        name: 'dashboard-administrator',
        redirect: '/dashboard/administrator/user',
        children: [
          {
            path: 'user',
            name: 'dashboard-administrator-user',
            component: User,
          },
          {
            path: 'devtools',
            name: 'dashboard-administrator-devtools',
            redirect: '/dashboard/administrator/devtools/play/one',
            children: [
              {
                path: 'play/one',
                name: 'dashboard-administrator-devtools-playone',
                component: PlayOne,
              },
              {
                path: 'play/two',
                name: 'dashboard-administrator-devtools-playtwo',
                component: PlayTwo,
              },
            ],
          },
        ],
      },
    ],
  },
  {
    path: '/error-page',
    name: 'error-page',
    component: Error,
    meta: {
      public: true,
    },
  },
  {
    path: '/:pathMatch(.*)*',
    redirect: '/error-page',
  },
]
