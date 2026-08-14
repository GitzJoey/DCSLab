import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, type Config } from 'ziggy-js';
import type { SalesOrderPaymentRefund } from '../types/models/SalesOrderPaymentRefund';
import type { Resource } from '../types/resources/Resource';
import type { Collection } from '../types/resources/Collection';
import type { ServiceResponse } from '../types/services/ServiceResponse';
import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import ErrorHandlerService from './ErrorHandlerService';
import {
  type SalesOrderPaymentRefundReadAnyGetRequest,
  type SalesOrderPaymentRefundReadAnyPaginateRequest,
} from '../types/services/sales-order-payment-refund/SalesOrderPaymentRefundRequest';
import { StatusCode } from '../types/enums/StatusCode';

export default class SalesOrderPaymentRefundService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: SalesOrderPaymentRefundReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<SalesOrderPaymentRefund>> | null>> {
    const result: ServiceResponse<Collection<Array<SalesOrderPaymentRefund>> | null> = { success: false };

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

      const url = route('api.get.sales_order_payment_refund.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<SalesOrderPaymentRefund>>> = await axios.get(url);

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
    args: SalesOrderPaymentRefundReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<SalesOrderPaymentRefund>> | null>> {
    const result: ServiceResponse<Resource<Array<SalesOrderPaymentRefund>> | null> = { success: false };

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

      const url = route('api.get.sales_order_payment_refund.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<SalesOrderPaymentRefund>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<SalesOrderPaymentRefund | null>> {
    const result: ServiceResponse<SalesOrderPaymentRefund | null> = { success: false };

    try {
      const url = route(
        'api.get.sales_order_payment_refund.read',
        { sales_order_payment_refund: ulid },
        false,
        this.ziggyRoute,
      );
      const response: AxiosResponse<Resource<SalesOrderPaymentRefund>> = await axios.get(url);

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
