import dcslabHttpClient from '@/axios'
import { useZiggyRouteStore } from '@/stores/ziggy-route'
import { route } from 'ziggy-js'
import type { Config } from 'ziggy-js'
import type { ServiceResponse } from '@/types/services/ServiceResponse'
import type { AxiosResponse } from 'axios'

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

      const response: AxiosResponse<ServiceResponse<Config>> = await dcslabHttpClient.get(url)

      result.success = response.data.success
      result.data = response.data.data

      return result
    } catch (e: unknown) {
      return result
    }
  }
}
