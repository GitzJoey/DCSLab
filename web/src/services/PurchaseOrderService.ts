import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import route, { Config } from "ziggy-js";
import { PurchaseOrderType } from "../types/resources/PurchaseOrderType";
import { authAxiosInstance } from "../axios";
import { ServiceResponseType } from "../types/systems/ServiceResponseType";
import { AxiosError, AxiosResponse } from "axios";
import ErrorHandlerService from "./ErrorHandlerService";

export default class PurchaseOrderService {
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
        invoiceCodeText: string,
        invoiceDateText: string,
        supplierIdText: string,
        shippingDateText: string,
        shippingAddressText: string,
        remarksText: string,
        statusCheck: boolean,
        globalDiscountIdText: string[],
        globalDiscountDiscountTypeText: string[],
        globalDiscountAmountText: number[],
        productUnitIdText: string[],
        productUnitProductUnitIdText: string[],
        productUnitQtyText: number[],
        productUnitAmountPerUnitText: number[],
        productUnitInitialPriceText: number[],
        productUnitPerUnitDiscountIdText: string[],
        productUnitPerUnitDiscountDiscountTypeText: string[],
        productUnitPerUnitDiscountAmountText: number[],
        productUnitPerUnitSubTotalDiscountIdText: string[],
        productUnitPerUnitSubTotalDiscountDiscountTypeText: string[],
        productUnitPerUnitSubTotalDiscountAmountText: number[],
        productUnitVatStatusDropDown: string[],
        productUnitVatRateText: number[],
        productUnitRemarksText: string[],
    ): Promise<ServiceResponseType<PurchaseOrderType | null>> {
        try {
            await authAxiosInstance.get('/sanctum/csrf-cookie');
            const response: AxiosResponse<PurchaseOrderType> = await authAxiosInstance.post(
                'store', {
                company_id: companyIdText,
                branch_id: branchIdText,
                invoice_code: invoiceCodeText,
                invoice_date: invoiceDateText,
                supplier_id: supplierIdText,
                shipping_date: shippingDateText,
                shipping_address: shippingAddressText,
                remarks: remarksText,
                status: statusCheck,
                global_discount_id: globalDiscountIdText,
                global_discount_discount_type: globalDiscountDiscountTypeText,
                global_discount_amount: globalDiscountAmountText,
                product_unit_id: productUnitIdText,
                product_unit_product_unit_id: productUnitProductUnitIdText,
                product_unit_qty: productUnitQtyText,
                product_unit_amount_per_unit: productUnitAmountPerUnitText,
                product_unit_initial_price: productUnitInitialPriceText,
                product_unit_per_unit_discount_id: productUnitPerUnitDiscountIdText,
                product_unit_per_unit_discount_discount_type: productUnitPerUnitDiscountDiscountTypeText,
                product_unit_per_unit_discount_amount: productUnitPerUnitDiscountAmountText,
                product_unit_per_unit_sub_total_discount_id: productUnitPerUnitSubTotalDiscountIdText,
                product_unit_per_unit_sub_total_discount_discount_type: productUnitPerUnitSubTotalDiscountDiscountTypeText,
                product_unit_per_unit_sub_total_discount_amount: productUnitPerUnitSubTotalDiscountAmountText,
                product_unit_vat_status: productUnitVatStatusDropDown,
                product_unit_vat_rate: productUnitVatRateText,
                product_unit_remarks: productUnitRemarksText,
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

    public async readAny(): Promise<ServiceResponseType<PurchaseOrderType[] | null>> {
        try {
            const url = route('api.get.db.purchase_order.purchase_order.read_any', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<PurchaseOrderType[]> = await axios.get(url);

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

    public async read(): Promise<ServiceResponseType<PurchaseOrderType | null>> {
        try {
            const url = route('api.get.db.purchase_order.purchase_order.read', undefined, false, this.ziggyRoute);
            if (!url) return this.errorHandlerService.generateZiggyUrlErrorServiceResponse();

            const response: AxiosResponse<PurchaseOrderType> = await axios.get(url);

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