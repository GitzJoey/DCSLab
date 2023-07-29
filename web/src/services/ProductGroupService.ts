import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import CacheService from "./CacheService";
import { ProductGroup } from "../types/models/ProductGroup";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";
import { SearchRequest } from "../types/requests/SearchRequest";
import { ProductGroupFormRequest } from "../types/requests/ProductGroupFormRequest";
import { DropDownOption } from "../types/services/DropDownOption";
import { Pause } from "lucide";

export default class ProductGroupService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();
    
    private cacheService;
    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.cacheService = new CacheService();
        this.errorHandlerService = new ErrorHandlerService();
    }

    public async create(company_id: string, payload: ProductGroupFormRequest): Promise<ServiceResponse<ProductGroup | null>> {
        const result: ServiceResponse<ProductGroup | null> = {
            success: false,
        }

        try {
            const url = route('api.post.db.product.product_group.save', undefined, false, this.ziggyRoute);        
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<ProductGroup> = await axios.post(
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

    public async readAny(company_id: string, args: SearchRequest): Promise<ServiceResponse<Collection<Array<ProductGroup>> | Resource<Array<ProductGroup>> | null>> {
        const result: ServiceResponse<Collection<ProductGroup[]> | Resource<ProductGroup[]> | null> = {
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

            const url = route('api.get.db.product.product_group.read_any', { _query: queryParams }, false, this.ziggyRoute);

            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<Collection<ProductGroup[]>> = await axios.get(url);

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

    public async read(ulid: string): Promise<ServiceResponse<ProductGroup | null>> {
        const result: ServiceResponse<ProductGroup | null> = {
            success: false
        }

        try {
            const url = route('api.get.db.product.product_group.read', {
                user: ulid
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<ProductGroup>> = await axios.get(url);

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

    public async update(ulid: string, company_id: string, payload: ProductGroupFormRequest): Promise<ServiceResponse<ProductGroup | null>> {
        const result: ServiceResponse<ProductGroup | null> = {
            success: false,
        }

        try {
            const url = route('api.post.db.product.product_group.edit', ulid, false, this.ziggyRoute);        
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();        

            const response: AxiosResponse<ProductGroup> = await axios.post(
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
            const url = route('api.post.db.product.product_group.delete', ulid, false, this.ziggyRoute);
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

    public async getProductGroupDDL(company_id: string): Promise<Array<DropDownOption> | null> {
        const ddlName = 'productGroupDDL';
        let result: Array<DropDownOption> = [];

        try {
            if (this.cacheService.getCachedDDL(ddlName) == null) {
                const queryParams: Record<string, string | number | boolean> = {};
                queryParams['company_id'] = company_id;
                queryParams['category'] = 'PRODUCTS';

                const url = route('api.get.db.product.product_group.ddl.list.productgroups', { _query: queryParams }, false, this.ziggyRoute);

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
    
    public async getProductGroupCategoryDDL(): Promise<Array<DropDownOption> | null> {
        const ddlName = 'categoryDDL';
        let result: Array<DropDownOption> = [];

        try {
            if (this.cacheService.getCachedDDL(ddlName) == null) {
                const url = route('api.get.db.product.product_group.read.productgroup.categories', undefined, false, this.ziggyRoute);

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
}