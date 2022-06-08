import SideMenu from "@/layouts/side-menu/Main.vue";

import Company from "@/views/company/Company.vue";
import Branch from "@/views/branch/Branch.vue";
import Warehouse from "@/views/warehouse/Warehouse.vue";

const root = '/dashboard';

export default {
    path: root + '/company',
    component: SideMenu,
    children: [
        {
            path: root + '/company' + '/company',
            name: 'side-menu-company-company',
            component: Company,
            meta: { 
                remember: true,
                log_route: true 
            }
        },
        {
            path: root + '/company' + '/branch',
            name: 'side-menu-company-branch',
            component: Branch,
            meta: { 
                remember: true,
                log_route: true 
            }
        },
        {
            path: root + '/company' + '/warehouse',
            name: 'side-menu-company-warehouse',
            component: Warehouse,
            meta: { 
                remember: true,
                log_route: true
            }
        }
    ]
};