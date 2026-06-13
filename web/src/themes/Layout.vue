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

onMounted(() => {
  dashboardStore.setScreenMaskValue(true)

  const theme = route.query.theme as Themes['name']
  if (route.query.theme !== undefined && themes.map((theme) => theme.name).includes(theme)) {
    switchTheme(theme)
  }

  dashboardStore.setScreenMaskValue(false)

  dashboardService.readRoutes().then((zRoute: ServiceResponse<Config | null>) => {})
  profileService.readProfile()
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
