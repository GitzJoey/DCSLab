import { AxiosRequestConfig, AxiosResponse } from "axios";
import axios from "../axios";

export default class DashboardService {
    private async axiosCall<T>(config: AxiosRequestConfig) {
        try {
            const { data } = await axios.request(config);
            return [ null, data];
        } catch (e) {
            return [ e ];
        }
    }

    public async readProfile() {
        return this.axiosCall({ method: 'GET', url: '/api/get/dashboard/module/profile/read' });
    }

    public async readUserMenu() {
        return this.axiosCall({ method: 'GET', url: '/api/get/dashboard/core/user/menu'});
    }

    public async readUserApi() {
        return this.axiosCall({ method: 'GET', url: '/api/get/dashboard/core/user/api'});
    }
}