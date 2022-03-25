import SideMenu from "@/layouts/side-menu/Main.vue";
import Error403 from "@/views/error/403.vue";
import Error401 from "@/views/error/403.vue";

const root = '/dashboard';

export default {
    path: root + '/error',
    name: 'side-menu-error',
    component: SideMenu,
    children: [
        {
            path: root + '/error' + '/403',
            name: 'side-menu-error-403',
            component: Error403,
            meta: { 
                remember: false,
                log_route: false,
                skipBeforeEach: true
            }
        },
        {
            path: root + '/error' + '/401',
            name: 'side-menu-error-401',
            component: Error401,
            meta: { 
                remember: false,
                log_route: false,
                skipBeforeEach: true
            }
        }
    ]
}
