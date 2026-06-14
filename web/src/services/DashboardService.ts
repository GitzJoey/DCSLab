import httpClient from '@/axios'
import { useZiggyRouteStore } from '@/stores/ziggy-route'
import { route } from 'ziggy-js'
import type { Config } from 'ziggy-js'
import type { ServiceResponse } from '@/types/services/ServiceResponse'
import type { AxiosResponse } from 'axios'
import type { Menu as sMenu } from '@/stores/menu'

export default class DashboardService {
  private ziggyRoute: Config
  private ziggyRouteStore = useZiggyRouteStore()

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy()
  }

  public async readRoutes(): Promise<ServiceResponse<Config | null>> {
    const result: ServiceResponse<Config> | null = {
      success: false,
    }

    try {
      const url = route('api.dashboard.routes', undefined, false, this.ziggyRoute)

      const response: AxiosResponse<ServiceResponse<Config>> = await httpClient.get(url)

      result.success = response.data.success
      result.data = response.data.data

      return result
    } catch (e: unknown) {
      return result
    }
  }

  public async readMenu(): Promise<ServiceResponse<Array<sMenu> | null>> {
    const result: ServiceResponse<Array<sMenu>> | null = {
      success: false,
    }

    try {
      const url = route('api.dashboard.menu', undefined, false, this.ziggyRoute)

      const response: AxiosResponse<Array<sMenu>> = await httpClient.get(url)

      result.success = true
      result.data = response.data

      return result
    } catch (e: unknown) {
      return result
    }
  }
}
