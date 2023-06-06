import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import CacheService from "./CacheService";
import { AxiosResponse, AxiosError } from "axios";
import { ServiceResponseType } from "../types/systems/ServiceResponseType";
import { UserProfileType } from "../types/resources/UserProfileType";
import { UserType } from "../types/resources/UserType";
import ErrorHandlerService from "./ErrorHandlerService";
import { Menu as sMenu } from "../stores/side-menu";
import { DropdownOptionType } from "../types/DropdownOptionType";

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

    public async readProfile(): Promise<ServiceResponseType<UserProfileType | null>> {
        try {
            const url = route('api.get.db.module.profile.read', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<UserProfileType> = await axios.get(url);

            return {
                success: true,
                statusCode: response.status,
                statusDescription: response.statusText,
                data: response.data
            }
        } catch (e: unknown) {
            return this.errorHandlerService.generateErrorServiceResponse(e as AxiosError<unknown, unknown>);
        }
    }

    public async readUserMenu(): Promise<ServiceResponseType<Array<sMenu> | null>> {
        try {
            const url = route('api.get.db.core.user.menu', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Array<sMenu>> = await axios.get(url);

            return {
                success: true,
                statusCode: response.status,
                statusDescription: response.statusText,
                data: response.data
            }
        } catch (e: unknown) {
            return this.errorHandlerService.generateErrorServiceResponse(e as AxiosError<unknown, unknown>);
        }
    }

    public async readUserApi(): Promise<ServiceResponseType<Config | null>> {
        try {
            const url = route('api.get.db.core.user.api', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Config> = await axios.get(url);

            return {
                success: true,
                statusCode: response.status,
                statusDescription: response.statusText,
                data: response.data
            }
        } catch (e: unknown) {
            return this.errorHandlerService.generateErrorServiceResponse(e as AxiosError<unknown, unknown>);
        }
    }

    public async read(): Promise<ServiceResponseType<UserType | null>> {
        try {
            const url = route('api.get.db.admin.users.read', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<UserType> = await axios.get(url);

            return {
                success: true,
                statusCode: response.status,
                statusDescription: response.statusText,
                data: response.data
            }
        } catch (e: unknown) {
            return this.errorHandlerService.generateErrorServiceResponse(e as AxiosError<unknown, unknown>);
        }
    }

    public async getStatusDDL(): Promise<DropdownOptionType[] | null> {
        const ddlName = 'statusDDL';
        try {
            if (this.cacheService.getCachedDDL(ddlName) == null) {
                const url = route('api.get.db.common.ddl.list.statuses', undefined, false, this.ziggyRoute);
                if (!url) return null;

                const response: AxiosResponse<DropdownOptionType[]> = await axios.get(url);

                this.cacheService.setCachedDDL(ddlName, response.data);
            }

            return this.cacheService.getCachedDDL(ddlName);
        } catch (e: unknown) {
            return null;
        }
    }

    public async getCountriesDDL(): Promise<DropdownOptionType[] | null> {
        const ddlName = 'countriesDDL';
        try {
            if (this.cacheService.getCachedDDL(ddlName) == null) {
                const url = route('api.get.db.common.ddl.list.countries', undefined, false, this.ziggyRoute);
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