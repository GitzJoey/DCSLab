import { createRouter, createWebHistory } from "vue-router";
import multiguard from "vue-router-multiguard";
import * as guards from "@/router/guards";
import axios from "@/axios";

import { useUserContextStore } from "@/stores/user-context";

import Root from "@/views/root/Main.vue";

import * as RouteAuth from "./route-auth";
import RouteDashboard from "./route-dashboard";
import RouteCompany from "./route-company";
import RouterFinance from "./route-finance";
import RouteProduct from "./route-product";
import RouteSupplier from "./route-supplier";
import RoutePurchaseOrder from "./route-purchaseorder";
import RouteAdministrator from "./route-admin";
import * as RouteError from "./route-error";

const routes = [
    {
        path: "/",
        name: "root",
        component: Root
    },
];

routes.push(RouteAuth.login());
routes.push(RouteAuth.register());
routes.push(RouteAuth.resetPassword());
routes.push(RouteDashboard);
routes.push(RouteCompany);
routes.push(RouterFinance);
routes.push(RouteProduct);
routes.push(RouteSupplier);
routes.push(RoutePurchaseOrder);
routes.push(RouteAdministrator.Admin())
routes.push(RouteAdministrator.DevTool())
routes.push(RouteAdministrator.Example())
routes.push(RouteError.dashboardError());
routes.push(RouteError.pageError());
routes.push(RouteError.anyMatch());

const router = createRouter({
    history: createWebHistory(), 
    routes, 
    scrollBehavior(to, from, savedPosition) {
        return savedPosition || { left: 0, top: 0 };
    }
});

router.beforeEach(async (to, from, next) => {
    if (to.matched.some(r => r.meta.skipBeforeEach) && to.meta.skipBeforeEach) {
        next();
        return;
    }

    const userContextStore = useUserContextStore();
    if (userContextStore.userContext.name === undefined) {
        next();
        return;
    }

    multiguard([
        guards.userHasRoles(to, userContextStore.userContext, next),
        guards.userHasPermissions(to, userContextStore.userContext, next)
    ]);

    next();
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
  