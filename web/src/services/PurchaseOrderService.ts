import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import { route, Config } from 'ziggy-js';
import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import type { DropDownOption } from '../types/models/DropDownOption';
import { PurchaseOrder } from '../types/models/PurchaseOrder';
import { StatusCode } from '../types/enums/StatusCode';
import { type Collection } from '../types/resources/Collection';
import { type Resource } from '../types/resources/Resource';
import { type ServiceResponse } from '../types/services/ServiceResponse';
import {
  type PurchaseOrderReadAnyGetRequest,
  type PurchaseOrderReadAnyPaginateRequest,
  type PurchaseOrderStoreRequest,
  type PurchaseOrderUpdateRequest,
} from '../types/services/purchase-order/PurchaseOrderRequest';
import ErrorHandlerService from './ErrorHandlerService';

export default class PurchaseOrderService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readProgressStatuses(): Promise<ServiceResponse<Array<DropDownOption> | null>> {
    const result: ServiceResponse<Array<DropDownOption> | null> = {
      success: false,
    };

    try {
      const url = route(
        'api.get.purchase_order.read_progress_statuses',
        {},
        false,
        this.ziggyRoute,
      );

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
    args: PurchaseOrderReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<PurchaseOrder>> | null>> {
    const result: ServiceResponse<Collection<Array<PurchaseOrder>> | null> = {
      success: false,
    };

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
      queryParams['supplier_id'] = args.supplier_id;
      queryParams['progress_status'] = args.progress_status;
      queryParams['refresh'] = args.refresh;
      queryParams['paginate'] = {
        page: args.page,
        per_page: args.per_page,
      };

      const url = route(
        'api.get.purchase_order.read_any',
        {
          _query: queryParams,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Collection<Array<PurchaseOrder>>> = await axios.get(url);

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
    args: PurchaseOrderReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<PurchaseOrder>> | null>> {
    const result: ServiceResponse<Resource<Array<PurchaseOrder>> | null> = {
      success: false,
    };

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
      queryParams['supplier_id'] = args.supplier_id;
      queryParams['progress_status'] = args.progress_status;
      queryParams['refresh'] = args.refresh;
      queryParams['get'] = {
        limit: args.limit,
      };

      const url = route(
        'api.get.purchase_order.read_any',
        {
          _query: queryParams,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<Array<PurchaseOrder>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<PurchaseOrder | null>> {
    const result: ServiceResponse<PurchaseOrder | null> = {
      success: false,
    };

    try {
      const url = route(
        'api.get.purchase_order.read',
        {
          purchase_order: ulid,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<PurchaseOrder>> = await axios.get(url);

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

  public usePurchaseOrderCreateForm() {
    const url = route('api.post.purchase_order.save', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      due_days: 0,
      supplier_id: null,
      remarks: '',
      global_discount: 0,
      rounding: 0,
      items: [] as NonNullable<PurchaseOrderStoreRequest['items']>,
      payments: [] as NonNullable<PurchaseOrderStoreRequest['payments']>,
      refunded_payments: [] as NonNullable<PurchaseOrderStoreRequest['refunded_payments']>,
    });
  }

  public usePurchaseOrderEditForm(ulid: string) {
    const url = route(
      'api.post.purchase_order.edit',
      {
        purchase_order: ulid,
      },
      true,
      this.ziggyRoute,
    );

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '',
      due_days: 0,
      supplier_id: null,
      remarks: '',
      global_discount: 0,
      rounding: 0,
      delete_item_ids: [] as NonNullable<PurchaseOrderUpdateRequest['delete_item_ids']>,
      items: [] as NonNullable<PurchaseOrderUpdateRequest['items']>,
      delete_payment_ids: [] as NonNullable<PurchaseOrderUpdateRequest['delete_payment_ids']>,
      payments: [] as NonNullable<PurchaseOrderUpdateRequest['payments']>,
      delete_refunded_payment_ids: [] as NonNullable<PurchaseOrderUpdateRequest['delete_refunded_payment_ids']>,
      refunded_payments: [] as NonNullable<PurchaseOrderUpdateRequest['refunded_payments']>,
    });
  }

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = {
      success: false,
    };

    try {
      const url = route(
        'api.post.purchase_order.delete',
        {
          purchase_order: ulid,
        },
        false,
        this.ziggyRoute,
      );

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
