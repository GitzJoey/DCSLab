import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import { route, Config } from 'ziggy-js';
import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { StatusCode } from '../types/enums/StatusCode';
import { PurchaseInvoice } from '../types/models/PurchaseInvoice';
import { type Collection } from '../types/resources/Collection';
import { type Resource } from '../types/resources/Resource';
import { type ServiceResponse } from '../types/services/ServiceResponse';
import {
  type PurchaseInvoiceReadAnyGetRequest,
  type PurchaseInvoiceReadAnyPaginateRequest,
  type PurchaseInvoiceStoreRequest,
  type PurchaseInvoiceUpdateRequest,
} from '../types/services/purchase-invoice/PurchaseInvoiceRequest';
import ErrorHandlerService from './ErrorHandlerService';

export default class PurchaseInvoiceService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: PurchaseInvoiceReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<PurchaseInvoice>> | null>> {
    const result: ServiceResponse<Collection<Array<PurchaseInvoice>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed,
        company_id: args.company_id,
        refresh: args.refresh,
        paginate: {
          page: args.page,
          per_page: args.per_page,
        },
      };

      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.start_date) queryParams['start_date'] = args.start_date;
      if (args.end_date) queryParams['end_date'] = args.end_date;
      if (args.supplier_id) queryParams['supplier_id'] = args.supplier_id;
      if (args.purchase_order_id) queryParams['purchase_order_id'] = args.purchase_order_id;
      if (args.is_posted !== undefined && args.is_posted !== null) queryParams['is_posted'] = args.is_posted;
      if (args.is_paid_off !== undefined && args.is_paid_off !== null) queryParams['is_paid_off'] = args.is_paid_off;

      const url = route('api.get.purchase_invoice.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<PurchaseInvoice>>> = await axios.get(url);

      if (response.status == StatusCode.OK) {
        result.success = true;
        result.data = response.data;
      }

      return result;
    } catch (e: unknown) {
      if (e instanceof Error && e.message.includes('Ziggy error')) {
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
      } else if (isAxiosError(e)) {
        return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
      } else {
        return result;
      }
    }
  }

  public async readAnyGet(
    args: PurchaseInvoiceReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<PurchaseInvoice>> | null>> {
    const result: ServiceResponse<Resource<Array<PurchaseInvoice>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed,
        company_id: args.company_id,
        refresh: args.refresh,
        get: {
          limit: args.limit,
        },
      };

      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.start_date) queryParams['start_date'] = args.start_date;
      if (args.end_date) queryParams['end_date'] = args.end_date;
      if (args.supplier_id) queryParams['supplier_id'] = args.supplier_id;
      if (args.purchase_order_id) queryParams['purchase_order_id'] = args.purchase_order_id;
      if (args.is_posted !== undefined && args.is_posted !== null) queryParams['is_posted'] = args.is_posted;
      if (args.is_paid_off !== undefined && args.is_paid_off !== null) queryParams['is_paid_off'] = args.is_paid_off;
      if (args.include_id) queryParams['include_id'] = args.include_id;

      const url = route('api.get.purchase_invoice.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<PurchaseInvoice>>> = await axios.get(url);

      if (response.status == StatusCode.OK) {
        result.success = true;
        result.data = response.data;
      }

      return result;
    } catch (e: unknown) {
      if (e instanceof Error && e.message.includes('Ziggy error')) {
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
      } else if (isAxiosError(e)) {
        return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
      } else {
        return result;
      }
    }
  }

  public async read(ulid: string): Promise<ServiceResponse<PurchaseInvoice | null>> {
    const result: ServiceResponse<PurchaseInvoice | null> = { success: false };

    try {
      const url = route('api.get.purchase_invoice.read', { purchase_invoice: ulid }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<PurchaseInvoice>> = await axios.get(url);

      if (response.status == StatusCode.OK) {
        result.success = true;
        result.data = response.data.data;
      }

      return result;
    } catch (e: unknown) {
      if (e instanceof Error && e.message.includes('Ziggy error')) {
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
      } else if (isAxiosError(e)) {
        return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
      } else {
        return result;
      }
    }
  }

  public usePurchaseInvoiceCreateForm() {
    const url = route('api.post.purchase_invoice.save', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      due_days: 0,
      supplier_id: null as string | null,
      purchase_order_id: null as string | null,
      tax_invoice_number: '',
      tax_invoice_vat_base: 0,
      tax_invoice_vat: 0,
      remarks: '',
      is_posted: false,
      global_discount: 0,
      rounding: 0,
      items: [] as NonNullable<PurchaseInvoiceStoreRequest['items']>,
      payments: [] as NonNullable<PurchaseInvoiceStoreRequest['payments']>,
    });
  }

  public usePurchaseInvoiceEditForm(ulid: string) {
    const url = route('api.post.purchase_invoice.edit', { purchase_invoice: ulid }, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '',
      due_days: 0,
      supplier_id: null as string | null,
      purchase_order_id: null as string | null,
      tax_invoice_number: '',
      tax_invoice_vat_base: 0,
      tax_invoice_vat: 0,
      remarks: '',
      is_posted: false,
      global_discount: 0,
      rounding: 0,
      delete_item_ids: [] as NonNullable<PurchaseInvoiceUpdateRequest['delete_item_ids']>,
      items: [] as NonNullable<PurchaseInvoiceUpdateRequest['items']>,
      delete_payment_ids: [] as NonNullable<PurchaseInvoiceUpdateRequest['delete_payment_ids']>,
      payments: [] as NonNullable<PurchaseInvoiceUpdateRequest['payments']>,
    });
  }

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = { success: false };

    try {
      const url = route('api.post.purchase_invoice.delete', { purchase_invoice: ulid }, false, this.ziggyRoute);
      const response: AxiosResponse<boolean | null> = await axios.post(url);

      if (response.status == StatusCode.OK) {
        result.success = true;
      }

      return result;
    } catch (e: unknown) {
      if (e instanceof Error && e.message.includes('Ziggy error')) {
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
      } else if (isAxiosError(e)) {
        return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
      } else {
        return result;
      }
    }
  }
}
