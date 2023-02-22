import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import CacheService from "./CacheService";

export default class RoleService {
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

    public async getRolesDDL() {
        if (this.cacheService.getCachedDDL('rolesDDL') == null) {
            let response = await this.axiosGet(route('api.get.db.admin.users.roles.read', undefined, false, this.ziggyRoute));
            this.cacheService.setCachedDDL('rolesDDL', response.data);
            return response.data;
        } else {
            return this.cacheService.getCachedDDL('rolesDDL');
        }
    }
}