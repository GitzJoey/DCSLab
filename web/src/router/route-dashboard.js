import SideMenu from "@/layouts/side-menu/Main.vue";

import MainDashboard from "@/views/dashboard/MainDashboard.vue";
import Profile from "@/views/dashboard/Profile.vue";
import Inbox from "@/views/dashboard/Inbox.vue";
import Activity from "@/views/dashboard/Activity.vue";
import Demo from "@/views/dashboard/Demo.vue";

const root = '/dashboard';

export default {
    path: root,
    component: SideMenu,
    children: [
        {
            path: root,
            name: 'side-menu-dashboard-maindashboard',
            component: MainDashboard,
            meta: {
                remember: false,
                log_route: true 
            }
        },
        {
            path: root + '/demo',
            name: 'side-menu-dashboard-demo',
            component: Demo,
            meta: {
                remember: false,
                log_route: false 
            }
        },
        {
            path: root + '/profile',
            name: 'side-menu-dashboard-profile',
            component: Profile,
            meta: { 
                remember: true,
                log_route: true 
            }
        },
        {
            path: root + '/inbox',
            name: 'side-menu-dashboard-inbox',
            component: Inbox,
            meta: { 
                remember: true,
                log_route: true 
            }
        },
        {
            path: root + '/activity',
            name: 'side-menu-dashboard-activity',
            component: Activity,
            meta: { 
                remember: true,
                log_route: true 
            }
        }
    ]
}