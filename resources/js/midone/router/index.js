import { createRouter, createWebHistory } from "vue-router";
import multiguard from "vue-router-multiguard";
import * as guards from "@/router/guards";
import axios from "@/axios";

import { useUserContextStore } from "@/stores/user-context";

import RouteDashboard from "./route-dashboard";
import RouteAdmin from "./route-admin";

import RouteCompany from "./route-company";
import RouteSupplier from "./route-supplier";
import RouteProduct from "./route-product";
import RoutePurchaseOrder from "./route-purchaseorder";
import RouteError from "./route-error";

const routes = [
    RouteDashboard,
    RouteCompany,
    RouteSupplier,
    RouteProduct,
    RoutePurchaseOrder,
    RouteAdmin.Admin(),
    RouteAdmin.DevTool(),
    RouteAdmin.Example(),
    RouteError
];

const router = createRouter({
    history: createWebHistory(process.env.BASE_URL),
    routes,
    scrollBehavior(to, from, savedPosition) {
        return savedPosition || { left: 0, top: 0 };
    },
});

router.beforeEach(async (to, from) => {
    const userContextStore = useUserContextStore();
    if (userContextStore.userContext.name !== undefined) {
        multiguard([
            guards.checkPasswordExpiry(userContextStore.userContext), 
            guards.checkUserStatus(userContextStore.userContext)
        ]);
    }
});

router.afterEach((to, from) => {
    if (to.matched.some(r => r.meta.log_route)) {
        axios.post('/api/post/dashboard/core/activity/log/route', {
            to: to.name,
            params: to.params
        }).catch(e => { });    
    }

    if (to.matched.some(r => r.meta.remember))
        sessionStorage.setItem('DCSLAB_LAST_ROUTE', to.name);
});

export default router;
