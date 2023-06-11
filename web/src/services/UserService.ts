import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { User } from "../types/models/User";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { AxiosError, AxiosResponse } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";

export default class UserService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public async readAny(args: { page?: number, per_page?: number, search?: string }): Promise<ServiceResponse<Collection<User[]> | null>> {
        try {
            const url = route('api.get.db.admin.users.read_any', args, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();
            const response: AxiosResponse<Collection<User[]>> = await axios.get(url);

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

    public async read(): Promise<ServiceResponse<User | null>> {
        try {
            const url = route('api.get.db.admin.users.read', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Resource<User>> = await axios.get(url);

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
}