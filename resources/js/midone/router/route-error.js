import SideMenu from "@/layouts/side-menu/Main.vue";
import Error403 from "@/views/error/403.vue";

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
                remember: false 
            }
        }
    ]
}
