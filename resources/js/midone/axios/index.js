import axios from "axios";
import router from "@/router";

const defaultAxiosInstance = axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
});    

defaultAxiosInstance.interceptors.request.use(function (config) {
    config.headers.common['X-localization'] = localStorage.getItem('DCSLAB_LANG') == null ? document.documentElement.lang : localStorage.getItem('DCSLAB_LANG');
    return config;
});

defaultAxiosInstance.interceptors.response.use(response => {
    return response;
}, error => {
    if (error.response == undefined || error.response.status == undefined) return Promise.reject(error);
    switch(error.response.status) {
        case 401:
        case 403:
            router.push({ name: 'side-menu-error-code', params: { code: error.response.status } });
            break;
        case 500:
            router.push({ name: 'side-menu-error-code', params: { code: error.response.status } });
            break;
        default:
            break;
    }
    return Promise.reject(error);
});

const axiosInstance = axios.create();
    
export { defaultAxiosInstance as default, axiosInstance }