import SideMenu from "@/layouts/side-menu/Main.vue";
import Error from "@/views/error/Error.vue";
import ErrorPage from "@/views/error-page/Main.vue";

const root = '/dashboard';

function dashboardError() {
    return {
        path: root + '/error',
        name: 'side-menu-error',
        component: SideMenu,
        children: [
            {
                path: root + '/error' + '/:code',
                name: 'side-menu-error-code',
                component: Error,
                meta: { 
                    remember: false,
                    log_route: false,
                    skipBeforeEach: true
                }
            }
        ]
    };
}

function pageError() {
    return {
        path: "/error-page",
        name: "error-page",
        component: ErrorPage
    };
}

function anyMatch() {
    return {
        path: "/:pathMatch(.*)*",
        component: ErrorPage
    };
}

export { dashboardError, pageError, anyMatch } 
