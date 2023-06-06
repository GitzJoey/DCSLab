import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { WarehouseType } from "../types/resources/WarehouseType";
import { authAxiosInstance } from "../axios";
import { ServiceResponseType } from "../types/systems/ServiceResponseType";
import { AxiosError, AxiosResponse } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";

export default class WarehouseService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public async create(
        companyIdText: string,
        branchIdText: string,
        codeText: string,
        nameText: string,
        addressText: string,
        cityText: string,
        contactText: string,
        remarksText: string,
        statusCheck: boolean,
    ): Promise<ServiceResponseType<WarehouseType | null>> {
        try {
            await authAxiosInstance.get('/sanctum/csrf-cookie');
            const response: AxiosResponse<WarehouseType> = await authAxiosInstance.post(
                'store', {
                company_id: companyIdText,
                branch_id: branchIdText,
                code: codeText,
                name: nameText,
                address: addressText,
                city: cityText,
                contact: contactText,
                remarks: remarksText,
                status: statusCheck,
            }
            );

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

    public async readAny(): Promise<ServiceResponseType<WarehouseType[] | null>> {
        try {
            const url = route('api.get.db.company.warehouse.read_any', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<WarehouseType[]> = await axios.get(url);

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

    public async read(): Promise<ServiceResponseType<WarehouseType | null>> {
        try {
            const url = route('api.get.db.company.warehouse.read', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<WarehouseType> = await axios.get(url);

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