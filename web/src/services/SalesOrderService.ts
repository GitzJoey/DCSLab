import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import { route, Config } from 'ziggy-js';
import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import type { DropDownOption } from '../types/models/DropDownOption';
import { SalesOrder } from '../types/models/SalesOrder';
import { StatusCode } from '../types/enums/StatusCode';
import { type Collection } from '../types/resources/Collection';
import { type Resource } from '../types/resources/Resource';
import { type ServiceResponse } from '../types/services/ServiceResponse';
import {
  type SalesOrderReadAnyGetRequest,
  type SalesOrderReadAnyPaginateRequest,
  type SalesOrderStoreRequest,
  type SalesOrderUpdateRequest,
} from '../types/services/sales-order/SalesOrderRequest';
import ErrorHandlerService from './ErrorHandlerService';

export default class SalesOrderService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readProgressStatuses(): Promise<ServiceResponse<Array<DropDownOption> | null>> {
    const result: ServiceResponse<Array<DropDownOption> | null> = { success: false };

    try {
      const url = route('api.get.sales_order.read_progress_statuses', {}, false, this.ziggyRoute);
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
      } else {
        return result;
      }
    }
  }

  public async readAnyPaginate(
    args: SalesOrderReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<SalesOrder>> | null>> {
    const result: ServiceResponse<Collection<Array<SalesOrder>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {};
      queryParams['with_trashed'] = args.with_trashed;
      queryParams['company_id'] = args.company_id;
      queryParams['branch_id'] = args.branch_id;
      queryParams['search'] = args.search;
      if (args.start_date) {
        queryParams['start_date'] = args.start_date;
      }
      if (args.end_date) {
        queryParams['end_date'] = args.end_date;
      }
      queryParams['customer_id'] = args.customer_id;
      queryParams['progress_status'] = args.progress_status;
      queryParams['refresh'] = args.refresh;
      queryParams['paginate'] = { page: args.page, per_page: args.per_page };

      const url = route('api.get.sales_order.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<SalesOrder>>> = await axios.get(url);

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
    args: SalesOrderReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<SalesOrder>> | null>> {
    const result: ServiceResponse<Resource<Array<SalesOrder>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {};
      queryParams['with_trashed'] = args.with_trashed;
      queryParams['company_id'] = args.company_id;
      queryParams['branch_id'] = args.branch_id;
      queryParams['search'] = args.search;
      if (args.start_date) {
        queryParams['start_date'] = args.start_date;
      }
      if (args.end_date) {
        queryParams['end_date'] = args.end_date;
      }
      queryParams['customer_id'] = args.customer_id;
      queryParams['progress_status'] = args.progress_status;
      queryParams['refresh'] = args.refresh;
      queryParams['get'] = { limit: args.limit };

      const url = route('api.get.sales_order.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<SalesOrder>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<SalesOrder | null>> {
    const result: ServiceResponse<SalesOrder | null> = { success: false };

    try {
      const url = route('api.get.sales_order.read', { sales_order: ulid }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<SalesOrder>> = await axios.get(url);

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

  public useSalesOrderCreateForm() {
    const url = route('api.post.sales_order.save', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      due_days: 0,
      customer_id: null,
      remarks: '',
      global_discount: 0,
      rounding: 0,
      items: [] as NonNullable<SalesOrderStoreRequest['items']>,
      payments: [] as NonNullable<SalesOrderStoreRequest['payments']>,
      refunded_payments: [] as NonNullable<SalesOrderStoreRequest['refunded_payments']>,
    });
  }

  public useSalesOrderEditForm(ulid: string) {
    const url = route('api.post.sales_order.edit', { sales_order: ulid }, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '',
      due_days: 0,
      customer_id: null,
      remarks: '',
      global_discount: 0,
      rounding: 0,
      delete_item_ids: [] as NonNullable<SalesOrderUpdateRequest['delete_item_ids']>,
      items: [] as NonNullable<SalesOrderUpdateRequest['items']>,
      delete_payment_ids: [] as NonNullable<SalesOrderUpdateRequest['delete_payment_ids']>,
      payments: [] as NonNullable<SalesOrderUpdateRequest['payments']>,
      delete_refunded_payment_ids: [] as NonNullable<SalesOrderUpdateRequest['delete_refunded_payment_ids']>,
      refunded_payments: [] as NonNullable<SalesOrderUpdateRequest['refunded_payments']>,
    });
  }

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = { success: false };

    try {
      const url = route('api.post.sales_order.delete', { sales_order: ulid }, false, this.ziggyRoute);
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
