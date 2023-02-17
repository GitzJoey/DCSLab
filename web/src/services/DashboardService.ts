import { AxiosRequestConfig, AxiosResponse } from "axios";
import { authAxiosInstance } from "../axios";

export default class DashboardService {
    private async axiosCall<T>(config: AxiosRequestConfig) {
        try {
            const { data } = await authAxiosInstance.request(config);
            return [ null, data];
        } catch (e) {
            return [ e ];
        }
    }

    public async readProfile() {
        
    }
}