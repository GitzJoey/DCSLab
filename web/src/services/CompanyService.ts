import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { Company } from "../types/models/Company";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";

export default class CompanyService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public async readAny(search: string, refresh: boolean, paginate: boolean, page?: number, per_page?: number): Promise<ServiceResponse<Collection<Company[]> | Resource<Company[]> | null>> {
        try {
            const queryParams: Record<string, string | number | boolean> = {};
            queryParams['search'] = search;
            queryParams['refresh'] = refresh;
            queryParams['paginate'] = paginate;
            if (page) queryParams['page'] = page;
            if (per_page) queryParams['per_page'] = per_page;

            const url = route('api.get.db.admin.company.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);

            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Collection<Company[]>> = await axios.get(url);

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

    public async read(ulid: string): Promise<ServiceResponse<Company | null>> {
        try {
            const url = route('api.get.db.admin.company.read', {
                user: ulid
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Company>> = await axios.get(url);

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