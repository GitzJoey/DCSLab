import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, type Config } from 'ziggy-js';
import type { SalesOrderPayment } from '../types/models/SalesOrderPayment';
import type { Resource } from '../types/resources/Resource';
import type { Collection } from '../types/resources/Collection';
import type { ServiceResponse } from '../types/services/ServiceResponse';
import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import ErrorHandlerService from './ErrorHandlerService';
import type { DropDownOption } from '../types/models/DropDownOption';
import {
  type SalesOrderPaymentReadAnyGetRequest,
  type SalesOrderPaymentReadAnyPaginateRequest,
} from '../types/services/sales-order-payment/SalesOrderPaymentRequest';
import { StatusCode } from '../types/enums/StatusCode';

export default class SalesOrderPaymentService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAllocationStatuses(): Promise<ServiceResponse<Array<DropDownOption> | null>> {
    const result: ServiceResponse<Array<DropDownOption> | null> = { success: false };

    try {
      const url = route('api.get.sales_order_payment.read_allocation_statuses', {}, false, this.ziggyRoute);
      const response: AxiosResponse<Array<DropDownOption>> = await axios.get(url);

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
      }

      return result;
    }
  }

  public async readAnyPaginate(
    args: SalesOrderPaymentReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<SalesOrderPayment>> | null>> {
    const result: ServiceResponse<Collection<Array<SalesOrderPayment>> | null> = { success: false };

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
      if (args.sales_order_id) queryParams['sales_order_id'] = args.sales_order_id;
      if (args.customer_id) queryParams['customer_id'] = args.customer_id;
      if (args.cash_account_id) queryParams['cash_account_id'] = args.cash_account_id;
      if (args.allocation_status) queryParams['allocation_status'] = args.allocation_status;

      const url = route('api.get.sales_order_payment.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<SalesOrderPayment>>> = await axios.get(url);

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
      }

      return result;
    }
  }

  public async readAnyGet(
    args: SalesOrderPaymentReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<SalesOrderPayment>> | null>> {
    const result: ServiceResponse<Resource<Array<SalesOrderPayment>> | null> = { success: false };

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
      if (args.sales_order_id) queryParams['sales_order_id'] = args.sales_order_id;
      if (args.customer_id) queryParams['customer_id'] = args.customer_id;
      if (args.cash_account_id) queryParams['cash_account_id'] = args.cash_account_id;
      if (args.allocation_status) queryParams['allocation_status'] = args.allocation_status;

      const url = route('api.get.sales_order_payment.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<SalesOrderPayment>>> = await axios.get(url);

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
      }

      return result;
    }
  }

  public async read(ulid: string): Promise<ServiceResponse<SalesOrderPayment | null>> {
    const result: ServiceResponse<SalesOrderPayment | null> = { success: false };

    try {
      const url = route('api.get.sales_order_payment.read', { sales_order_payment: ulid }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<SalesOrderPayment>> = await axios.get(url);

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
      }

      return result;
    }
  }
}
