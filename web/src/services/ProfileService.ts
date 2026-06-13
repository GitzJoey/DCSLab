import { useZiggyRouteStore } from '../stores/ziggy-route'
import { route } from 'ziggy-js'
import { client, useForm } from 'laravel-precognition-vue'
import type { Config } from 'ziggy-js'
import type { ServiceResponse } from '@/types/services/ServiceResponse'
import type { UserProfile } from '@/types/models/UserProfile'
import type { AxiosResponse } from 'axios'
import type { Resource } from '@/types/resources/Resource'
import dcslabHttpClient from '@/axios'

export default class ProfileService {
  private ziggyRoute: Config
  private ziggyRouteStore = useZiggyRouteStore()

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy()
  }

  public async readProfile(): Promise<ServiceResponse<UserProfile | null>> {
    const result: ServiceResponse<UserProfile | null> = {
      success: false,
    }

    try {
      const url = route('api.dashboard.profile.show', undefined, false, this.ziggyRoute)

      const response: AxiosResponse<Resource<UserProfile>> = await dcslabHttpClient.get(url)

      result.success = true
      result.data = response.data.data

      return result
    } catch (e: unknown) {
      return result
    }
  }
}
