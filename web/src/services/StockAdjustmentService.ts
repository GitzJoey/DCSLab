import { type AxiosError, type AxiosResponse, isAxiosError } from "axios";
import { client, useForm } from "laravel-precognition-vue";
import { route, Config } from "ziggy-js";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import { StatusCode } from "../types/enums/StatusCode";
import { StockAdjustment } from "../types/models/StockAdjustment";
import { type Collection } from "../types/resources/Collection";
import { type Resource } from "../types/resources/Resource";
import { type ServiceResponse } from "../types/services/ServiceResponse";
import {
    type StockAdjustmentReadAnyGetRequest,
    type StockAdjustmentReadAnyPaginateRequest,
    type StockAdjustmentStoreRequest,
    type StockAdjustmentUpdateRequest,
} from "../types/services/stock-adjustment/StockAdjustmentRequest";
import axios from "../axios";
import ErrorHandlerService from "./ErrorHandlerService";

export default class StockAdjustmentService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public useStockAdjustmentCreateForm() {
        const url = route("api.post.stock_adjustment.save", undefined, true, this.ziggyRoute);

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;

        const form = useForm("post", url, {
            company_id: "",
            branch_id: "",
            code: "_AUTO_",
            date: "",
            category_id: "",
            in_warehouse_id: "",
            out_warehouse_id: "",
            remarks: "",
            is_posted: true,
            in_products: [] as NonNullable<StockAdjustmentStoreRequest["in_products"]>,
            out_products: [] as NonNullable<StockAdjustmentStoreRequest["out_products"]>,
        });

        return form;
    }

    public useStockAdjustmentEditForm(ulid: string) {
        const url = route(
            "api.post.stock_adjustment.edit",
            {
                stock_adjustment: ulid,
            },
            true,
            this.ziggyRoute
        );

        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;

        const form = useForm("post", url, {
            company_id: "",
            branch_id: "",
            code: "_AUTO_",
            date: "",
            category_id: "",
            in_warehouse_id: "",
            out_warehouse_id: "",
            remarks: "",
            is_posted: false,
            delete_in_product_ids: [] as NonNullable<StockAdjustmentUpdateRequest["delete_in_product_ids"]>,
            in_products: [] as NonNullable<StockAdjustmentUpdateRequest["in_products"]>,
            delete_out_product_ids: [] as NonNullable<StockAdjustmentUpdateRequest["delete_out_product_ids"]>,
            out_products: [] as NonNullable<StockAdjustmentUpdateRequest["out_products"]>,
        });

        return form;
    }

    public async readAnyPaginate(
        args: StockAdjustmentReadAnyPaginateRequest
    ): Promise<ServiceResponse<Collection<Array<StockAdjustment>> | null>> {
        const result: ServiceResponse<Collection<Array<StockAdjustment>> | null> = {
            success: false,
        };

        try {
            const queryParams: Record<string, any> = {};
            if (args.with_trashed !== undefined) queryParams["with_trashed"] = args.with_trashed;
            queryParams["company_id"] = args.company_id;
            if (args.branch_id) queryParams["branch_id"] = args.branch_id;

            if (args.search) queryParams["search"] = args.search;

            queryParams["refresh"] = args.refresh;
            queryParams["paginate"] = {
                page: args.page,
                per_page: args.per_page,
            };

            const url = route(
                "api.get.stock_adjustment.read_any",
                {
                    _query: queryParams,
                },
                false,
                this.ziggyRoute
            );

            const response: AxiosResponse<Collection<Array<StockAdjustment>>> = await axios.get(url);

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
        args: StockAdjustmentReadAnyGetRequest
    ): Promise<ServiceResponse<Resource<Array<StockAdjustment>> | null>> {
        const result: ServiceResponse<Resource<Array<StockAdjustment>> | null> = {
            success: false,
        };

        try {
            const queryParams: Record<string, any> = {};
            queryParams["with_trashed"] = args.with_trashed;
            queryParams["company_id"] = args.company_id;
            if (args.branch_id) queryParams["branch_id"] = args.branch_id;

            if (args.search) queryParams["search"] = args.search;

            queryParams["refresh"] = args.refresh;
            queryParams["get"] = {
                limit: args.limit,
            };

            const url = route(
                "api.get.stock_adjustment.read_any",
                {
                    _query: queryParams,
                },
                false,
                this.ziggyRoute
            );

            const response: AxiosResponse<Resource<Array<StockAdjustment>>> = await axios.get(url);

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

    public async read(ulid: string): Promise<ServiceResponse<StockAdjustment | null>> {
        const result: ServiceResponse<StockAdjustment | null> = {
            success: false,
        };

        try {
            const url = route(
                "api.get.stock_adjustment.read",
                {
                    stock_adjustment: ulid,
                },
                false,
                this.ziggyRoute
            );

            const response: AxiosResponse<Resource<StockAdjustment>> = await axios.get(url);

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
                "api.post.stock_adjustment.delete",
                {
                    stock_adjustment: ulid,
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
