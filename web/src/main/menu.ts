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
    icon: 'Home',
    title: 'components.menu.dashboard',
    sub_menu: [
      {
        icon: 'Home',
        route_name: 'dashboard-maindashboard',
        title: 'components.menu.dashboard-maindashboard',
      },
    ],
  },
  {
    icon: 'Network',
    title: 'components.menu.organization',
    sub_menu: [
      {
        icon: 'ChevronRight',
        route_name: 'dashboard-organization-company',
        title: 'components.menu.organization-company',
      },
      {
        icon: 'ChevronRight',
        route_name: 'dashboard-organization-branch',
        title: 'components.menu.organization-branch',
      },
    ],
  },
  {
    icon: 'Cpu',
    title: 'components.menu.administrator',
    sub_menu: [
      {
        icon: 'ChevronRight',
        route_name: 'dashboard-administrator-user',
        title: 'components.menu.administrator-user',
      },
      {
        icon: 'ChevronRight',
        route_name: 'components.menu.administrator-devtools',
        title: 'Dev Tools',
        sub_menu: [
          {
            icon: 'ChevronsRight',
            route_name: 'dashboard-administrator-devtools-playground-1',
            title: 'components.menu.administrator-devtools-playground-1',
          },
          {
            icon: 'ChevronsRight',
            route_name: 'dashboard-administrator-devtools-playground-2',
            title: 'components.menu.administrator-devtools-playground-1',
          },
        ],
      },
    ],
  },
]

export default mainMenu
