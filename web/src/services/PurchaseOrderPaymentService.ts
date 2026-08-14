import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, type Config } from 'ziggy-js';
import type { PurchaseOrderPayment } from '../types/models/PurchaseOrderPayment';
import type { Resource } from '../types/resources/Resource';
import type { Collection } from '../types/resources/Collection';
import type { ServiceResponse } from '../types/services/ServiceResponse';
import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import ErrorHandlerService from './ErrorHandlerService';
import type { DropDownOption } from '../types/models/DropDownOption';
import {
  type PurchaseOrderPaymentReadAnyGetRequest,
  type PurchaseOrderPaymentReadAnyPaginateRequest,
} from '../types/services/purchase-order-payment/PurchaseOrderPaymentRequest';
import { StatusCode } from '../types/enums/StatusCode';

export default class PurchaseOrderPaymentService {
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
      const url = route('api.get.purchase_order_payment.read_allocation_statuses', {}, false, this.ziggyRoute);
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
    args: PurchaseOrderPaymentReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<PurchaseOrderPayment>> | null>> {
    const result: ServiceResponse<Collection<Array<PurchaseOrderPayment>> | null> = { success: false };

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
      if (args.purchase_order_id) queryParams['purchase_order_id'] = args.purchase_order_id;
      if (args.supplier_id) queryParams['supplier_id'] = args.supplier_id;
      if (args.cash_account_id) queryParams['cash_account_id'] = args.cash_account_id;
      if (args.allocation_status) queryParams['allocation_status'] = args.allocation_status;

      const url = route('api.get.purchase_order_payment.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<PurchaseOrderPayment>>> = await axios.get(url);

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
    args: PurchaseOrderPaymentReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<PurchaseOrderPayment>> | null>> {
    const result: ServiceResponse<Resource<Array<PurchaseOrderPayment>> | null> = { success: false };

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
      if (args.purchase_order_id) queryParams['purchase_order_id'] = args.purchase_order_id;
      if (args.supplier_id) queryParams['supplier_id'] = args.supplier_id;
      if (args.cash_account_id) queryParams['cash_account_id'] = args.cash_account_id;
      if (args.allocation_status) queryParams['allocation_status'] = args.allocation_status;

      const url = route('api.get.purchase_order_payment.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<PurchaseOrderPayment>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<PurchaseOrderPayment | null>> {
    const result: ServiceResponse<PurchaseOrderPayment | null> = { success: false };

    try {
      const url = route('api.get.purchase_order_payment.read', { purchase_order_payment: ulid }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<PurchaseOrderPayment>> = await axios.get(url);

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
