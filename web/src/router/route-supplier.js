import SideMenu from "@/layouts/side-menu/Main.vue";
import Supplier from "@/views/supplier/Supplier.vue";

const root = '/dashboard';

export default {
    path: root + '/supplier',
    component: SideMenu,
    children: [
        {
            path: root + '/supplier' + '/supplier',
            name: 'side-menu-supplier-supplier',
            component: Supplier,
            meta: { 
                remember: true,
                log_route: true
            }
        }
    ]
};