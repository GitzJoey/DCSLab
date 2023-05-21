import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { CustomerType } from "../types/resources/CustomerType";
import { ServiceResponseType } from "../types/ServiceResponseType";
import { AxiosError, AxiosResponse } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";

export default class CustomerService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public async readAny(): Promise<ServiceResponseType<CustomerType[] | null>> {
        try {
            const url = route('api.get.db.customer.customer.read_any', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<CustomerType[]> = await axios.get(url);

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

    public async read(): Promise<ServiceResponseType<CustomerType | null>> {
        try {
            const url = route('api.get.db.customer.customer.read', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<CustomerType> = await axios.get(url);

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