import axios, { authAxiosInstance } from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { Brand } from "../types/models/Brand";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";
import { SearchRequest } from "../types/requests/SearchRequest";
import { FormRequest } from "../types/requests/FormRequest";

export default class BrandService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public async create(company_id: string, payload: FormRequest<Brand>): Promise<ServiceResponse<Brand | null>> {
        const result: ServiceResponse<Brand | null> = {
            success: false,
        }

        try {
            const url = route('api.post.db.product.brand.save', undefined, false, this.ziggyRoute);        
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Brand> = await authAxiosInstance.post(
                url, {
                company_id: company_id,
                code: payload.data.code,
                name: payload.data.name
            });            

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

    public async readAny(company_id: string, args: SearchRequest): Promise<ServiceResponse<Collection<Array<Brand>> | Resource<Array<Brand>> | null>> {
        const result: ServiceResponse<Collection<Brand[]> | Resource<Brand[]> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, string | number | boolean> = {};
            queryParams['company_id'] = company_id;
            queryParams['search'] = args.search ? args.search : '';
            queryParams['refresh'] = args.refresh;
            queryParams['paginate'] = args.paginate;
            if (args.page) queryParams['page'] = args.page;
            if (args.per_page) queryParams['per_page'] = args.per_page;

            const url = route('api.get.db.product.brand.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);

            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Collection<Brand[]>> = await axios.get(url);

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

    public async read(ulid: string): Promise<ServiceResponse<Brand | null>> {
        const result: ServiceResponse<Brand | null> = {
            success: false
        }

        try {
            const url = route('api.get.db.product.brand.read', {
                user: ulid
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Brand>> = await axios.get(url);

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

    public async update(ulid: string, company_id: string, payload: FormRequest<Brand>): Promise<ServiceResponse<Brand | null>> {
        const result: ServiceResponse<Brand | null> = {
            success: false,
        }

        try {                    
            const url = route('api.post.db.product.brand.edit', ulid, false, this.ziggyRoute);        
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();        

            const response: AxiosResponse<Brand> = await authAxiosInstance.post(
                url, {
                    company_id: company_id,
                    code: payload.data.code,
                    name: payload.data.name
            }); 
            
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
            const url = route('api.post.db.product.brand.delete', ulid, false, this.ziggyRoute);
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