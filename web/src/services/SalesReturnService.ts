import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import { route, type Config } from 'ziggy-js';
import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { SalesReturn } from '../types/models/SalesReturn';
import { StatusCode } from '../types/enums/StatusCode';
import { type Collection } from '../types/resources/Collection';
import { type Resource } from '../types/resources/Resource';
import { type ServiceResponse } from '../types/services/ServiceResponse';
import {
  type SalesReturnReadAnyGetRequest,
  type SalesReturnReadAnyPaginateRequest,
  type SalesReturnStoreRequest,
  type SalesReturnUpdateRequest,
} from '../types/services/sales-return/SalesReturnRequest';
import ErrorHandlerService from './ErrorHandlerService';

export default class SalesReturnService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: SalesReturnReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<SalesReturn>> | null>> {
    const result: ServiceResponse<Collection<Array<SalesReturn>> | null> = { success: false };

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
      if (args.sales_invoice_id) queryParams['sales_invoice_id'] = args.sales_invoice_id;
      if (args.warehouse_id) queryParams['warehouse_id'] = args.warehouse_id;
      if (args.is_settled !== null && args.is_settled !== undefined) {
        queryParams['is_settled'] = args.is_settled ? 1 : 0;
      }

      const url = route('api.get.sales_return.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<SalesReturn>>> = await axios.get(url);

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
    args: SalesReturnReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<SalesReturn>> | null>> {
    const result: ServiceResponse<Resource<Array<SalesReturn>> | null> = { success: false };

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
      if (args.sales_invoice_id) queryParams['sales_invoice_id'] = args.sales_invoice_id;
      if (args.warehouse_id) queryParams['warehouse_id'] = args.warehouse_id;
      if (args.is_settled !== null && args.is_settled !== undefined) {
        queryParams['is_settled'] = args.is_settled ? 1 : 0;
      }

      const url = route('api.get.sales_return.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<SalesReturn>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<SalesReturn | null>> {
    const result: ServiceResponse<SalesReturn | null> = { success: false };

    try {
      const url = route('api.get.sales_return.read', { sales_return: ulid }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<SalesReturn>> = await axios.get(url);

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

  public useSalesReturnCreateForm() {
    const url = route('api.post.sales_return.save', undefined, true, this.ziggyRoute);
    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      customer_id: null as string | null,
      sales_invoice_id: null as string | null,
      warehouse_id: null as string | null,
      global_discount: 0,
      rounding: 0,
      remarks: '',
      is_posted: false,
      items: [] as NonNullable<SalesReturnStoreRequest['items']>,
      refunds: [] as NonNullable<SalesReturnStoreRequest['refunds']>,
    });
  }

  public useSalesReturnEditForm(ulid: string) {
    const url = route('api.post.sales_return.edit', { sales_return: ulid }, true, this.ziggyRoute);
    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '',
      customer_id: null as string | null,
      sales_invoice_id: null as string | null,
      warehouse_id: null as string | null,
      global_discount: 0,
      rounding: 0,
      remarks: '',
      is_posted: false,
      delete_item_ids: [] as NonNullable<SalesReturnUpdateRequest['delete_item_ids']>,
      items: [] as NonNullable<SalesReturnUpdateRequest['items']>,
      delete_refund_ids: [] as NonNullable<SalesReturnUpdateRequest['delete_refund_ids']>,
      refunds: [] as NonNullable<SalesReturnUpdateRequest['refunds']>,
    });
  }

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = { success: false };

    try {
      const url = route('api.post.sales_return.delete', { sales_return: ulid }, false, this.ziggyRoute);
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
