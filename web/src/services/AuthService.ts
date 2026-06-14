import { client, useForm } from 'laravel-precognition-vue'
import httpClient from '@/axios'

export default class AuthService {
  public async ensureCSRF(): Promise<void> {
    let resultXSRF = await this.checkCookieExists('XSRF-TOKEN')

    if (resultXSRF) return

    await this.generateCSRF()
  }

  private checkCookieExists = (cookieName: string): Promise<boolean> => {
    return new Promise<boolean>((resolve) => {
      const cookies = document.cookie.split('; ')

      for (const cookie of cookies) {
        const [name] = cookie.split('=')
        if (name === cookieName) {
          resolve(true)
          return
        }
      }

      resolve(false)
    })
  }

  public async generateCSRF(): Promise<void> {
    await httpClient.get('/sanctum/csrf-cookie')
  }

  public useLoginForm() {
    client.useHttpClient(httpClient)

    const form = useForm('post', import.meta.env.VITE_BACKEND_URL + '/login', {
      email: '',
      password: '',
      remember: false,
    })

    return form
  }
}
