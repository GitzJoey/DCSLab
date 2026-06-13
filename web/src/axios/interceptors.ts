import type { AxiosInstance } from 'axios'
import router from '@/router'
import { StatusCodes } from '@/types/enums/StatusCodes'

export function setupInterceptors(axiosInstance: AxiosInstance): void {
  axiosInstance.interceptors.response.use(
    (response) => {
      return response
    },
    (error) => {
      if (error.response) {
        const status = error.response.status

        switch (status) {
          case StatusCodes.UNAUTHORIZED:
            //Unauthorized
            router.push({ name: 'login' })
            break
          case StatusCodes.LARAVEL_UNKNOWN_STATUS:
            //Session expired
            router.push({ name: 'login' })
            break
          case StatusCodes.INTERNAL_SERVER_ERROR:
            //Server Error
            router.push({
              name: 'error-page',
              state: {
                code: '500',
                message: 'Server Error.',
                additional_message: '',
              },
            })
            break
          default:
            break
        }
      } else if (error.request) {
      } else {
      }

      return Promise.reject(error)
    },
  )
}
