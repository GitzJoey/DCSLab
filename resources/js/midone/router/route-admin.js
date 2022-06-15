import SideMenu from "@/layouts/side-menu/Main.vue";

import AdminUser from "@/views/administrator/User";

import DBBackup from "@/views/dev/DBBackup";
import Ex1 from "@/views/dev/Ex1";
import Ex2 from "@/views/dev/Ex2";

const root = '/dashboard';

function Admin() {
    return {
        path: root + '/admin',
        component: SideMenu,
        children: [
            {
                path: root + '/admin' + '/user',
                name: 'side-menu-administrator-user',
                component: AdminUser,
                meta: { 
                    remember: true,
                    roles: ['Administrator', 'Dev']
                }
            }
        ]    
    };
}

function DevTool() {
    return {
        path: root + '/dev',
        component: SideMenu,
        children: [
            {
                path: root + '/dev' + '/tools/db_backup',
                name: 'side-menu-devtool-backup',
                component: DBBackup,
                meta: { 
                    remember: true 
                }
            }
        ]    
    };
}

function Example() {
    return {
        path: root + '/dev' + '/example',
        name: 'side-menu-devtool-example',
        component: SideMenu,
        children: [
            {
                path: root + '/dev' + '/example' + '/ex1',
                name: 'side-menu-devtool-example-ex1',
                component: Ex1,
                meta: { 
                    remember: true 
                }
            },
            {
                path: root + '/dev' + '/example' + '/ex2',
                name: 'side-menu-devtool-example-ex2',
                component: Ex2,
                meta: { 
                    remember: true 
                }
            }
        ]    
    };
}

export default { Admin, DevTool, Example }