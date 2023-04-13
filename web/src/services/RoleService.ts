import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import CacheService from "./CacheService";
import { AxiosResponse } from "axios";
import { DropdownOptionType } from "../types/DropdownOptionType";

export default class RoleService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private cacheService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;
        this.cacheService = new CacheService(); 
    }

    public async getRolesDDL(): Promise<DropdownOptionType[] | null> {
        const ddlName = 'rolesDDL';
        try {
            if (this.cacheService.getCachedDDL(ddlName) == null) {
                const url = route('api.get.db.admin.users.roles.read', undefined, false, this.ziggyRoute);
                if (!url) return null;
                    
                const response: AxiosResponse<DropdownOptionType[]> = await axios.get(url);

                this.cacheService.setCachedDDL(ddlName, response.data);
            } 

            return this.cacheService.getCachedDDL(ddlName);
        } catch (e: unknown) {
            return null;
        }
    }
}