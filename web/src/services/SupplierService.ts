import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { SupplierType } from "../types/resources/SupplierType";
import { ServiceResponseType } from "../types/ServiceResponseType";
import { AxiosError, AxiosResponse } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";

export default class SupplierService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public async readAny(): Promise<ServiceResponseType<SupplierType[] | null>> {
        try {
            const url = route('api.get.db.supplier.supplier.read_any', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<SupplierType[]> = await axios.get(url);

            return {
                success: true,
                statusCode: response.status,
                statusDescription: response.statusText,
                data: response.data
            }
        } catch (e: unknown) {
            return this.errorHandlerService.generateErrorServiceResponse(e as AxiosError<unknown, unknown>);
        }
    }

    public async read(): Promise<ServiceResponseType<SupplierType | null>> {
        try {
            const url = route('api.get.db.supplier.supplier.read', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<SupplierType> = await axios.get(url);

            return {
                success: true,
                statusCode: response.status,
                statusDescription: response.statusText,
                data: response.data
            }
        } catch (e: unknown) {
            return this.errorHandlerService.generateErrorServiceResponse(e as AxiosError<unknown, unknown>);
        }
    }
}