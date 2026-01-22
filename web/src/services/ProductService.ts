import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import { route, Config } from "ziggy-js";
import { Product } from "../types/models/Product";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";
import { 
    ProductReadAnyPaginateRequest, 
    ProductReadAnyGetRequest
} from "../types/services/product/ProductRequest";
import { ProductUnitStoreRequest, ProductUnitUpdateRequest } from "../types/services/product-unit/ProductUnitRequest";
import { StatusCode } from "../types/enums/StatusCode";
import { client, useForm } from "laravel-precognition-vue";
import CacheService from "./CacheService";

export default class ProductService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;
    private cacheService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
        this.cacheService = new CacheService();
    }

    public useProductPhysicalStoreForm() {
        const url = route('api.post.product.save.physical', undefined, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', url, {
            company_id: '',
            code: '_AUTO_',
            category_id: '',
            brand_id: '',
            name: '',
            slug: '_AUTO_',
            is_taxable: false,
            vat_rate: 0,
            is_price_include_vat: false,
            is_use_serial_number: false,
            is_expirable: false,
            remarks: '',
            type: 1,
            status: 1,
            product_units: [] as ProductUnitStoreRequest[],
        });

        return form;
    }

    public useProductPhysicalUpdateForm(ulid: string) {
        const url = route('api.post.product.edit.physical', {
            product: ulid
        }, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', url, {
            company_id: '',
            code: '',
            category_id: '',
            brand_id: '',
            name: '',
            slug: '',
            is_taxable: false,
            vat_rate: 0,
            is_price_include_vat: false,
            is_use_serial_number: false,
            is_expirable: false,
            remarks: '',
            type: 1,
            status: 1,
            delete_product_unit_ids: [] as string[],
            product_units: [] as ProductUnitUpdateRequest[],
        });

        return form;
    }

    public useProductServiceStoreForm() {
        const url = route('api.post.product.save.service', undefined, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', url, {
            company_id: '',
            code: '_AUTO_',
            category_id: '',
            name: '',
            slug: '_AUTO_',
            is_taxable: false,
            vat_rate: 0,
            is_price_include_vat: false,
            remarks: '',
            status: '',
            unit_id: '',
            price: 0,
            point: 0,
        });

        return form;
    }

    public useProductServiceUpdateForm(ulid: string) {
        const url = route('api.post.product.edit.service', {
            product: ulid
        }, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', url, {
            company_id: '',
            code: '',
            category_id: '',
            name: '',
            slug: '',
            is_taxable: false,
            vat_rate: 0,
            is_price_include_vat: false,
            remarks: '',
            status: '',
            unit_id: '',
            price: 0,
            point: 0,
            product_units: [],
        });

        return form;
    }

    public async readAnyPaginate(args: ProductReadAnyPaginateRequest): Promise<ServiceResponse<Collection<Array<Product>> | null>> {
        const result: ServiceResponse<Collection<Array<Product>> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, any> = {};
            if (args.with_trashed !== undefined) queryParams['with_trashed'] = args.with_trashed;            
            if (args.company_id) queryParams['company_id'] = args.company_id;
            
            if (args.search) queryParams['search'] = args.search;
            if (args.category_id) queryParams['category_id'] = args.category_id;
            if (args.brand_id) queryParams['brand_id'] = args.brand_id;
            if (args.is_taxable !== undefined && args.is_taxable !== null) queryParams['is_taxable'] = args.is_taxable;
            if (args.vat_rate !== undefined && args.vat_rate !== null) queryParams['vat_rate'] = args.vat_rate;
            if (args.is_price_include_vat !== undefined && args.is_price_include_vat !== null) queryParams['is_price_include_vat'] = args.is_price_include_vat;
            if (args.is_use_serial_number !== undefined && args.is_use_serial_number !== null) queryParams['is_use_serial_number'] = args.is_use_serial_number;
            if (args.is_expirable !== undefined && args.is_expirable !== null) queryParams['is_expirable'] = args.is_expirable;
            if (args.type) queryParams['type'] = args.type;
            if (args.status) queryParams['status'] = args.status;
            if (args.include_id) queryParams['include_id'] = args.include_id;

            queryParams['refresh'] = args.refresh;
            queryParams['paginate'] = {
                page: args.page,
                per_page: args.per_page
            };

            const url = route('api.get.product.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Collection<Array<Product>>> = await axios.get(url);

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

    public async readAnyGet(args: ProductReadAnyGetRequest): Promise<ServiceResponse<Resource<Array<Product>> | null>> {
        const result: ServiceResponse<Resource<Array<Product>> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, any> = {};
            queryParams['with_trashed'] = args.with_trashed ? 1 : 0;
            
            queryParams['company_id'] = args.company_id;
            queryParams['search'] = args.search ? args.search : '';
            if (args.category_id) queryParams['category_id'] = args.category_id;
            if (args.brand_id) queryParams['brand_id'] = args.brand_id;
            if (args.is_taxable !== undefined && args.is_taxable !== null) queryParams['is_taxable'] = args.is_taxable ? 1 : 0;
            if (args.vat_rate !== undefined && args.vat_rate !== null) queryParams['vat_rate'] = args.vat_rate;
            if (args.is_price_include_vat !== undefined && args.is_price_include_vat !== null) queryParams['is_price_include_vat'] = args.is_price_include_vat ? 1 : 0;
            if (args.is_use_serial_number !== undefined && args.is_use_serial_number !== null) queryParams['is_use_serial_number'] = args.is_use_serial_number ? 1 : 0;
            if (args.is_expirable !== undefined && args.is_expirable !== null) queryParams['is_expirable'] = args.is_expirable ? 1 : 0;
            if (args.type) queryParams['type'] = args.type;
            if (args.status) queryParams['status'] = args.status;
            if (args.include_id) queryParams['include_id'] = args.include_id;

            queryParams['refresh'] = args.refresh;
            queryParams['get'] = {
                limit: args.limit
            };

            const url = route('api.get.product.read_any', {
                _query: queryParams
            }, false, this.ziggyRoute);

            const response: AxiosResponse<Resource<Array<Product>>> = await axios.get(url);

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
            const url = route('api.get.product.read', {
                product: ulid
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

    public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
        const result: ServiceResponse<boolean | null> = {
            success: false
        }

        try {
            const url = route('api.post.product.delete', {
                product: ulid
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
}
