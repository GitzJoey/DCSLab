import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import CacheService from "./CacheService";

export default class DashboardService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private cacheService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;
        this.cacheService = new CacheService();
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

    public async getStatusDDL() {
        if (this.cacheService.getCachedDDL('statusDDL') == null) {
            let response = await this.axiosGet(route('api.get.db.common.ddl.list.statuses', undefined, false, this.ziggyRoute));
            this.cacheService.setCachedDDL('statusDDL', response.data);
            return response.data;
        } else {
            return this.cacheService.getCachedDDL('statusDDL');
        }
    }

    public async getCountriesDDL() {
        if (this.cacheService.getCachedDDL('countriesDDL') == null) {
            let response = await this.axiosGet(route('api.get.db.common.ddl.list.countries', undefined, false, this.ziggyRoute));
            this.cacheService.setCachedDDL('countriesDDL', response.data);
            return response.data;
        } else {
            return this.cacheService.getCachedDDL('countriesDDL');
        }
    }
}