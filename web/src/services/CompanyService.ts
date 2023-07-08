import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { Company } from "../types/models/Company";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";
import { SearchRequest } from "../types/requests/SearchRequest";
import { FormRequest } from "../types/requests/FormRequest";
import { authAxiosInstance } from '../axios'

export default class CompanyService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public async create(payload: FormRequest<Company>): Promise<ServiceResponse<Company | null>> {
        const result: ServiceResponse<Company | null> = {
            success: false,
        }

        try {                    
            const url = route('api.post.db.company.company.save', undefined, false, this.ziggyRoute);        
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();
            
            if (payload.default == undefined) payload.default = false;

            const response: AxiosResponse<Company> = await authAxiosInstance.post(
                url, {
                code: payload.code,
                name: payload.name,
                address: payload.address,
                default: payload.default,
                status: payload.status
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

    public async readAny(args: SearchRequest): Promise<ServiceResponse<Collection<Array<Company>> | Resource<Array<Company>> | null>> {
        const result: ServiceResponse<Collection<Array<Company>> | Resource<Array<Company>> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, string | number | boolean> = {};
            queryParams['search'] = args.search ? args.search : '';
            queryParams['refresh'] = args.refresh;
            queryParams['paginate'] = args.paginate;
            if (args.page) queryParams['page'] = args.page;
            if (args.per_page) queryParams['per_page'] = args.per_page;

            const url = route('api.get.db.company.company.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);

            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Collection<Company[]>> = await axios.get(url);

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

    public async read(ulid: string): Promise<ServiceResponse<Company | null>> {
        const result: ServiceResponse<Company | null> = {
            success: false
        }

        try {
            const url = route('api.get.db.company.company.read', {
                user: ulid
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Company>> = await axios.get(url);

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

    public async update(payload: FormRequest<Company>): Promise<ServiceResponse<Company | null>> {
        const result: ServiceResponse<Company | null> = {
            success: false,
        }

        try {                    
            const url = route('api.post.db.company.company.edit', payload.ulid, false, this.ziggyRoute);        
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();        

            const response: AxiosResponse<Company> = await authAxiosInstance.post(
                url, {
                code: payload.code,
                name: payload.name,
                address: payload.address,
                default: payload.default,
                status: payload.status
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
        const result: ServiceResponse<Company | null> = {
            success: false,
        }

        try {
            const url = route('api.post.db.company.company.delete', ulid, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<boolean> = await authAxiosInstance.post(url);

            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            result.success = true;

            return {
                success: result.success,
            }
        } catch (e: unknown) {
            if (e instanceof Error && e.message.includes('Ziggy error')) {
                return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
            } else if (isAxiosError(e)) {
                return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
            } else {
                return {
                    success: false
                }
            }
        }
    }
}