import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { Product } from "../types/models/Product";
import { Resource } from "../types/resources/Resource";
import { Collection } from "../types/resources/Collection";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";
import { ReadAnyRequest } from "../types/services/ServiceRequest";
import { StatusCode } from "../types/enums/StatusCode";
import { client, useForm } from "laravel-precognition-vue";

export default class ProductService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public useProductCreateForm() {
        const url = route('api.post.db.product.product.save', undefined, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        const form = useForm('post', url, {
            company_id: '',
            code: '',
            name: '',
            product_group_id: '',
            brand_id: '',
            product_type: '',
            taxable_supply: '',
            price_include_vat: '',
            standard_rated_supply: '',
            point: '',
            use_serial_number: '',
            has_expiry_date: '',
            remarks: '',
            status: '',
            arr_product_unit_id: '',
            arr_product_unit_code: '',
            arr_product_unit_unit_id: '',
            arr_product_unit_conversion_value: '',
            arr_product_unit_is_base: '',
            arr_product_unit_is_primary_unit: '',
            arr_product_unit_remarks: '',
        });

        return form;
    }

    public async readAny(args: ReadAnyRequest): Promise<ServiceResponse<Collection<Array<Product>> | Resource<Array<Product>> | null>> {
        const result: ServiceResponse<Collection<Array<Product>> | Resource<Array<Product>> | null> = {
            success: false
        }

        try {
            const queryParams: Record<string, string | number | boolean> = {};
            if (args.company_id) {
                queryParams['company_id'] = args.company_id;
            }
            queryParams['search'] = args.search ? args.search : '';
            queryParams['refresh'] = args.refresh;
            queryParams['paginate'] = args.paginate;
            if (args.page) queryParams['page'] = args.page;
            if (args.per_page) queryParams['per_page'] = args.per_page;

            const url = route('api.get.db.product.product.read_any', {
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

    public async read(ulid: string): Promise<ServiceResponse<Product | null>> {
        const result: ServiceResponse<Product | null> = {
            success: false
        }

        try {
            const url = route('api.get.db.product.product.read', {
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

    public useProductEditForm(ulid: string) {
        const url = route('api.post.db.product.product.edit', ulid, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        const form = useForm('post', url, {
            company_id: '',
            code: '',
            name: '',
            product_group_id: '',
            brand_id: '',
            product_type: '',
            taxable_supply: '',
            price_include_vat: '',
            standard_rated_supply: '',
            point: '',
            use_serial_number: '',
            has_expiry_date: '',
            remarks: '',
            status: '',
            arr_product_unit_id: '',
            arr_product_unit_code: '',
            arr_product_unit_unit_id: '',
            arr_product_unit_conversion_value: '',
            arr_product_unit_is_base: '',
            arr_product_unit_is_primary_unit: '',
            arr_product_unit_remarks: '',
        });

        return form;
    }

    public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
        const result: ServiceResponse<boolean | null> = {
            success: false,
        }

        try {
            const url = route('api.post.db.product.product.delete', ulid, false, this.ziggyRoute);

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