import { type Icon } from '@/components/ui/lucide'

export interface Menu {
  icon?: Icon
  title?: string
  route_name?: string
  params?: any
  badge?: number
  sub_menu?: Menu[]
}

const mainMenu: Array<Menu> = [
  {
    icon: 'CircleGauge',
    title: 'Dashboard',
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
    icon: 'CircleGauge',
    title: 'Apps',
    sub_menu: [
      {
        icon: 'Users',
        route_name: 'users',
        title: 'Users',
        sub_menu: [
          {
            icon: 'Zap',
            route_name: 'users-layout-1',
            title: 'Layout 1',
          },
          {
            icon: 'Zap',
            route_name: 'users-layout-2',
            title: 'Layout 2',
          },
          {
            icon: 'Zap',
            route_name: 'users-layout-3',
            title: 'Layout 3',
          },
        ],
      },
      {
        icon: 'Trello',
        route_name: 'profile',
        title: 'Profile',
        sub_menu: [
          {
            icon: 'Zap',
            route_name: 'profile-overview-1',
            title: 'Overview 1',
          },
          {
            icon: 'Zap',
            route_name: 'profile-overview-2',
            title: 'Overview 2',
          },
          {
            icon: 'Zap',
            route_name: 'profile-overview-3',
            title: 'Overview 3',
          },
        ],
      },
      {
        icon: 'ShoppingBag',
        route_name: 'ecommerce',
        title: 'E-Commerce',
        sub_menu: [
          {
            icon: 'Zap',
            route_name: 'categories',
            title: 'Categories',
          },
          {
            icon: 'Zap',
            route_name: 'add-product',
            title: 'Add Product',
          },
          {
            icon: 'Zap',
            route_name: 'product-list',
            title: 'Product List',
          },
          {
            icon: 'Zap',
            route_name: 'product-grid',
            title: 'Product Grid',
          },
          {
            icon: 'Zap',
            route_name: 'transaction-list',
            title: 'Transaction List',
          },
          {
            icon: 'Zap',
            route_name: 'transaction-detail',
            title: 'Transaction Detail',
          },
          {
            icon: 'Zap',
            route_name: 'seller-list',
            title: 'Seller List',
          },
          {
            icon: 'Zap',
            route_name: 'seller-detail',
            title: 'Seller Detail',
          },
          {
            icon: 'Zap',
            route_name: 'reviews',
            title: 'Reviews',
          },
        ],
      },
      {
        icon: 'Inbox',
        route_name: 'inbox',
        title: 'Inbox',
      },
      {
        icon: 'Folder',
        route_name: 'file-manager',
        title: 'File Manager',
      },
      {
        icon: 'CreditCard',
        route_name: 'point-of-sale',
        title: 'Point of Sale',
      },
      {
        icon: 'MessageSquare',
        route_name: 'chat',
        title: 'Chat',
      },
      {
        icon: 'FileText',
        route_name: 'post',
        title: 'Post',
      },
      {
        icon: 'Calendar',
        route_name: 'calendar',
        title: 'Calendar',
      },
      {
        icon: 'Edit',
        route_name: 'crud',
        title: 'Crud',
        sub_menu: [
          {
            icon: 'Zap',
            route_name: 'crud-data-list',
            title: 'Data List',
          },
          {
            icon: 'Zap',
            route_name: 'crud-form',
            title: 'Form',
          },
        ],
      },
    ],
  },
  {
    icon: 'Layout',
    title: 'Pages',
    sub_menu: [
      {
        icon: 'CircleGauge',
        route_name: 'wizards',
        title: 'Wizards',
        sub_menu: [
          {
            icon: 'Zap',
            route_name: 'wizard-layout-1',
            title: 'Layout 1',
          },
          {
            icon: 'Zap',
            route_name: 'wizard-layout-2',
            title: 'Layout 2',
          },
          {
            icon: 'Zap',
            route_name: 'wizard-layout-3',
            title: 'Layout 3',
          },
        ],
      },
      {
        icon: 'CircleGauge',
        route_name: 'blog',
        title: 'Blog',
        sub_menu: [
          {
            icon: 'Zap',
            route_name: 'blog-layout-1',
            title: 'Layout 1',
          },
          {
            icon: 'Zap',
            route_name: 'blog-layout-2',
            title: 'Layout 2',
          },
          {
            icon: 'Zap',
            route_name: 'blog-layout-3',
            title: 'Layout 3',
          },
        ],
      },
      {
        icon: 'CircleGauge',
        route_name: 'pricing',
        title: 'Pricing',
        sub_menu: [
          {
            icon: 'Zap',
            route_name: 'pricing-layout-1',
            title: 'Layout 1',
          },
          {
            icon: 'Zap',
            route_name: 'pricing-layout-2',
            title: 'Layout 2',
          },
        ],
      },
      {
        icon: 'CircleGauge',
        route_name: 'invoice',
        title: 'Invoice',
        sub_menu: [
          {
            icon: 'Zap',
            route_name: 'invoice-layout-1',
            title: 'Layout 1',
          },
          {
            icon: 'Zap',
            route_name: 'invoice-layout-2',
            title: 'Layout 2',
          },
        ],
      },
      {
        icon: 'CircleGauge',
        route_name: 'faq',
        title: 'FAQ',
        sub_menu: [
          {
            icon: 'Zap',
            route_name: 'faq-layout-1',
            title: 'Layout 1',
          },
          {
            icon: 'Zap',
            route_name: 'faq-layout-2',
            title: 'Layout 2',
          },
          {
            icon: 'Zap',
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
  {
    icon: 'Folder',
    title: 'Docs',
    sub_menu: [
      {
        icon: 'CircleGauge',
        title: 'Base',
        sub_menu: [
          {
            icon: 'Zap',
            route_name: 'slot',
            title: 'Slot',
          },
          {
            icon: 'Zap',
            route_name: 'box',
            title: 'Box',
          },
          {
            icon: 'Zap',
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
            icon: 'Zap',
            route_name: 'breadcrumb',
            title: 'Breadcrumb',
          },
          {
            icon: 'Zap',
            route_name: 'menu',
            title: 'Menu',
          },
          {
            icon: 'Zap',
            route_name: 'pagination',
            title: 'Pagination',
          },
          {
            icon: 'Zap',
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
            icon: 'Zap',
            route_name: 'button',
            title: 'Button',
          },
          {
            icon: 'Zap',
            route_name: 'checkbox',
            title: 'Checkbox',
          },
          {
            icon: 'Zap',
            route_name: 'combobox',
            title: 'Combobox',
          },
          {
            icon: 'Zap',
            route_name: 'datepicker',
            title: 'Datepicker',
          },
          {
            icon: 'Zap',
            route_name: 'field',
            title: 'Field',
          },
          {
            icon: 'Zap',
            route_name: 'input',
            title: 'Input',
          },
          {
            icon: 'Zap',
            route_name: 'native-select',
            title: 'Native Select',
          },
          {
            icon: 'Zap',
            route_name: 'radio-group',
            title: 'Radio Group',
          },
          {
            icon: 'Zap',
            route_name: 'select',
            title: 'Select',
          },
          {
            icon: 'Zap',
            route_name: 'slider',
            title: 'Slider',
          },
          {
            icon: 'Zap',
            route_name: 'switch',
            title: 'Switch',
          },
          {
            icon: 'Zap',
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
            icon: 'Zap',
            route_name: 'accordion',
            title: 'Accordion',
          },
          {
            icon: 'Zap',
            route_name: 'avatar',
            title: 'Avatar',
          },
          {
            icon: 'Zap',
            route_name: 'badge',
            title: 'Badge',
          },
          {
            icon: 'Zap',
            route_name: 'carousel',
            title: 'Carousel',
          },
          {
            icon: 'Zap',
            route_name: 'table',
            title: 'Table',
          },
          {
            icon: 'Zap',
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
            icon: 'Zap',
            route_name: 'alert',
            title: 'Alert',
          },
          {
            icon: 'Zap',
            route_name: 'progress-circular',
            title: 'Progress Circular',
          },
          {
            icon: 'Zap',
            route_name: 'progress-linear',
            title: 'Progress Linear',
          },
          {
            icon: 'Zap',
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
            icon: 'Zap',
            route_name: 'dialog',
            title: 'Dialog',
          },
          {
            icon: 'Zap',
            route_name: 'popover',
            title: 'Popover',
          },
          {
            icon: 'Zap',
            route_name: 'sheet',
            title: 'Sheet',
          },
          {
            icon: 'Zap',
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
            icon: 'Zap',
            route_name: 'chart',
            title: 'Chart',
          },
          {
            icon: 'Zap',
            route_name: 'map',
            title: 'Map',
          },
        ],
      },
    ],
  },
]

export default mainMenu
