import { AxiosError, AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import { route, Config } from 'ziggy-js';
import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { StatusCode } from '../types/enums/StatusCode';
import { PurchaseOrderReceipt } from '../types/models/PurchaseOrderReceipt';
import { Collection } from '../types/resources/Collection';
import { Resource } from '../types/resources/Resource';
import { ServiceResponse } from '../types/services/ServiceResponse';
import {
  PurchaseOrderReceiptReadAnyGetRequest,
  PurchaseOrderReceiptReadAnyPaginateRequest,
  PurchaseOrderReceiptStoreRequest,
  PurchaseOrderReceiptUpdateRequest,
} from '../types/services/purchase-order-receipt/PurchaseOrderReceiptRequest';
import ErrorHandlerService from './ErrorHandlerService';

export default class PurchaseOrderReceiptService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: PurchaseOrderReceiptReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<PurchaseOrderReceipt>> | null>> {
    const result: ServiceResponse<Collection<Array<PurchaseOrderReceipt>> | null> = { success: false };

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
      if (args.supplier_id) queryParams['supplier_id'] = args.supplier_id;
      if (args.purchase_order_id) queryParams['purchase_order_id'] = args.purchase_order_id;
      if (args.start_date) queryParams['start_date'] = args.start_date;
      if (args.end_date) queryParams['end_date'] = args.end_date;
      if (args.warehouse_id) queryParams['warehouse_id'] = args.warehouse_id;
      if (args.is_posted !== undefined && args.is_posted !== null) queryParams['is_posted'] = args.is_posted;

      const url = route(
        'api.get.purchase_order_receipt.read_any',
        { _query: queryParams },
        false,
        this.ziggyRoute,
      );
      const response: AxiosResponse<Collection<Array<PurchaseOrderReceipt>>> = await axios.get(url);

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
    args: PurchaseOrderReceiptReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<PurchaseOrderReceipt>> | null>> {
    const result: ServiceResponse<Resource<Array<PurchaseOrderReceipt>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed ? 1 : 0,
        company_id: args.company_id,
        refresh: args.refresh,
        get: {
          limit: args.limit,
        },
      };

      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.supplier_id) queryParams['supplier_id'] = args.supplier_id;
      if (args.purchase_order_id) queryParams['purchase_order_id'] = args.purchase_order_id;
      if (args.start_date) queryParams['start_date'] = args.start_date;
      if (args.end_date) queryParams['end_date'] = args.end_date;
      if (args.warehouse_id) queryParams['warehouse_id'] = args.warehouse_id;
      if (args.is_posted !== undefined && args.is_posted !== null) queryParams['is_posted'] = args.is_posted;

      const url = route(
        'api.get.purchase_order_receipt.read_any',
        { _query: queryParams },
        false,
        this.ziggyRoute,
      );
      const response: AxiosResponse<Resource<Array<PurchaseOrderReceipt>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<PurchaseOrderReceipt | null>> {
    const result: ServiceResponse<PurchaseOrderReceipt | null> = { success: false };

    try {
      const url = route(
        'api.get.purchase_order_receipt.read',
        {
          purchase_order_receipt: ulid,
        },
        false,
        this.ziggyRoute,
      );
      const response: AxiosResponse<Resource<PurchaseOrderReceipt>> = await axios.get(url);

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

  public usePurchaseOrderReceiptCreateForm() {
    const url = route('api.post.purchase_order_receipt.save', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      supplier_id: null,
      purchase_order_id: null,
      warehouse_id: null,
      remarks: '',
      is_posted: false,
      items: [] as NonNullable<PurchaseOrderReceiptStoreRequest['items']>,
      costs: [] as NonNullable<PurchaseOrderReceiptStoreRequest['costs']>,
    });
  }

  public usePurchaseOrderReceiptEditForm(ulid: string) {
    const url = route(
      'api.post.purchase_order_receipt.edit',
      {
        purchase_order_receipt: ulid,
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
      supplier_id: null,
      purchase_order_id: null,
      warehouse_id: null,
      remarks: '',
      is_posted: false,
      delete_item_ids: [] as NonNullable<PurchaseOrderReceiptUpdateRequest['delete_item_ids']>,
      items: [] as NonNullable<PurchaseOrderReceiptUpdateRequest['items']>,
      delete_cost_ids: [] as NonNullable<PurchaseOrderReceiptUpdateRequest['delete_cost_ids']>,
      costs: [] as NonNullable<PurchaseOrderReceiptUpdateRequest['costs']>,
    });
  }

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = { success: false };

    try {
      const url = route(
        'api.post.purchase_order_receipt.delete',
        {
          purchase_order_receipt: ulid,
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
