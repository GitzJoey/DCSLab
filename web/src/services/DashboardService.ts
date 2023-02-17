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
        return this.axiosCall({ method: 'GET', url: '/api/get/dashboard/core/profile/read' });
    }

    public async readUserMenu() {
        
    }

    public async readUserApi() {
        
    }
}