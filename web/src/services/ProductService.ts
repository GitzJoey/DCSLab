import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { ProductType } from "../types/resources/ProductType";
import { authAxiosInstance } from "../axios";
import { ServiceResponseType } from "../types/ServiceResponseType";
import { AxiosError, AxiosResponse } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";

export default class ProductService {
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
        productGroupIdText: string,
        brandIdText: string,
        productTypeText: string,
        taxableSupplyCheck: boolean,
        priceIncludeVatCheck: boolean,
        standardRatedSupplyText: number,
        pointText: number,
        useSerialNumberCheck: boolean,
        hasExpiryDateCheck: boolean,
        remarksText: string,
        statusCheck: boolean,
        productUnitIdText: string[],
        productUnitCodeText: string[],
        productUnitUnitIdText: string[],
        productUnitConversionValueText: number[],
        productUnitIsBaseCheck: boolean[],
        productUnitIsPrimaryUnitCheck: boolean[],
        productUnitRemarksText: string[],
    ): Promise<ServiceResponseType<ProductType | null>> {
        try {
            await authAxiosInstance.get('/sanctum/csrf-cookie');
            const response: AxiosResponse<ProductType> = await authAxiosInstance.post(
                'store', { 
                    company_id: companyIdText, 
                    code: codeText,
                    name: nameText,
                    product_group_id: productGroupIdText,
                    brand_id: brandIdText,
                    product_type: productTypeText,
                    taxable_supply: taxableSupplyCheck,
                    price_include_vat: priceIncludeVatCheck,
                    standard_rated_supply: standardRatedSupplyText,
                    point: pointText,
                    use_serial_number: useSerialNumberCheck,
                    has_expiry_date: hasExpiryDateCheck,
                    remarks: remarksText,
                    status: statusCheck,
                    arr_product_unit_id: productUnitIdText,
                    arr_product_unit_code: productUnitCodeText,
                    arr_product_unit_unit_id: productUnitUnitIdText,
                    arr_product_unit_conversion_value: productUnitConversionValueText,
                    arr_product_unit_is_base: productUnitIsBaseCheck,
                    arr_product_unit_is_primary_unit: productUnitIsPrimaryUnitCheck,
                    arr_product_unit_remarks: productUnitRemarksText,
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

    public async readAny(): Promise<ServiceResponseType<ProductType[] | null>> {
        try {
            const url = route('api.get.db.product.product.read_any', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<ProductType[]> = await axios.get(url);

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

    public async read(): Promise<ServiceResponseType<ProductType | null>> {
        try {
            const url = route('api.get.db.product.product.read', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<ProductType> = await axios.get(url);

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