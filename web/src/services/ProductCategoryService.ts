import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import { route, Config } from "ziggy-js";
import { ProductCategory } from "../types/models/ProductCategory";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";
import { ProductCategoryReadAnyPaginateRequest, ProductCategoryReadAnyGetRequest } from "../types/services/product-category/ProductCategoryRequest";
import { StatusCode } from "../types/enums/StatusCode";
import { client, useForm } from "laravel-precognition-vue";
import CacheService from "./CacheService";
import { DropDownOption } from "../types/models/DropDownOption";

export default class ProductCategoryService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;
    private cacheService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
        this.cacheService = new CacheService();
    }

    public useProductCategoryCreateForm() {
        const url = route('api.post.product_category.save', undefined, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', url, {
            company_id: '',
            code: '_AUTO_',
            name: '',
            type: 1,
        });

        return form;
    }

    public async readAnyPaginate(args: ProductCategoryReadAnyPaginateRequest): Promise<ServiceResponse<Collection<Array<ProductCategory>> | null>> {
        const result: ServiceResponse<Collection<Array<ProductCategory>> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, any> = {};
            if (args.with_trashed !== undefined) queryParams['with_trashed'] = args.with_trashed;
            
            queryParams['company_id'] = args.company_id;
            if (args.search) queryParams['search'] = args.search;
            if (args.type) queryParams['type'] = args.type;
            if (args.include_id) queryParams['include_id'] = args.include_id;

            queryParams['refresh'] = args.refresh;
            queryParams['paginate'] = {
                page: args.page,
                per_page: args.per_page
            };

            const url = route('api.get.product_category.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Collection<Array<ProductCategory>>> = await axios.get(url);

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

    public async readAnyGet(args: ProductCategoryReadAnyGetRequest): Promise<ServiceResponse<Resource<Array<ProductCategory>> | null>> {
        const result: ServiceResponse<Resource<Array<ProductCategory>> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, any> = {};
            if (args.with_trashed !== undefined) queryParams['with_trashed'] = args.with_trashed;
            
            queryParams['company_id'] = args.company_id;
            if (args.search) queryParams['search'] = args.search;
            if (args.type) queryParams['type'] = args.type;
            if (args.include_id) queryParams['include_id'] = args.include_id;

            queryParams['refresh'] = args.refresh;
            queryParams['get'] = {
                limit: args.limit
            };

            const url = route('api.get.product_category.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Array<ProductCategory>>> = await axios.get(url);

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

    public async read(ulid: string): Promise<ServiceResponse<ProductCategory | null>> {
        const result: ServiceResponse<ProductCategory | null> = {
            success: false
        }

        try {
            const url = route('api.get.product_category.read', {
                product_category: ulid
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<ProductCategory>> = await axios.get(url);

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

    public useProductCategoryEditForm(ulid: string) {
        const url = route('api.post.product_category.edit', {
            product_category: ulid
        }, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', url, {
            company_id: '',
            code: '_AUTO_',
            name: '',
            type: 1,
        });

        return form;
    }

    public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
        const result: ServiceResponse<boolean | null> = {
            success: false
        }

        try {
            const url = route('api.post.product_category.delete', {
                product_category: ulid
            }, false, this.ziggyRoute);

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

    public async getTypes(): Promise<Array<DropDownOption> | null> {
        const ddlName = 'productCategoryTypesDDL';
        let result: Array<DropDownOption> = [];

        try {
            if (this.cacheService.getCachedDDL(ddlName) == null) {
                const url = route('api.get.product_category.read_types', undefined, false, this.ziggyRoute);

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
