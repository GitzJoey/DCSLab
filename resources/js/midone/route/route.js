import { createRouter, createWebHistory } from 'vue-router';

import SideMenu from '../layouts/side-menu/Main.vue';

import MainDashboard from '../views/dashboard/MainDashboard.vue';
import AdminUsers from "../views/administrators/Users";
import AdminRoles from "../views/administrators/Roles";

import DBBackup from "../views/dev/DBBackup";
import Ex1 from "../views/dev/Ex1";
import Ex2 from "../views/dev/Ex2";

const root = '/dashboard';

const routes = [
    {
        path: root,
        component: SideMenu,
        children: [
            {
                path: root,
                name: 'side-menu-dashboard-maindashboard',
                component: MainDashboard
            },
        ],
    },
    {
        path: root + '/admin',
        component: SideMenu,
        children: [
            {
                path: root + '/admin' + '/users/users',
                name: 'side-menu-administrators-users',
                component: AdminUsers
            },
            {
                path: root + '/admin' + '/users/roles',
                name: 'side-menu-administrators-roles',
                component: AdminRoles
            }
        ],
    },
    {
        path: root + '/dev',
        component: SideMenu,
        children: [
            {
                path: root + '/dev' + '/tools/db_backup',
                name: 'side-menu-devtools-backup',
                component: DBBackup
            }
        ],
    },
    {
        path: root + '/dev' + '/examples',
        name: 'side-menu-devtools-examples',
        component: SideMenu,
        children: [
            {
                path: root + '/dev' + '/examples' + '/ex1',
                name: 'side-menu-devtools-examples-ex1',
                component: Ex1,
            },
            {
                path: root + '/dev' + '/examples' + '/ex2',
                name: 'side-menu-devtools-examples-ex2',
                component: Ex2,
            }
        ]
    },
];

const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes,
});

export default router;
