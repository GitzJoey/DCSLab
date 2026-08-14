import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import { route, type Config } from 'ziggy-js';
import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { SalesInvoice } from '../types/models/SalesInvoice';
import { StatusCode } from '../types/enums/StatusCode';
import { type Collection } from '../types/resources/Collection';
import { type Resource } from '../types/resources/Resource';
import { type ServiceResponse } from '../types/services/ServiceResponse';
import {
  type SalesInvoiceReadAnyGetRequest,
  type SalesInvoiceReadAnyPaginateRequest,
  type SalesInvoiceStoreRequest,
  type SalesInvoiceUpdateRequest,
} from '../types/services/sales-invoice/SalesInvoiceRequest';
import ErrorHandlerService from './ErrorHandlerService';

export default class SalesInvoiceService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: SalesInvoiceReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<SalesInvoice>> | null>> {
    const result: ServiceResponse<Collection<Array<SalesInvoice>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed,
        company_id: args.company_id,
        refresh: args.refresh,
        paginate: { page: args.page, per_page: args.per_page },
      };

      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.start_date) queryParams['start_date'] = args.start_date;
      if (args.end_date) queryParams['end_date'] = args.end_date;
      if (args.customer_id) queryParams['customer_id'] = args.customer_id;
      if (args.sales_order_id) queryParams['sales_order_id'] = args.sales_order_id;
      if (args.is_posted !== null && args.is_posted !== undefined) {
        queryParams['is_posted'] = args.is_posted ? 1 : 0;
      }
      if (args.is_paid_off !== null && args.is_paid_off !== undefined) {
        queryParams['is_paid_off'] = args.is_paid_off ? 1 : 0;
      }

      const url = route('api.get.sales_invoice.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<SalesInvoice>>> = await axios.get(url);

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
    args: SalesInvoiceReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<SalesInvoice>> | null>> {
    const result: ServiceResponse<Resource<Array<SalesInvoice>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed,
        company_id: args.company_id,
        refresh: args.refresh,
        get: { limit: args.limit },
      };

      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.start_date) queryParams['start_date'] = args.start_date;
      if (args.end_date) queryParams['end_date'] = args.end_date;
      if (args.customer_id) queryParams['customer_id'] = args.customer_id;
      if (args.sales_order_id) queryParams['sales_order_id'] = args.sales_order_id;
      if (args.is_posted !== null && args.is_posted !== undefined) {
        queryParams['is_posted'] = args.is_posted ? 1 : 0;
      }
      if (args.is_paid_off !== null && args.is_paid_off !== undefined) {
        queryParams['is_paid_off'] = args.is_paid_off ? 1 : 0;
      }

      const url = route('api.get.sales_invoice.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<SalesInvoice>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<SalesInvoice | null>> {
    const result: ServiceResponse<SalesInvoice | null> = { success: false };

    try {
      const url = route('api.get.sales_invoice.read', { sales_invoice: ulid }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<SalesInvoice>> = await axios.get(url);

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

  public useSalesInvoiceCreateForm() {
    const url = route('api.post.sales_invoice.save', undefined, true, this.ziggyRoute);
    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      due_days: 0,
      customer_id: null as string | null,
      sales_order_id: null as string | null,
      tax_invoice_number: '',
      tax_invoice_vat_base: 0,
      tax_invoice_vat: 0,
      remarks: '',
      is_posted: false,
      global_discount: 0,
      rounding: 0,
      items: [] as NonNullable<SalesInvoiceStoreRequest['items']>,
      payments: [] as NonNullable<SalesInvoiceStoreRequest['payments']>,
    });
  }

  public useSalesInvoiceEditForm(ulid: string) {
    const url = route('api.post.sales_invoice.edit', { sales_invoice: ulid }, true, this.ziggyRoute);
    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '',
      due_days: 0,
      customer_id: null as string | null,
      sales_order_id: null as string | null,
      tax_invoice_number: '',
      tax_invoice_vat_base: 0,
      tax_invoice_vat: 0,
      remarks: '',
      is_posted: false,
      global_discount: 0,
      rounding: 0,
      delete_item_ids: [] as NonNullable<SalesInvoiceUpdateRequest['delete_item_ids']>,
      items: [] as NonNullable<SalesInvoiceUpdateRequest['items']>,
      delete_payment_ids: [] as NonNullable<SalesInvoiceUpdateRequest['delete_payment_ids']>,
      payments: [] as NonNullable<SalesInvoiceUpdateRequest['payments']>,
    });
  }

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = { success: false };

    try {
      const url = route('api.post.sales_invoice.delete', { sales_invoice: ulid }, false, this.ziggyRoute);
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
