import SideMenu from "@/layouts/side-menu/Main.vue";
import ProductGroup from "@/views/product_group/ProductGroup.vue";
import Brand from "@/views/brand/Brand.vue";
import Product from "@/views/product/Product.vue";
import Service from "@/views/product/Service.vue";

const root = '/dashboard';

export default {
    path: root + '/product',
    component: SideMenu,
    children: [
        {
            path: root + '/product' + '/product_group',
            name: 'side-menu-product-product_group',
            component: ProductGroup,
            meta: {
                remember: true,
                log_route: true,
            },
        },
        {
            path: root + '/product' + '/brand',
            name: 'side-menu-product-brand',
            component: Brand,
            meta: {
                remember: true,
                log_route: true,
            },
        },
        {
            path: root + '/product' + '/product',
            name: 'side-menu-product-product',
            component: Product,
            meta: {
                remember: true,
                log_route: true
            },
        },
        {
            path: root + '/product' + '/service',
            name: 'side-menu-product-service',
            component: Service,
            meta: {
                remember: true,
                log_route: true
            },
        },
    ],
};