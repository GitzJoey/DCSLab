import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { SupplierType } from "../types/resources/SupplierType";
import { authAxiosInstance } from "../axios";
import { ServiceResponseType } from "../types/systems/ServiceResponseType";
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

    public async create(
        companyIdText: string,
        codeText: string,
        nameText: string,
        addressText: string,
        cityText: string,
        contactText: string,
        taxableEnterpriseText: number,
        taxIdText: string,
        paymentTermTypeText: string,
        paymentTermText: number,
        remarksText: string,
        statusText: string,
        picCreateUserText: string[],
        picContactPersonNameText: string[],
        picEmailText: string[],
        picPasswordText: string[],
        supplierProductProductIdText: string[],
        supplierProductMainProductIdCheck: boolean[],
    ): Promise<ServiceResponseType<SupplierType | null>> {
        try {
            await authAxiosInstance.get('/sanctum/csrf-cookie');
            const response: AxiosResponse<SupplierType> = await authAxiosInstance.post(
                'store', {
                company_id: companyIdText,
                code: codeText,
                name: nameText,
                address: addressText,
                city: cityText,
                contact: contactText,
                taxable_enterprise: taxableEnterpriseText,
                tax_id: taxIdText,
                payment_term_type: paymentTermTypeText,
                payment_term: paymentTermText,
                remarks: remarksText,
                status: statusText,
                pic_create_use: picCreateUserText,
                pic_contact_person_name: picContactPersonNameText,
                pic_email: picEmailText,
                pic_password: picPasswordText,
                arr_supplier_product_product_id: supplierProductProductIdText,
                arr_supplier_product_main_product_id: supplierProductMainProductIdCheck,
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

    public async readAny({company_id, search, paginate, page, per_page, refresh}  : {company_id : string, search? : string, paginate? : boolean , page? : number , per_page ? : number, refresh? : boolean }): Promise<ServiceResponseType<SupplierType[] | null>> {
        try {
            const queryParams : Record<string, string | number | boolean> = {}

            queryParams['company_id'] = company_id 
            queryParams['search'] = search ? search : ''
            queryParams['paginate'] = paginate ? paginate : false
            queryParams['page'] = page ? page : 1
            queryParams['per_page'] = per_page ? per_page : 10
            queryParams['refresh'] = refresh ? refresh : false


            const url = route('api.get.db.supplier.supplier.read_any', {_query : queryParams }, false, this.ziggyRoute);
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

    public async read(supplierId : string): Promise<ServiceResponseType<SupplierType | null>> {
        try {
            const url = route('api.get.db.supplier.supplier.read', supplierId, false, this.ziggyRoute);
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
