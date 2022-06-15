import SideMenu from "@/layouts/side-menu/Main.vue";
import Error from "@/views/error/Error.vue";

const root = '/dashboard';

export default {
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
                skipBeforeEach: true
            }
        }
    ]
}
