import axios from "axios";

const defaultAxiosInstance = axios.create({
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        'Accept': 'application/json',
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
    }
});    

defaultAxios.interceptors.request.use(function (config) {
    config.headers.common['X-localization'] = localStorage.getItem('DCSLAB_LANG') == null ? document.documentElement.lang : localStorage.getItem('DCSLAB_LANG');
    return config;
});

defaultAxios.interceptors.response.use(response => {
    return response;
}, error => {
    window.location.href = '/dashboard';
});

const AxiosInstance = axios.create();
    
export { defaultAxiosInstance as default, AxiosInstance }