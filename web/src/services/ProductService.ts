import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { Product } from "../types/models/Product";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";
import { SearchRequest } from "../types/requests/SearchRequest";
import { ProductFormRequest } from "../types/requests/ProductFormRequest";
import { StatusCode } from "../types/enums/StatusCode";

export default class ProductService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public async create(
        company_id: string,
        product_group_id: string,
        brand_id: string,
        product_unit_id: string,
        payload: ProductFormRequest
    ): Promise<ServiceResponse<Product | null>> {
        const result: ServiceResponse<Product | null> = {
            success: false,
        }

        try {
            const url = route('api.post.db.product.product.save', undefined, false, this.ziggyRoute);        
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Product> = await axios.post(
                url, payload);

            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            if (response.status == StatusCode.OK) {
                result.success = true;
                result.data = response.data;
            }

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

    public async readAnyProducts(company_id: string, args: SearchRequest): Promise<ServiceResponse<Collection<Array<Product>> | Resource<Array<Product>> | null>> {
        const result: ServiceResponse<Collection<Product[]> | Resource<Product[]> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, string | number | boolean> = {};
            queryParams['company_id'] = company_id;
            queryParams['search'] = args.search ? args.search : '';
            queryParams['refresh'] = args.refresh;
            queryParams['paginate'] = args.paginate;
            queryParams['product_category'] = 'PRODUCTS';
            if (args.page) queryParams['page'] = args.page;
            if (args.per_page) queryParams['per_page'] = args.per_page;

            const url = route('api.get.db.product.product.read_any', { _query: queryParams }, false, this.ziggyRoute);

            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Collection<Product[]>> = await axios.get(url);

            if (response.status == StatusCode.OK) {
                result.success = true;
                result.data = response.data;
            }

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

    public async readAnyServices(company_id: string, args: SearchRequest): Promise<ServiceResponse<Collection<Array<Product>> | Resource<Array<Product>> | null>> {
        const result: ServiceResponse<Collection<Product[]> | Resource<Product[]> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, string | number | boolean> = {};
            queryParams['company_id'] = company_id;
            queryParams['product_category'] = 'SERVICES';
            queryParams['search'] = args.search ? args.search : '';
            queryParams['refresh'] = args.refresh;
            queryParams['paginate'] = args.paginate;
            if (args.page) queryParams['page'] = args.page;
            if (args.per_page) queryParams['per_page'] = args.per_page;

            const url = route('api.get.db.product.product.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);

            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Collection<Product[]>> = await axios.get(url);

            if (response.status == StatusCode.OK) {
                result.success = true;
                result.data = response.data;
            }

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

    public async read(ulid: string): Promise<ServiceResponse<Product | null>> {
        const result: ServiceResponse<Product | null> = {
            success: false
        }

        try {
            const url = route('api.get.db.product.product.read', {
                user: ulid
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Product>> = await axios.get(url);

            if (response.status == StatusCode.OK) {
                result.success = true;
                result.data = response.data.data;
            }

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

    public async update(
        ulid: string,
        company_id: string,
        product_group_id: string,
        brand_id: string,
        product_unit_id: string,
        payload: ProductFormRequest
    ): Promise<ServiceResponse<Product | null>> {
        const result: ServiceResponse<Product | null> = {
            success: false,
        }

        try {                    
            const url = route('api.post.db.product.product.edit', ulid, false, this.ziggyRoute);        
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();        

            const response: AxiosResponse<Product> = await axios.post(
                url, payload);
            
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            if (response.status == StatusCode.OK) {
                result.success = true;
                result.data = response.data;
            }

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
            const url = route('api.post.db.product.product.delete', ulid, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<boolean | null> = await axios.post(url);

            if (response.status == StatusCode.OK) {
                result.success = true;
            }

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