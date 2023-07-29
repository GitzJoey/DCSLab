import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { Warehouse } from "../types/models/Warehouse";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";
import { SearchRequest } from "../types/requests/SearchRequest";
import { WarehouseFormRequest } from "../types/requests/WarehouseFormRequest";

export default class WarehouseService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public async create(company_id: string, branch_id: string, payload: WarehouseFormRequest): Promise<ServiceResponse<Warehouse | null>> {
        const result: ServiceResponse<Warehouse | null> = {
            success: false,
        }

        try {
            const url = route('api.post.db.company.warehouse.save', undefined, false, this.ziggyRoute);        
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Warehouse> = await axios.post(
                url, payload);

            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

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

    public async readAny(
        company_id: string, 
        branch_id: string, 
        args: SearchRequest
    ): Promise<ServiceResponse<Collection<Array<Warehouse>> | Resource<Array<Warehouse>> | null>> {
        const result: ServiceResponse<Collection<Warehouse[]> | Resource<Warehouse[]> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, string | number | boolean> = {};
            queryParams['company_id'] = company_id;
            queryParams['branch_id'] = branch_id;
            queryParams['search'] = args.search ? args.search : '';
            queryParams['refresh'] = args.refresh;
            queryParams['paginate'] = args.paginate;
            if (args.page) queryParams['page'] = args.page;
            if (args.per_page) queryParams['per_page'] = args.per_page;

            const url = route('api.get.db.company.warehouse.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);

            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Collection<Warehouse[]>> = await axios.get(url);

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

    public async read(ulid: string): Promise<ServiceResponse<Warehouse | null>> {
        const result: ServiceResponse<Warehouse | null> = {
            success: false
        }

        try {
            const url = route('api.get.db.company.warehouse.read', {
                user: ulid
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Warehouse>> = await axios.get(url);

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

    public async update(ulid: string, company_id: string, branch_id: string, payload: WarehouseFormRequest): Promise<ServiceResponse<Warehouse | null>> {
        const result: ServiceResponse<Warehouse | null> = {
            success: false,
        }

        try {                    
            const url = route('api.post.db.company.warehouse.edit', ulid, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Warehouse> = await axios.post(
                url, payload);
            
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

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

    public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
        const result: ServiceResponse<boolean | null> = {
            success: false,
        }

        try {
            const url = route('api.post.db.company.warehouse.delete', ulid, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<boolean | null> = await axios.post(url);

            result.success = true;
            result.data = true;

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