import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { EmployeeType } from "../types/resources/EmployeeType";
import { authAxiosInstance } from "../axios";
import { ServiceResponseType } from "../types/systems/ServiceResponseType";
import { AxiosError, AxiosResponse } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";

export default class EmployeeService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();

    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;

        this.errorHandlerService = new ErrorHandlerService();
    }

    public async create(
        companyIdText: string,
        codeText: string,
        nameText: string,
        emailText: string,
        addressText: string,
        cityText: string,
        postalCodeText: string,
        imgPathText: string,
        countryText: string,
        taxIdText: string,
        icNumText: string,
        joinDateText: string,
        remarksText: string,
        statusCheck: boolean,
        accessBranchIdCheck: string[],
    ): Promise<ServiceResponseType<EmployeeType | null>> {
        try {
            await authAxiosInstance.get('/sanctum/csrf-cookie');
            const response: AxiosResponse<EmployeeType> = await authAxiosInstance.post(
                'store', {
                company_id: companyIdText,
                code: codeText,
                name: nameText,
                email: emailText,
                address: addressText,
                city: cityText,
                postal_code: postalCodeText,
                img_path: imgPathText,
                country: countryText,
                tax_id: taxIdText,
                ic_num: icNumText,
                join_date: joinDateText,
                remarks: remarksText,
                status: statusCheck,
                arr_access_branch_id: accessBranchIdCheck,
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

    public async readAny(): Promise<ServiceResponseType<EmployeeType[] | null>> {
        try {
            const url = route('api.get.db.company.employee.read_any', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<EmployeeType[]> = await axios.get(url);

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

    public async read(): Promise<ServiceResponseType<EmployeeType | null>> {
        try {
            const url = route('api.get.db.company.employee.read', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<EmployeeType> = await axios.get(url);

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