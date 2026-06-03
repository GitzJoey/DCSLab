import { type Icon } from '@/components/ui/lucide'

export interface Menu {
  icon?: Icon
  title?: string
  route_name?: string
  params?: any
  badge?: number
  sub_menu?: Menu[]
}

const mainMenu: (string | Menu)[] = [
  'GENERAL REPORTS',
  {
    icon: 'CircleGauge',
    title: 'Dashboards',
    badge: 4,
    sub_menu: [
      {
        icon: 'PanelBottomClose',
        route_name: 'dashboard-overview-1',
        title: 'Overview 1',
      },
      {
        icon: 'Disc3',
        route_name: 'dashboard-overview-2',
        title: 'Overview 2',
      },
      {
        icon: 'SquareActivity',
        route_name: 'dashboard-overview-3',
        title: 'Overview 3',
      },
      {
        icon: 'Album',
        route_name: 'dashboard-overview-4',
        title: 'Overview 4',
      },
    ],
  },
  {
    icon: 'SquareKanban',
    title: 'E-Commerce',
    badge: 2,
    sub_menu: [
      {
        icon: 'CircleGauge',
        route_name: 'categories',
        title: 'Categories',
      },
      {
        icon: 'CircleGauge',
        route_name: 'add-product',
        title: 'Add Product',
      },
      {
        icon: 'CircleGauge',
        title: 'Products',
        sub_menu: [
          {
            icon: 'CircleGauge',
            route_name: 'product-list',
            title: 'Product List',
          },
          {
            icon: 'CircleGauge',
            route_name: 'product-grid',
            title: 'Product Grid',
          },
        ],
      },
      {
        icon: 'CircleGauge',
        title: 'Transactions',
        sub_menu: [
          {
            icon: 'CircleGauge',
            route_name: 'transaction-list',
            title: 'Transaction List',
          },
          {
            icon: 'CircleGauge',
            route_name: 'transaction-detail',
            title: 'Transaction Detail',
          },
        ],
      },
      {
        icon: 'CircleGauge',
        title: 'Sellers',
        sub_menu: [
          {
            icon: 'CircleGauge',
            route_name: 'seller-list',
            title: 'Seller List',
          },
          {
            icon: 'CircleGauge',
            route_name: 'seller-detail',
            title: 'Seller Detail',
          },
        ],
      },
      {
        icon: 'CircleGauge',
        route_name: 'reviews',
        title: 'Reviews',
      },
    ],
  },
  'APPS',
  {
    icon: 'CircleGauge',
    route_name: 'inbox',
    title: 'Inbox',
  },
  {
    icon: 'CircleGauge',
    route_name: 'file-manager',
    title: 'File Manager',
    badge: 5,
  },
  {
    icon: 'CircleGauge',
    route_name: 'point-of-sale',
    title: 'Point of Sale',
  },
  {
    icon: 'CircleGauge',
    route_name: 'chat',
    title: 'Chat',
    badge: 3,
  },
  {
    icon: 'CircleGauge',
    route_name: 'post',
    title: 'Post',
  },
  // {
  //   icon: 'CircleGauge',
  //   route_name: 'calendar',
  //   title: 'Calendar',
  // },
  'PAGES',
  {
    icon: 'CircleGauge',
    title: 'Crud',
    sub_menu: [
      {
        icon: 'CircleGauge',
        route_name: 'crud-data-list',
        title: 'Data List',
      },
      {
        icon: 'CircleGauge',
        route_name: 'crud-form',
        title: 'Form',
      },
    ],
  },
  {
    icon: 'CircleGauge',
    title: 'Users',
    sub_menu: [
      {
        icon: 'CircleGauge',
        route_name: 'users-layout-1',
        title: 'Layout 1',
      },
      {
        icon: 'CircleGauge',
        route_name: 'users-layout-2',
        title: 'Layout 2',
      },
      {
        icon: 'CircleGauge',
        route_name: 'users-layout-3',
        title: 'Layout 3',
      },
    ],
  },
  {
    icon: 'CircleGauge',
    title: 'Profile',
    sub_menu: [
      {
        icon: 'CircleGauge',
        route_name: 'profile-overview-1',
        title: 'Overview 1',
      },
      {
        icon: 'CircleGauge',
        route_name: 'profile-overview-2',
        title: 'Overview 2',
      },
      {
        icon: 'CircleGauge',
        route_name: 'profile-overview-3',
        title: 'Overview 3',
      },
    ],
  },
  {
    icon: 'CircleGauge',
    title: 'Pages',
    sub_menu: [
      {
        icon: 'CircleGauge',
        title: 'Wizards',
        sub_menu: [
          {
            icon: 'CircleGauge',
            route_name: 'wizard-layout-1',
            title: 'Layout 1',
          },
          {
            icon: 'CircleGauge',
            route_name: 'wizard-layout-2',
            title: 'Layout 2',
          },
          {
            icon: 'CircleGauge',
            route_name: 'wizard-layout-3',
            title: 'Layout 3',
          },
        ],
      },
      {
        icon: 'CircleGauge',
        title: 'Blog',
        sub_menu: [
          {
            icon: 'CircleGauge',
            route_name: 'blog-layout-1',
            title: 'Layout 1',
          },
          {
            icon: 'CircleGauge',
            route_name: 'blog-layout-2',
            title: 'Layout 2',
          },
          {
            icon: 'CircleGauge',
            route_name: 'blog-layout-3',
            title: 'Layout 3',
          },
        ],
      },
      {
        icon: 'CircleGauge',
        title: 'Pricing',
        sub_menu: [
          {
            icon: 'CircleGauge',
            route_name: 'pricing-layout-1',
            title: 'Layout 1',
          },
          {
            icon: 'CircleGauge',
            route_name: 'pricing-layout-2',
            title: 'Layout 2',
          },
        ],
      },
      {
        icon: 'CircleGauge',
        title: 'Invoice',
        sub_menu: [
          {
            icon: 'CircleGauge',
            route_name: 'invoice-layout-1',
            title: 'Layout 1',
          },
          {
            icon: 'CircleGauge',
            route_name: 'invoice-layout-2',
            title: 'Layout 2',
          },
        ],
      },
      {
        icon: 'CircleGauge',
        title: 'FAQ',
        sub_menu: [
          {
            icon: 'CircleGauge',
            route_name: 'faq-layout-1',
            title: 'Layout 1',
          },
          {
            icon: 'CircleGauge',
            route_name: 'faq-layout-2',
            title: 'Layout 2',
          },
          {
            icon: 'CircleGauge',
            route_name: 'faq-layout-3',
            title: 'Layout 3',
          },
        ],
      },
      {
        icon: 'CircleGauge',
        route_name: 'login',
        title: 'Login',
      },
      {
        icon: 'CircleGauge',
        route_name: 'register',
        title: 'Register',
      },
      {
        icon: 'CircleGauge',
        route_name: 'error-page',
        title: 'Error Page',
      },
      {
        icon: 'CircleGauge',
        route_name: 'update-profile',
        title: 'Update profile',
      },
      {
        icon: 'CircleGauge',
        route_name: 'change-password',
        title: 'Change Password',
      },
    ],
  },
  'UI COMPONENTS',
  {
    icon: 'CircleGauge',
    title: 'Base',
    sub_menu: [
      {
        icon: 'CircleGauge',
        route_name: 'slot',
        title: 'Slot',
      },
      {
        icon: 'CircleGauge',
        route_name: 'box',
        title: 'Box',
      },
      {
        icon: 'CircleGauge',
        route_name: 'scroll-area',
        title: 'Scroll Area',
      },
    ],
  },
  {
    icon: 'CircleGauge',
    title: 'Navigation',
    sub_menu: [
      {
        icon: 'CircleGauge',
        route_name: 'breadcrumb',
        title: 'Breadcrumb',
      },
      {
        icon: 'CircleGauge',
        route_name: 'menu',
        title: 'Menu',
      },
      {
        icon: 'CircleGauge',
        route_name: 'pagination',
        title: 'Pagination',
      },
      {
        icon: 'CircleGauge',
        route_name: 'tabs',
        title: 'Tabs',
      },
    ],
  },
  {
    icon: 'CircleGauge',
    title: 'Forms',
    sub_menu: [
      {
        icon: 'CircleGauge',
        route_name: 'button',
        title: 'Button',
      },
      {
        icon: 'CircleGauge',
        route_name: 'checkbox',
        title: 'Checkbox',
      },
      {
        icon: 'CircleGauge',
        route_name: 'combobox',
        title: 'Combobox',
      },
      {
        icon: 'CircleGauge',
        route_name: 'datepicker',
        title: 'Datepicker',
      },
      {
        icon: 'CircleGauge',
        route_name: 'field',
        title: 'Field',
      },
      {
        icon: 'CircleGauge',
        route_name: 'input',
        title: 'Input',
      },
      {
        icon: 'CircleGauge',
        route_name: 'native-select',
        title: 'Native Select',
      },
      {
        icon: 'CircleGauge',
        route_name: 'radio-group',
        title: 'Radio Group',
      },
      {
        icon: 'CircleGauge',
        route_name: 'select',
        title: 'Select',
      },
      {
        icon: 'CircleGauge',
        route_name: 'slider',
        title: 'Slider',
      },
      {
        icon: 'CircleGauge',
        route_name: 'switch',
        title: 'Switch',
      },
      {
        icon: 'CircleGauge',
        route_name: 'textarea',
        title: 'Textarea',
      },
    ],
  },
  {
    icon: 'CircleGauge',
    title: 'Data Display',
    sub_menu: [
      {
        icon: 'CircleGauge',
        route_name: 'accordion',
        title: 'Accordion',
      },
      {
        icon: 'CircleGauge',
        route_name: 'avatar',
        title: 'Avatar',
      },
      {
        icon: 'CircleGauge',
        route_name: 'badge',
        title: 'Badge',
      },
      {
        icon: 'CircleGauge',
        route_name: 'carousel',
        title: 'Carousel',
      },
      {
        icon: 'CircleGauge',
        route_name: 'table',
        title: 'Table',
      },
      {
        icon: 'CircleGauge',
        route_name: 'data-table',
        title: 'Data Table',
      },
    ],
  },
  {
    icon: 'CircleGauge',
    title: 'Feedback',
    sub_menu: [
      {
        icon: 'CircleGauge',
        route_name: 'alert',
        title: 'Alert',
      },
      {
        icon: 'CircleGauge',
        route_name: 'progress-circular',
        title: 'Progress Circular',
      },
      {
        icon: 'CircleGauge',
        route_name: 'progress-linear',
        title: 'Progress Linear',
      },
      {
        icon: 'CircleGauge',
        route_name: 'toast',
        title: 'Toast',
      },
    ],
  },
  {
    icon: 'CircleGauge',
    title: 'Overlay',
    sub_menu: [
      {
        icon: 'CircleGauge',
        route_name: 'dialog',
        title: 'Dialog',
      },
      {
        icon: 'CircleGauge',
        route_name: 'popover',
        title: 'Popover',
      },
      {
        icon: 'CircleGauge',
        route_name: 'sheet',
        title: 'Sheet',
      },
      {
        icon: 'CircleGauge',
        route_name: 'tooltip',
        title: 'Tooltip',
      },
    ],
  },
  {
    icon: 'CircleGauge',
    title: 'Visuals',
    sub_menu: [
      {
        icon: 'CircleGauge',
        route_name: 'chart',
        title: 'Chart',
      },
      {
        icon: 'CircleGauge',
        route_name: 'map',
        title: 'Map',
      },
    ],
  },
]

export default mainMenu
