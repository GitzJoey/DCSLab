import SideMenu from "@/layouts/side-menu/Main.vue";
import Product from "@/views/product/Product.vue";
import Service from "@/views/product/Service.vue";

const root = '/dashboard';

export default {
    path: root + '/product',
    component: SideMenu,
    children: [
        {
            path: root + '/product' + '/product',
            name: 'side-menu-product-product',
            component: Product,
            meta: { 
                remember: true,
                log_route: true
            }
        },
        {
            path: root + '/product' + '/service',
            name: 'side-menu-product-service',
            component: Service,
            meta: { 
                remember: true,
                log_route: true 
            }
        }
    ]
};
