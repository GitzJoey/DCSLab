import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import { Role } from "../types/models/Role";
import { Resource } from "../types/resources/Resource";
import { ServiceResponse } from "../types/services/ServiceResponse";
import ErrorHandlerService from "./ErrorHandlerService";

export default class RoleService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public async readAny(): Promise<ServiceResponse<Resource<Array<Role>> | null>> {
        const result: ServiceResponse<Resource<Array<Role>> | null> = {
            success: false,
        };

        try {
            const url = route('api.get.db.admin.role.read_any', undefined, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Array<Role>>> = await axios.get(url);

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

    public async Update(roles: string): Promise<ServiceResponse<Resource<Role> | null>> {
        const result: ServiceResponse<Resource<Role> | null> = {
            success: false
        }
        try {
            const url = route('api.post.db.module.profile.update.roles', undefined, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Role>> = await axios.post(url, { 'roles': roles })
            result.success = true
            result.data = response.data
            return result
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