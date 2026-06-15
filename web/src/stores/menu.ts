import { ref, toRaw } from 'vue'
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

const MENU_STORAGE_KEY = 'DCSLAB_MENU'

export const useMenuStore = defineStore('menu', () => {
  const menuValue = ref<Array<MenuItem>>([
    {
      icon: 'Home',
      pageName: 'dashboard-dashboard',
      title: 'Dashboard',
      subMenu: [
        {
          icon: 'ChevronRight',
          pageName: 'dashboard-maindashboard',
          title: 'Main Dashboard',
        },
      ],
    },
  ])

  const isDebug = import.meta.env.VITE_APP_DEBUG === 'true'

  const getMenu = (layout: Themes['layout']): Array<MenuItem> => {
    const serializedMenu = sessionStorage.getItem(MENU_STORAGE_KEY)

    if (serializedMenu) {
      try {
        const deserializedMenu: Array<MenuItem> = JSON.parse(
          isDebug ? serializedMenu : atob(serializedMenu),
        )
        menuValue.value = deserializedMenu
      } catch (error) {
        console.error('Failed to parse Menu layout from session storage', error)
      }
    } else {
      const stringifiedMenu = JSON.stringify(menuValue.value)
      sessionStorage.setItem(MENU_STORAGE_KEY, isDebug ? stringifiedMenu : btoa(stringifiedMenu))
    }

    return toRaw(menuValue.value)
  }

  const setMenu = (menu: Array<MenuItem>) => {
    if (menu !== undefined && menu !== null) {
      const stringifiedMenu = JSON.stringify(menu)
      sessionStorage.setItem(MENU_STORAGE_KEY, isDebug ? stringifiedMenu : btoa(stringifiedMenu))

      menuValue.value = menu
    }
  }

  return {
    getMenu,
    setMenu,
  }
})
