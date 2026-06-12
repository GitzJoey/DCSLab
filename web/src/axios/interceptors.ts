import type { AxiosInstance } from 'axios'
import { useRouter } from 'vue-router'

const router = useRouter()

export function setupInterceptors(axiosInstance: AxiosInstance): void {
    axiosInstance.interceptors.response.use(
        response => {
            return response;
        },
        error => {
            if (error.response) {
                const status = error.response.status;

                switch (status) {
                    case 401:
                        //Unauthorized
                        router.push({ name: 'login' })
                        break;
                    case 419:
                        //Session expired
                        router.push({ name: 'login' })
                        break;
                    case 500:
                        //Server Error
                        router.push({ 
                            name: 'error-page',
                            state: {
                                code: '500',
                                message: 'Server Error.',
                                additional_message: ''
                            }
                        })
                        break;
                    default:
                        break;
                }
            } else if (error.request) {
            } else {
            }

            return Promise.reject(error);
        }
    );
}