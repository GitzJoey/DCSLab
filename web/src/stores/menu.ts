import { ref, computed } from 'vue'
import { defineStore } from 'pinia'
import type { Icon } from '@/components/ui/lucide/Lucide.vue'
import type { Themes } from '@/stores/theme'

export interface Menu {
  icon: Icon
  title: string
  pageName?: string
  subMenu?: Menu[]
  ignore?: boolean
}

export type MenuItem = Menu | 'divider'

export const useMenuStore = defineStore('menu', () => {
  const menuValue = ref<Array<MenuItem>>([
    {
      icon: 'Home',
      pageName: 'side-menu-dashboard',
      title: 'Dashboard',
      subMenu: [
        {
          icon: 'ChevronRight',
          pageName: 'side-menu-dashboard-maindashboard',
          title: 'Main Dashboard',
        },
      ],
    },
  ])

  const menu = computed(() => {
    return (layout: Themes['layout']): Array<MenuItem> => {
      return menuValue.value
    }
  })

  const setMenu = (menu: Array<MenuItem>) => {
    menuValue.value = menu
  }

  return {
    menuValue,
    menu,
    setMenu,
  }
})
