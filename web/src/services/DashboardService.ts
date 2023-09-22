import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import CacheService from "./CacheService";
import { AxiosResponse, AxiosError, isAxiosError } from "axios";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { UserProfile } from "../types/models/UserProfile";
import { Menu as sMenu } from "../stores/side-menu";
import ErrorHandlerService from "./ErrorHandlerService";
import { Resource } from "../types/resources/Resource";
import { DropDownOption } from "../types/models/DropDownOption";
import { FileUpload } from "../types/models/FileUpload";

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
        const result: ServiceResponse<UserProfile> | null = {
            success: false,
        };

        try {
            const url = route('api.get.db.module.profile.read', undefined, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<UserProfile>> = await axios.get(url);

            result.success = true;
            result.data = response.data.data;

            return result;
        } catch (e: unknown) {
            if (e instanceof Error && e.message.includes('Ziggy error')) {
                return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
            } else if (isAxiosError(e)) {
                return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
            } else {
                return result;
            }
        }
    }

    public async readUserMenu(): Promise<ServiceResponse<Array<sMenu> | null>> {
        const result: ServiceResponse<Array<sMenu>> | null = {
            success: false,
        };

        try {
            const url = route('api.get.db.core.user.menu', undefined, false, this.ziggyRoute);

            const response: AxiosResponse<Array<sMenu>> = await axios.get(url);

            result.success = true;
            result.data = response.data;

            return result;
        } catch (e: unknown) {
            if (e instanceof Error && e.message.includes('Ziggy error')) {
                return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
            } else if (isAxiosError(e)) {
                return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
            } else {
                return result;
            }
        }
    }

    public async readUserApi(): Promise<ServiceResponse<Config | null>> {
        const result: ServiceResponse<Config> | null = {
            success: false,
        };

        try {
            const url = route('api.get.db.core.user.api', undefined, false, this.ziggyRoute);

            const response: AxiosResponse<Config> = await axios.get(url);

            result.success = true;
            result.data = response.data;

            return result;
        } catch (e: unknown) {
            if (e instanceof Error && e.message.includes('Ziggy error')) {
                return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
            } else if (isAxiosError(e)) {
                return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
            } else {
                return result;
            }
        }
    }

    public async getStatusDDL(): Promise<Array<DropDownOption> | null> {
        const ddlName = 'statusDDL';
        let result: Array<DropDownOption> = [];

        try {
            if (this.cacheService.getCachedDDL(ddlName) == null) {
                const url = route('api.get.db.common.ddl.list.statuses', undefined, false, this.ziggyRoute);

                const response: AxiosResponse<Array<DropDownOption> | null> = await axios.get(url);

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

    public async getCountriesDDL(): Promise<Array<DropDownOption> | null> {
        const ddlName = 'countriesDDL';
        let result: Array<DropDownOption> = [];

        try {
            if (this.cacheService.getCachedDDL(ddlName) == null) {
                const url = route('api.get.db.common.ddl.list.countries', undefined, false, this.ziggyRoute);

                const response: AxiosResponse<Array<DropDownOption> | null> = await axios.get(url);

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

    public async uploadFile(file: File): Promise<ServiceResponse<FileUpload | null>> {
        const result: ServiceResponse<FileUpload | null> = {
            success: false
        }

        try {
            const formData = new FormData()
            formData.append('file', file)

            const url = route('api.post.db.core.user.upload', undefined, false, this.ziggyRoute)

            axios.defaults.headers.common['Content-Type'] = "multipart/form-data"

            const response: AxiosResponse<Resource<FileUpload>> = await axios.post(url, formData);

            result.success = true;
            result.data = response.data.data;

            return result;
        } catch (e: unknown) {
            if (e instanceof Error && e.message.includes('Ziggy error')) {
                return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
            } else if (isAxiosError(e)) {
                return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
            } else {
                return result;
            }
        }
    }
}