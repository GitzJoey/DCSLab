import axios from '@/axios'
import { useZiggyRouteStore } from '@/stores/ziggy-route'
import { route } from 'ziggy-js'
import type { Config } from 'ziggy-js'
import type { ServiceResponse } from '@/types/services/ServiceResponse'

export default class DashboardService {
  private ziggyRoute: Config
  private ziggyRouteStore = useZiggyRouteStore()

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy
  }

  public async readRoutes(): Promise<ServiceResponse<Config | null>> {
    const result: ServiceResponse<Config> | null = {
      success: false,
    }

    try {
      const url = route('api.get.db.core.user.api', undefined, false, this.ziggyRoute)

      const response: AxiosResponse<Config> = await axios.get(url)

      result.success = true
      result.data = response.data

      return result
    } catch (e: unknown) {
      if (e instanceof Error && e.message.includes('Ziggy error')) {
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message)
      } else if (isAxiosError(e)) {
        return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError)
      } else {
        return result
      }
    }
  }
}
