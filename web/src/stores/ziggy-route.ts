import { ref, toRaw } from 'vue'
import { defineStore } from 'pinia'
import type { Config } from 'ziggy-js'

const STORAGE_KEY = 'DCSLAB_ZIGGY_ROUTE'

const getDomain = (): string => {
  try {
    const domain = new URL(import.meta.env.VITE_BACKEND_URL)
    return domain.hostname || 'localhost'
  } catch {
    return 'localhost'
  }
}

const getDomainPort = (): number | undefined => {
  try {
    const domain = new URL(import.meta.env.VITE_BACKEND_URL)
    return domain.port ? Number(domain.port) : undefined
  } catch {
    return undefined
  }
}

export const useZiggyRouteStore = defineStore('ziggyRoute', () => {
  const ziggyRoute = ref<Config>({
    url: getDomain(),
    port: getDomainPort() as any,
    defaults: {},
    routes: {
      'api.dashboard.profile.show': {
        uri: 'api/dashboard/profile/show',
        methods: ['GET', 'HEAD'],
      },
      'api.dashboard.menu': {
        uri: 'api/dashboard/menu',
        methods: ['GET', 'HEAD'],
      },
      'api.dashboard.routes': {
        uri: 'api/dashboard/routes',
        methods: ['GET', 'HEAD'],
      },
    },
  })

  const isDebug = import.meta.env.VITE_APP_DEBUG === 'true'

  const getZiggy = (): Config => {
    const serializedZiggy = sessionStorage.getItem(STORAGE_KEY)

    if (serializedZiggy) {
      try {
        const deserializedZiggy: Config = JSON.parse(
          isDebug ? serializedZiggy : atob(serializedZiggy),
        )
        ziggyRoute.value = deserializedZiggy
      } catch (error) {
        console.error('Failed to parse Ziggy routes from session storage', error)
      }
    } else {
      const stringifiedZiggy = JSON.stringify(ziggyRoute.value)
      sessionStorage.setItem(STORAGE_KEY, isDebug ? stringifiedZiggy : btoa(stringifiedZiggy))
    }

    return toRaw(ziggyRoute.value)
  }

  const setZiggy = (ziggy: Config) => {
    if (ziggy !== undefined && ziggy !== null) {
      const stringifiedZiggy = JSON.stringify(ziggy)
      sessionStorage.setItem(STORAGE_KEY, isDebug ? stringifiedZiggy : btoa(stringifiedZiggy))
      ziggyRoute.value = ziggy
    }
  }

  return {
    getZiggy,
    setZiggy,
  }
})
