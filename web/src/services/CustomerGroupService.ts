import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { CustomerGroupType } from "../types/resources/CustomerGroupType";
import { authAxiosInstance } from "../axios";
import { ServiceResponseType } from "../types/systems/ServiceResponseType";
import { AxiosError, AxiosResponse } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";

export default class CustomerGroupService {
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
        maxOpenInvoiceText: number,
        maxOutstandingInvoiceText: number,
        maxInvoiceAgeText: number,
        paymentTermTypeText: string,
        paymentTermText: number,
        sellingPointText: number,
        sellingPointMultipleText: number,
        sellAtCost: boolean,
        priceMarkupPercentText: number,
        priceMarkupNominalText: number,
        priceMarkdownPercentText: number,
        priceMarkdownNominalText: number,
        rounOnDropDown: string,
        roundDigitText: number,
        remarksText: string,
    ): Promise<ServiceResponseType<CustomerGroupType | null>> {
        try {
            await authAxiosInstance.get('/sanctum/csrf-cookie');
            const response: AxiosResponse<CustomerGroupType> = await authAxiosInstance.post(
                'store', {
                company_id: companyIdText,
                code: codeText,
                name: nameText,
                max_open_invoice: maxOpenInvoiceText,
                max_outstanding_invoice: maxOutstandingInvoiceText,
                max_invoice_age: maxInvoiceAgeText,
                payment_term_type: paymentTermTypeText,
                payment_term: paymentTermText,
                selling_point: sellingPointText,
                selling_point_multiple: sellingPointMultipleText,
                sell_at_cost: sellAtCost,
                price_markup_percent: priceMarkupPercentText,
                price_markup_nominal: priceMarkupNominalText,
                price_markdown_percent: priceMarkdownPercentText,
                price_markdown_nominal: priceMarkdownNominalText,
                round_on: rounOnDropDown,
                round_digit: roundDigitText,
                remarks: remarksText,
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

    public async readAny(): Promise<ServiceResponseType<CustomerGroupType[] | null>> {
        try {
            const url = route('api.get.db.customer.customer_group.read_any', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<CustomerGroupType[]> = await axios.get(url);

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

    public async read(): Promise<ServiceResponseType<CustomerGroupType | null>> {
        try {
            const url = route('api.get.db.customer.customer_group.read', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<CustomerGroupType> = await axios.get(url);

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