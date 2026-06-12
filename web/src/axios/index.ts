import axios from "axios"
import { setupInterceptors } from './interceptors'

const defaultAxiosInstance = axios.create({
    baseURL: import.meta.env.VITE_BACKEND_URL,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-Sanitizer-Mode': ''
    }
});

defaultAxiosInstance.defaults.withCredentials = true
defaultAxiosInstance.defaults.withXSRFToken = true

defaultAxiosInstance.interceptors.request.use(function (config) {
    config.headers['X-Localization'] = localStorage.getItem('DCSLAB_LANG') == null ? document.documentElement.lang : localStorage.getItem('DCSLAB_LANG')
    return config
})

setupInterceptors(defaultAxiosInstance)

const axiosInstance = axios.create()

export { defaultAxiosInstance as default, axiosInstance }