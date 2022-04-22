import axios from "axios";

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
    switch(error.response.status) {
        default:
            return Promise.reject(error);
    }
});

const axiosInstance = axios.create();
    
export { defaultAxiosInstance as default, axiosInstance }