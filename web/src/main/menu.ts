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
    title: 'Dashboard',
    sub_menu: [
      {
        icon: 'Home',
        route_name: 'dashboard-maindashboard',
        title: 'Main Dashboard',
      },
    ],
  },
  {
    icon: 'Network',
    title: 'Organization',
    sub_menu: [
      {
        icon: 'ChevronRight',
        route_name: 'dashboard-organization-company',
        title: 'Company',
      },
      {
        icon: 'ChevronRight',
        route_name: 'dashboard-organization-branch',
        title: 'Branch',
      },
    ],
  },
  {
    icon: 'Cpu',
    title: 'Administrator',
    sub_menu: [
      {
        icon: 'ChevronRight',
        route_name: 'dashboard-administrator-user',
        title: 'User',
      },
      {
        icon: 'ChevronRight',
        route_name: 'dashboard-administrator-devtools',
        title: 'Dev Tools',
        sub_menu: [
          {
            icon: 'ChevronsRight',
            route_name: 'dashboard-administrator-devtools-playground-1',
            title: 'Playground 1',
          },
          {
            icon: 'ChevronsRight',
            route_name: 'dashboard-administrator-devtools-playground-2',
            title: 'Playground 2',
          },
        ],
      },
    ],
  },
]

export default mainMenu
