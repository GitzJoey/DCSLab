import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, type Config } from 'ziggy-js';
import type { PurchaseOrderDownPaymentRefund } from '../types/models/PurchaseOrderDownPaymentRefund';
import type { Resource } from '../types/resources/Resource';
import type { Collection } from '../types/resources/Collection';
import type { ServiceResponse } from '../types/services/ServiceResponse';
import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import ErrorHandlerService from './ErrorHandlerService';
import {
  type PurchaseOrderDownPaymentRefundReadAnyGetRequest,
  type PurchaseOrderDownPaymentRefundReadAnyPaginateRequest,
} from '../types/services/purchase-order-down-payment-refund/PurchaseOrderDownPaymentRefundRequest';
import { StatusCode } from '../types/enums/StatusCode';

export default class PurchaseOrderDownPaymentRefundService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: PurchaseOrderDownPaymentRefundReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<PurchaseOrderDownPaymentRefund>> | null>> {
    const result: ServiceResponse<Collection<Array<PurchaseOrderDownPaymentRefund>> | null> = { success: false };

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
      if (args.cash_account_id) queryParams['cash_account_id'] = args.cash_account_id;

      const url = route('api.get.purchase_order_down_payment_refund.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<PurchaseOrderDownPaymentRefund>>> = await axios.get(url);

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
    args: PurchaseOrderDownPaymentRefundReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<PurchaseOrderDownPaymentRefund>> | null>> {
    const result: ServiceResponse<Resource<Array<PurchaseOrderDownPaymentRefund>> | null> = { success: false };

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
      if (args.cash_account_id) queryParams['cash_account_id'] = args.cash_account_id;

      const url = route('api.get.purchase_order_down_payment_refund.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<PurchaseOrderDownPaymentRefund>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<PurchaseOrderDownPaymentRefund | null>> {
    const result: ServiceResponse<PurchaseOrderDownPaymentRefund | null> = { success: false };

    try {
      const url = route('api.get.purchase_order_down_payment_refund.read', { purchase_order_down_payment_refund: ulid }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<PurchaseOrderDownPaymentRefund>> = await axios.get(url);

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
