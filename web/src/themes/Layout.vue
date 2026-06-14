<script setup lang="ts">
import { useThemeStore, getTheme, themes, type Themes } from '@/stores/theme'
import { useDashboardStore } from '@/stores/dashboard'
import { ThemeSwitcher } from '@/components/theme-switcher'
import { useRoute } from 'vue-router'
import { onMounted, computed } from 'vue'
import { LoadingOverlay } from '@/components/loading-overlay'
import ProfileService from '@/services/ProfileService'
import { useZiggyRouteStore } from '@/stores/ziggy-route'
import DashboardService from '@/services/DashboardService'
import { useUserContextStore } from '@/stores/user-context'
import type { Config } from 'ziggy-js'
import type { ServiceResponse } from '@/types/services/ServiceResponse'
import type { UserProfile } from '@/types/models/UserProfile'
import { useMenuStore, type Menu as sMenu } from '@/stores/menu'

const profileService = new ProfileService()
const dashboardService = new DashboardService()

const route = useRoute()
const Component = computed(() => getTheme(themeStore.theme).component)

const themeStore = useThemeStore()
const switchTheme = (theme: Themes['name']) => {
  useThemeStore().setTheme(theme)
}

const dashboardStore = useDashboardStore()
const loading = computed(() => dashboardStore.getScreenMaskValue)

const ziggyRouteStore = useZiggyRouteStore()
const userContextStore = useUserContextStore()
const menuStore = useMenuStore()

onMounted(async () => {
  dashboardStore.setScreenMaskValue(true)

  const theme = route.query.theme as Themes['name']
  if (route.query.theme !== undefined && themes.map((theme) => theme.name).includes(theme)) {
    switchTheme(theme)
  }

  dashboardStore.setScreenMaskValue(false)

  let profile: ServiceResponse<UserProfile | null> = await profileService.readProfile()
  if (profile.success) {
    userContextStore.setUserContext(profile.data as UserProfile)
    console.log('Profile loaded')
  }

  let zRoute: ServiceResponse<Config | null> = await dashboardService.readRoutes()
  if (zRoute.success) {
    ziggyRouteStore.setZiggy(zRoute.data as Config)
    console.log('Ziggy loaded')
  }

  let sMenu: ServiceResponse<Array<sMenu> | null> = await dashboardService.readMenu()
  if (sMenu.success) {
    menuStore.setMenu(sMenu.data as Array<sMenu>)
  }
})
</script>

<template>
  <div>
    <LoadingOverlay :visible="loading" :transparent="false">
      <ThemeSwitcher />
      <Component />
    </LoadingOverlay>
  </div>
</template>
