import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import CacheService from "./CacheService";
import { AxiosResponse } from "axios";
import { DropDownOption } from "../types/services/DropDownOption";

export default class RoleService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private cacheService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;
        this.cacheService = new CacheService();
    }

    public async getRolesDDL(): Promise<Array<DropDownOption> | null> {
        const ddlName = 'rolesDDL';

        try {
            if (this.cacheService.getCachedDDL(ddlName) == null) {
                const url = route('api.get.db.admin.role.read.ddl', undefined, false, this.ziggyRoute);

                const response: AxiosResponse<Array<DropDownOption>> = await axios.get(url);

                this.cacheService.setCachedDDL(ddlName, response.data);
            }

            return this.cacheService.getCachedDDL(ddlName);
        } catch (e: unknown) {
            return null;
        }
    }
}