import SideMenu from "@/layouts/side-menu/Main.vue";
import ChartOfAccount from "@/views/finance/ChartOfAccount.vue";

const root = '/dashboard';

export default {
    path: root + '/finance',
    component: SideMenu,
    children: [
        {
            path: root + '/finance' + '/chart_of_account',
            name: 'side-menu-finance-chart_of_account',
            component: ChartOfAccount,
            meta: { 
                remember: true,
                log_route: true
            }
        },
    ]
};