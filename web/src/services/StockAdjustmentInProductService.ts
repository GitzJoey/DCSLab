import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import { route, type Config } from "ziggy-js";
import { StockAdjustmentInProduct } from "../types/models/StockAdjustmentInProduct";
import { type Resource } from "../types/resources/Resource";
import { type Collection } from "../types/resources/Collection";
import { type ServiceResponse } from "../types/services/ServiceResponse";
import { type AxiosError, type AxiosResponse, isAxiosError } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";
import {
    type StockAdjustmentInProductReadAnyGetRequest,
    type StockAdjustmentInProductReadAnyPaginateRequest,
} from "../types/services/stock-adjustment-in-product/StockAdjustmentInProductRequest";
import { StatusCode } from "../types/enums/StatusCode";
import { client, useForm } from "laravel-precognition-vue";

export default class StockAdjustmentInProductService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public useStockAdjustmentInProductCreateForm() {
        const url = route("api.post.stock_adjustment_in_product.save", undefined, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;

        const form = useForm("post", url, {
            company_id: "",
            branch_id: "",
            stock_adjustment_id: "",
            qty: 0,
            product_unit_id: "",
            product_unit_conversion_value: 1,
            product_unit_cogs: 0,
            remarks: "",
        });

        return form;
    }

    public useStockAdjustmentInProductEditForm(ulid: string) {
        const url = route(
            "api.post.stock_adjustment_in_product.edit",
            {
                stock_adjustment_in_product: ulid,
            },
            true,
            this.ziggyRoute
        );

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;

        const form = useForm("post", url, {
            company_id: "",
            branch_id: "",
            stock_adjustment_id: "",
            qty: 0,
            product_unit_id: "",
            product_unit_conversion_value: 1,
            product_unit_cogs: 0,
            remarks: "",
        });

        return form;
    }

    public async readAnyPaginate(
        args: StockAdjustmentInProductReadAnyPaginateRequest
    ): Promise<ServiceResponse<Collection<Array<StockAdjustmentInProduct>> | null>> {
        const result: ServiceResponse<Collection<Array<StockAdjustmentInProduct>> | null> = {
            success: false,
        };

        try {
            const queryParams: Record<string, any> = {};
            if (args.with_trashed !== undefined) queryParams["with_trashed"] = args.with_trashed;
            queryParams["company_id"] = args.company_id;
            if (args.branch_id) queryParams["branch_id"] = args.branch_id;
            if (args.stock_adjustment_id) queryParams["stock_adjustment_id"] = args.stock_adjustment_id;

            if (args.search) queryParams["search"] = args.search;

            queryParams["refresh"] = args.refresh;
            queryParams["paginate"] = {
                page: args.page,
                per_page: args.per_page,
            };

            const url = route(
                "api.get.stock_adjustment_in_product.read_any",
                {
                    _query: queryParams,
                },
                false,
                this.ziggyRoute
            );

            const response: AxiosResponse<Collection<Array<StockAdjustmentInProduct>>> = await axios.get(url);

            if (response.status == StatusCode.OK) {
                result.success = true;
                result.data = response.data;
            }

            return result;
        } catch (e: unknown) {
            if (e instanceof Error && e.message.includes("Ziggy error")) {
                return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
            } else if (isAxiosError(e)) {
                return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
            } else {
                return result;
            }
        }
    }

    public async readAnyGet(
        args: StockAdjustmentInProductReadAnyGetRequest
    ): Promise<ServiceResponse<Resource<Array<StockAdjustmentInProduct>> | null>> {
        const result: ServiceResponse<Resource<Array<StockAdjustmentInProduct>> | null> = {
            success: false,
        };

        try {
            const queryParams: Record<string, any> = {};
            queryParams["with_trashed"] = args.with_trashed;
            queryParams["company_id"] = args.company_id;
            if (args.branch_id) queryParams["branch_id"] = args.branch_id;
            if (args.stock_adjustment_id) queryParams["stock_adjustment_id"] = args.stock_adjustment_id;

            if (args.search) queryParams["search"] = args.search;

            queryParams["refresh"] = args.refresh;
            queryParams["get"] = {
                limit: args.limit,
            };

            const url = route(
                "api.get.stock_adjustment_in_product.read_any",
                {
                    _query: queryParams,
                },
                false,
                this.ziggyRoute
            );

            const response: AxiosResponse<Resource<Array<StockAdjustmentInProduct>>> = await axios.get(url);

            if (response.status == StatusCode.OK) {
                result.success = true;
                result.data = response.data;
            }

            return result;
        } catch (e: unknown) {
            if (e instanceof Error && e.message.includes("Ziggy error")) {
                return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
            } else if (isAxiosError(e)) {
                return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
            } else {
                return result;
            }
        }
    }

    public async read(ulid: string): Promise<ServiceResponse<StockAdjustmentInProduct | null>> {
        const result: ServiceResponse<StockAdjustmentInProduct | null> = {
            success: false,
        };

        try {
            const url = route(
                "api.get.stock_adjustment_in_product.read",
                {
                    stock_adjustment_in_product: ulid,
                },
                false,
                this.ziggyRoute
            );

            const response: AxiosResponse<Resource<StockAdjustmentInProduct>> = await axios.get(url);

            if (response.status == StatusCode.OK) {
                result.success = true;
                result.data = response.data.data;
            }

            return result;
        } catch (e: unknown) {
            if (e instanceof Error && e.message.includes("Ziggy error")) {
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
        };

        try {
            const url = route(
                "api.post.stock_adjustment_in_product.delete",
                {
                    stock_adjustment_in_product: ulid,
                },
                false,
                this.ziggyRoute
            );

            const response: AxiosResponse<boolean | null> = await axios.post(url);

            if (response.status == StatusCode.OK) {
                result.success = true;
            }

            return result;
        } catch (e: unknown) {
            if (e instanceof Error && e.message.includes("Ziggy error")) {
                return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
            } else if (isAxiosError(e)) {
                return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
            } else {
                return result;
            }
        }
    }
}
