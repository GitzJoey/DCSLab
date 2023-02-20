import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";

export default class DashboardService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    }

    private async axiosGet(url: string): Promise<any> {
        try {
            let response = await axios.get(url);
            return response.data;
        } catch (e) {
            return e;
        }
    }

    private async axiosPost(url: string, data: any): Promise<any> {
        try {
            return await axios.post(url, data);
        } catch (e) {
            return e;
        }
    }

    public async readProfile() {
        return this.axiosGet(route('api.get.db.module.profile.read', undefined, false, this.ziggyRoute));
    }

    public async readUserMenu() {
        return this.axiosGet(route('api.get.db.core.user.menu', undefined, false, this.ziggyRoute));
    }

    public async readUserApi() {
        return this.axiosGet(route('api.get.db.core.user.api', undefined, false, this.ziggyRoute));
    }
}