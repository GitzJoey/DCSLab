import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { User } from "../types/models/User";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";

export default class UserService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public async readAny(search: string, refresh: boolean, paginate: boolean, page?: number, per_page?: number): Promise<ServiceResponse<Collection<User[]> | Resource<User[]> | null>> {
        try {
            const queryParams: Record<string, string | number | boolean> = {};
            queryParams['search'] = search;
            queryParams['refresh'] = refresh;
            queryParams['paginate'] = paginate;
            if (page) queryParams['page'] = page;
            if (per_page) queryParams['per_page'] = per_page;

            //ZiggyRouteNotFoundException
            //const url = route('invalid.route', undefined, false, this.ziggyRoute);
            const url = route('api.get.db.admin.user.read_any', { _query: queryParams }, false, this.ziggyRoute);

            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Collection<User[]>> = await axios.get(url);

            return {
                success: true,
                data: response.data
            }
        } catch (e: unknown) {
            if (e instanceof Error && e.message.includes('Ziggy error')) {
                return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
            } else if (isAxiosError(e)) {
                return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
            } else {
                return { success: false }
            }
        }
    }

    public async read(ulid: string): Promise<ServiceResponse<User | null>> {
        try {
            const url = route('api.get.db.admin.user.read', {
                user: ulid
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<User>> = await axios.get(url);

            return {
                success: true,
                data: response.data.data
            }
        } catch (e: unknown) {
            if (e instanceof Error && e.message.includes('Ziggy error')) {
                return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
            } else if (isAxiosError(e)) {
                return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
            } else {
                return { success: false }
            }
        }
    }
}