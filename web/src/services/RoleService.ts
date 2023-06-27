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

    public async getRolesDDL(): Promise<Array<DropDownOption>> {
        const ddlName = 'rolesDDL';
        let result: Array<DropDownOption> = [];

        try {
            if (this.cacheService.getCachedDDL(ddlName) == null) {
                console.log('null');
                const url = route('api.get.db.admin.role.read.ddl', undefined, false, this.ziggyRoute);

                const response: AxiosResponse<Array<DropDownOption>> = await axios.get(url);

                console.log('set');
                this.cacheService.setCachedDDL(ddlName, response.data);
            }

            const cachedData: Array<DropDownOption> | null = this.cacheService.getCachedDDL(ddlName);
            if (cachedData != null) {
                result = cachedData as Array<DropDownOption>;
            }

            return result;
        } catch (e: unknown) {
            return result;
        }
    }
}