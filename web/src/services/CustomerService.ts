import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { CustomerType } from "../types/resources/CustomerType";
import { authAxiosInstance } from "../axios";
import { ServiceResponseType } from "../types/systems/ServiceResponseType";
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

    public async create(
        companyIdText: string,
        codeText: string,
        isMemberText: boolean,
        nameText: string,
        customerGroupIdText: string,
        zoneText: string,
        customerAddressIdText: string[],
        customerAddressAddressText: string[],
        customerAddressCityText: string[],
        customerAddressContactText: string[],
        customerAddressIsMainCheck: boolean[],
        customerAddressRemarksText: string[],
        maxOpenInvoiceText: number,
        maxOutstandingInvoiceText: number,
        maxInvoiceAgeText: number,
        paymentTermTypeText: string,
        paymentTermText: number,
        taxableEnterpriseText: boolean,
        taxIdText: string,
        remarksText: string,
        statusText: boolean,
        picCreateUserText: string[],
        picContactPersonNameText: string[],
        picEmailText: string[],
        picPasswordText: string[],
    ): Promise<ServiceResponseType<CustomerType | null>> {
        try {
            await authAxiosInstance.get('/sanctum/csrf-cookie');
            const response: AxiosResponse<CustomerType> = await authAxiosInstance.post(
                'store', {
                company_id: companyIdText,
                code: codeText,
                is_member: isMemberText,
                name: nameText,
                customer_group_id: customerGroupIdText,
                zone: zoneText,
                arr_customer_address_id: customerAddressIdText,
                arr_customer_address_address: customerAddressAddressText,
                arr_customer_address_city: customerAddressCityText,
                arr_customer_address_contact: customerAddressContactText,
                arr_customer_address_is_main: customerAddressIsMainCheck,
                arr_customer_address_remarks: customerAddressRemarksText,
                max_open_invoice: maxOpenInvoiceText,
                max_outstanding_invoice: maxOutstandingInvoiceText,
                max_invoice_age: maxInvoiceAgeText,
                payment_term_type: paymentTermTypeText,
                payment_term: paymentTermText,
                taxable_enterprise: taxableEnterpriseText,
                tax_id: taxIdText,
                remarks: remarksText,
                status: statusText,
                pic_create_use: picCreateUserText,
                pic_contact_person_name: picContactPersonNameText,
                pic_email: picEmailText,
                pic_password: picPasswordText
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

    public async readAny({company_id, search, paginate, page , per_page, refresh} : { company_id : string, search:string, paginate : boolean , page? : number, per_page? : number, refresh? : boolean }): Promise<ServiceResponseType<CustomerType[] | null>> {
        try {
            const queryParams: Record<string, string |number |boolean> = {}
            queryParams['company_id'] = company_id 
            queryParams['search'] = search ? search : ''
            queryParams['paginate'] = paginate ? paginate : false
            queryParams['page'] = page ? page : 1
            queryParams['per_page'] = per_page ? per_page : 10
            queryParams['refresh'] = refresh ? refresh : false

            const url = route('api.get.db.customer.customer.read_any',{
                _query : queryParams
            } , false, this.ziggyRoute);
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

    public async read(customerId:string): Promise<ServiceResponseType<CustomerType | null>> {
        try {
            const url = route('api.get.db.customer.customer.read', customerId, false, this.ziggyRoute);
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