import { createRouter, createWebHistory } from "vue-router";
import SideMenu from "../layouts/side-menu/Main.vue";

const routes = [
    {
        path: "/",
        component: SideMenu,
        children: [
            {
                path: "/",
                name: "side-menu-dashboard-overview-1",
                component: DashboardOverview1
            },
        ]
    }
];

const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes,
    scrollBehavior(to, from, savedPosition) {
        return savedPosition || { left: 0, top: 0 };
    }
});

export default router;

