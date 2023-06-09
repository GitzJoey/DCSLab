import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import CacheService from "./CacheService";
import { AxiosResponse, AxiosError } from "axios";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { UserProfile } from "../types/models/UserProfile";
import { Menu as sMenu } from "../stores/side-menu";
import ErrorHandlerService from "./ErrorHandlerService";
import { Resource } from "../types/resources/Resource";

export default class DashboardService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private cacheService;
    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.cacheService = new CacheService();
        this.errorHandlerService = new ErrorHandlerService();
    }

    public async readProfile(): Promise<ServiceResponse<UserProfile | null>> {
        try {
            const url = route('api.get.db.module.profile.read', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Resource<UserProfile>> = await axios.get(url);

            return {
                success: true,
                data: response.data.data
            }
        } catch (e: unknown) {
            return {
                success: false,
                error: e as AxiosError
            }
        }
    }

    public async readUserMenu(): Promise<ServiceResponse<Array<sMenu> | null>> {
        try {
            const url = route('api.get.db.core.user.menu', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Array<sMenu>> = await axios.get(url);

            return {
                success: true,
                data: response.data
            }
        } catch (e: unknown) {
            return {
                success: false,
                error: e as AxiosError
            }
        }
    }

    public async readUserApi(): Promise<ServiceResponse<Config | null>> {
        try {
            const url = route('api.get.db.core.user.api', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Config> = await axios.get(url);

            return {
                success: true,
                data: response.data
            }
        } catch (e: unknown) {
            return {
                success: false,
                error: e as AxiosError
            }
        }
    }

    public async getStatusDDL(): Promise<Record<string, string>[] | null> {
        const ddlName = 'statusDDL';
        try {
            if (this.cacheService.getCachedDDL(ddlName) == null) {
                const url = route('api.get.db.common.ddl.list.statuses', undefined, false, this.ziggyRoute);
                if (!url) return null;

                const response: AxiosResponse<Record<string, string>[]> = await axios.get(url);

                this.cacheService.setCachedDDL(ddlName, response.data);
            }

            return this.cacheService.getCachedDDL(ddlName);
        } catch (e: unknown) {
            return null;
        }
    }

    public async getCountriesDDL(): Promise<Record<string, string>[] | null> {
        const ddlName = 'countriesDDL';
        try {
            if (this.cacheService.getCachedDDL(ddlName) == null) {
                const url = route('api.get.db.common.ddl.list.countries', undefined, false, this.ziggyRoute);
                if (!url) return null;

                const response: AxiosResponse<Record<string, string>[]> = await axios.get(url);

                this.cacheService.setCachedDDL(ddlName, response.data);
            }

            return this.cacheService.getCachedDDL(ddlName);
        } catch (e: unknown) {
            return null;
        }
    }
}