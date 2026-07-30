import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import { route, Config } from 'ziggy-js';
import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { StatusCode } from '../types/enums/StatusCode';
import { PurchaseReturn } from '../types/models/PurchaseReturn';
import { type Collection } from '../types/resources/Collection';
import { type Resource } from '../types/resources/Resource';
import { type ServiceResponse } from '../types/services/ServiceResponse';
import {
  type PurchaseReturnReadAnyGetRequest,
  type PurchaseReturnReadAnyPaginateRequest,
  type PurchaseReturnStoreRequest,
  type PurchaseReturnUpdateRequest,
} from '../types/services/purchase-return/PurchaseReturnRequest';
import ErrorHandlerService from './ErrorHandlerService';

export default class PurchaseReturnService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: PurchaseReturnReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<PurchaseReturn>> | null>> {
    const result: ServiceResponse<Collection<Array<PurchaseReturn>> | null> = { success: false };

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
      if (args.purchase_invoice_id) queryParams['purchase_invoice_id'] = args.purchase_invoice_id;
      if (args.warehouse_id) queryParams['warehouse_id'] = args.warehouse_id;
      if (args.is_settled !== undefined && args.is_settled !== null) queryParams['is_settled'] = args.is_settled;

      const url = route('api.get.purchase_return.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<PurchaseReturn>>> = await axios.get(url);

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
    args: PurchaseReturnReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<PurchaseReturn>> | null>> {
    const result: ServiceResponse<Resource<Array<PurchaseReturn>> | null> = { success: false };

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
      if (args.purchase_invoice_id) queryParams['purchase_invoice_id'] = args.purchase_invoice_id;
      if (args.warehouse_id) queryParams['warehouse_id'] = args.warehouse_id;
      if (args.is_settled !== undefined && args.is_settled !== null) queryParams['is_settled'] = args.is_settled;
      if (args.include_id) queryParams['include_id'] = args.include_id;

      const url = route('api.get.purchase_return.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<PurchaseReturn>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<PurchaseReturn | null>> {
    const result: ServiceResponse<PurchaseReturn | null> = { success: false };

    try {
      const url = route('api.get.purchase_return.read', { purchase_return: ulid }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<PurchaseReturn>> = await axios.get(url);

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

  public usePurchaseReturnCreateForm() {
    const url = route('api.post.purchase_return.save', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      supplier_id: null as string | null,
      purchase_invoice_id: null as string | null,
      warehouse_id: null as string | null,
      global_discount: 0,
      rounding: 0,
      remarks: '',
      is_posted: false,
      items: [] as NonNullable<PurchaseReturnStoreRequest['items']>,
      refunds: [] as NonNullable<PurchaseReturnStoreRequest['refunds']>,
    });
  }

  public usePurchaseReturnEditForm(ulid: string) {
    const url = route('api.post.purchase_return.edit', { purchase_return: ulid }, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '',
      supplier_id: null as string | null,
      purchase_invoice_id: null as string | null,
      warehouse_id: null as string | null,
      global_discount: 0,
      rounding: 0,
      remarks: '',
      is_posted: false,
      delete_item_ids: [] as NonNullable<PurchaseReturnUpdateRequest['delete_item_ids']>,
      items: [] as NonNullable<PurchaseReturnUpdateRequest['items']>,
      delete_refund_ids: [] as NonNullable<PurchaseReturnUpdateRequest['delete_refund_ids']>,
      refunds: [] as NonNullable<PurchaseReturnUpdateRequest['refunds']>,
    });
  }

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = { success: false };

    try {
      const url = route('api.post.purchase_return.delete', { purchase_return: ulid }, false, this.ziggyRoute);
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
