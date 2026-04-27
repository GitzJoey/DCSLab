import { AxiosError, AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import { route, Config } from 'ziggy-js';
import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { StatusCode } from '../types/enums/StatusCode';
import { PurchaseReceipt } from '../types/models/PurchaseReceipt';
import { Collection } from '../types/resources/Collection';
import { Resource } from '../types/resources/Resource';
import { ServiceResponse } from '../types/services/ServiceResponse';
import {
  PurchaseReceiptReadAnyGetRequest,
  PurchaseReceiptReadAnyPaginateRequest,
  PurchaseReceiptStoreRequest,
  PurchaseReceiptUpdateRequest,
} from '../types/services/purchase-receipt/PurchaseReceiptRequest';
import ErrorHandlerService from './ErrorHandlerService';

export default class PurchaseReceiptService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: PurchaseReceiptReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<PurchaseReceipt>> | null>> {
    const result: ServiceResponse<Collection<Array<PurchaseReceipt>> | null> = { success: false };

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
      if (args.purchase_id) queryParams['purchase_id'] = args.purchase_id;
      if (args.warehouse_id) queryParams['warehouse_id'] = args.warehouse_id;

      const url = route(
        'api.get.purchase_receipt.read_any',
        { _query: queryParams },
        false,
        this.ziggyRoute,
      );
      const response: AxiosResponse<Collection<Array<PurchaseReceipt>>> = await axios.get(url);

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
    args: PurchaseReceiptReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<PurchaseReceipt>> | null>> {
    const result: ServiceResponse<Resource<Array<PurchaseReceipt>> | null> = { success: false };

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
      if (args.start_date) queryParams['start_date'] = args.start_date;
      if (args.end_date) queryParams['end_date'] = args.end_date;
      if (args.supplier_id) queryParams['supplier_id'] = args.supplier_id;
      if (args.purchase_id) queryParams['purchase_id'] = args.purchase_id;
      if (args.warehouse_id) queryParams['warehouse_id'] = args.warehouse_id;
      if (args.include_id) queryParams['include_id'] = args.include_id;

      const url = route(
        'api.get.purchase_receipt.read_any',
        { _query: queryParams },
        false,
        this.ziggyRoute,
      );
      const response: AxiosResponse<Resource<Array<PurchaseReceipt>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<PurchaseReceipt | null>> {
    const result: ServiceResponse<PurchaseReceipt | null> = { success: false };

    try {
      const url = route(
        'api.get.purchase_receipt.read',
        {
          purchase_receipt: ulid,
        },
        false,
        this.ziggyRoute,
      );
      const response: AxiosResponse<Resource<PurchaseReceipt>> = await axios.get(url);

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

  public usePurchaseReceiptCreateForm() {
    const url = route('api.post.purchase_receipt.save.manual', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      supplier_id: null,
      purchase_id: null,
      is_from_direct_purchase: false,
      code: '_AUTO_',
      date: '_AUTO_',
      warehouse_id: null,
      remarks: '',
      is_posted: false,
      items: [] as NonNullable<PurchaseReceiptStoreRequest['items']>,
    });
  }

  public usePurchaseReceiptEditForm(ulid: string) {
    const url = route(
      'api.post.purchase_receipt.edit.manual',
      {
        purchase_receipt: ulid,
      },
      true,
      this.ziggyRoute,
    );

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      supplier_id: null,
      purchase_id: null,
      is_from_direct_purchase: false,
      code: '_AUTO_',
      date: '',
      warehouse_id: null,
      remarks: '',
      is_posted: false,
      items: [] as NonNullable<PurchaseReceiptUpdateRequest['items']>,
    });
  }

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = { success: false };

    try {
      const url = route(
        'api.post.purchase_receipt.delete.manual',
        {
          purchase_receipt: ulid,
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
