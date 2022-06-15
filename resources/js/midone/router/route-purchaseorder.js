import SideMenu from "@/layouts/side-menu/Main.vue";
import PurchaseOrder from "@/views/purchase_order/PurchaseOrder.vue";

const root = '/dashboard';

export default {
    path: root + '/po',
    component: SideMenu,
    children: [
        {
            path: root + '/po' + '/po',
            name: 'side-menu-purchase_order-purchaseorder',
            component: PurchaseOrder,
            meta: { 
                remember: true,
                log_route: true 
            }
        }
    ]
};
