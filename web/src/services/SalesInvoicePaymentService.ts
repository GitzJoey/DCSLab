import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import { route, type Config } from 'ziggy-js';
import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { SalesInvoicePayment } from '../types/models/SalesInvoicePayment';
import { StatusCode } from '../types/enums/StatusCode';
import { type Collection } from '../types/resources/Collection';
import { type Resource } from '../types/resources/Resource';
import { type ServiceResponse } from '../types/services/ServiceResponse';
import {
  type SalesInvoicePaymentReadAnyGetRequest,
  type SalesInvoicePaymentReadAnyPaginateRequest,
} from '../types/services/sales-invoice-payment/SalesInvoicePaymentRequest';
import ErrorHandlerService from './ErrorHandlerService';

export default class SalesInvoicePaymentService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: SalesInvoicePaymentReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<SalesInvoicePayment>> | null>> {
    const result: ServiceResponse<Collection<Array<SalesInvoicePayment>> | null> = { success: false };

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
      if (args.sales_invoice_id) queryParams['sales_invoice_id'] = args.sales_invoice_id;
      if (args.payment_type) queryParams['payment_type'] = args.payment_type;
      if (args.cash_account_id) queryParams['cash_account_id'] = args.cash_account_id;
      if (args.sales_order_payment_id) queryParams['sales_order_payment_id'] = args.sales_order_payment_id;
      if (args.sales_return_id) queryParams['sales_return_id'] = args.sales_return_id;

      const url = route('api.get.sales_invoice_payment.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<SalesInvoicePayment>>> = await axios.get(url);

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
    args: SalesInvoicePaymentReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<SalesInvoicePayment>> | null>> {
    const result: ServiceResponse<Resource<Array<SalesInvoicePayment>> | null> = { success: false };

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
      if (args.sales_invoice_id) queryParams['sales_invoice_id'] = args.sales_invoice_id;
      if (args.payment_type) queryParams['payment_type'] = args.payment_type;
      if (args.cash_account_id) queryParams['cash_account_id'] = args.cash_account_id;
      if (args.sales_order_payment_id) queryParams['sales_order_payment_id'] = args.sales_order_payment_id;
      if (args.sales_return_id) queryParams['sales_return_id'] = args.sales_return_id;

      const url = route('api.get.sales_invoice_payment.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<SalesInvoicePayment>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<SalesInvoicePayment | null>> {
    const result: ServiceResponse<SalesInvoicePayment | null> = { success: false };

    try {
      const url = route('api.get.sales_invoice_payment.read', { sales_invoice_payment: ulid }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<SalesInvoicePayment>> = await axios.get(url);

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
}
