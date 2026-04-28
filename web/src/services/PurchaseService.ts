import { AxiosError, AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import { route, Config } from 'ziggy-js';
import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { StatusCode } from '../types/enums/StatusCode';
import { Purchase } from '../types/models/Purchase';
import { Collection } from '../types/resources/Collection';
import { Resource } from '../types/resources/Resource';
import { ServiceResponse } from '../types/services/ServiceResponse';
import {
  PurchaseReadAnyGetRequest,
  PurchaseReadAnyPaginateRequest,
  PurchaseDirectStoreRequest,
  PurchaseDirectUpdateRequest,
  PurchaseManualStoreRequest,
  PurchaseManualUpdateRequest,
  PurchaseStoreRequest,
  PurchaseUpdateRequest,
} from '../types/services/purchase/PurchaseRequest';
import ErrorHandlerService from './ErrorHandlerService';

export default class PurchaseService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: PurchaseReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<Purchase>> | null>> {
    const result: ServiceResponse<Collection<Array<Purchase>> | null> = { success: false };

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
      if (args.purchase_order_id) queryParams['purchase_order_id'] = args.purchase_order_id;
      if (args.receipt_mode) queryParams['receipt_mode'] = args.receipt_mode;
      if (args.progress_status) queryParams['progress_status'] = args.progress_status;
      if (args.is_posted !== undefined && args.is_posted !== null) queryParams['is_posted'] = args.is_posted;

      const url = route('api.get.purchase.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<Purchase>>> = await axios.get(url);

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
    args: PurchaseReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<Purchase>> | null>> {
    const result: ServiceResponse<Resource<Array<Purchase>> | null> = { success: false };

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
      if (args.purchase_order_id) queryParams['purchase_order_id'] = args.purchase_order_id;
      if (args.receipt_mode) queryParams['receipt_mode'] = args.receipt_mode;
      if (args.progress_status) queryParams['progress_status'] = args.progress_status;
      if (args.is_posted !== undefined && args.is_posted !== null) queryParams['is_posted'] = args.is_posted;

      const url = route('api.get.purchase.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<Purchase>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<Purchase | null>> {
    const result: ServiceResponse<Purchase | null> = { success: false };

    try {
      const url = route(
        'api.get.purchase.read',
        {
          purchase: ulid,
        },
        false,
        this.ziggyRoute,
      );
      const response: AxiosResponse<Resource<Purchase>> = await axios.get(url);

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

  public usePurchaseCreateManualForm() {
    const url = route('api.post.purchase.save.manual', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      due_days: 0,
      supplier_id: null,
      purchase_order_id: null,
      tax_invoice_number: null,
      tax_invoice_vat_base: 0,
      tax_invoice_vat: 0,
      remarks: '',
      is_posted: false,
      additional_cost: 0,
      rounding: 0,
      items: [] as NonNullable<PurchaseManualStoreRequest['items']>,
      global_discounts: [] as NonNullable<PurchaseManualStoreRequest['global_discounts']>,
      additional_costs: [] as NonNullable<PurchaseManualStoreRequest['additional_costs']>,
    });
  }

  public usePurchaseEditForm(ulid: string) {
    return this.usePurchaseEditManualForm(ulid);
  }

  public usePurchaseCreateDirectForm() {
    const url = route('api.post.purchase.save.direct', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      due_days: 0,
      supplier_id: null,
      purchase_order_id: null,
      direct_receipt_warehouse_id: '',
      tax_invoice_number: null,
      tax_invoice_vat_base: 0,
      tax_invoice_vat: 0,
      remarks: '',
      is_posted: false,
      additional_cost: 0,
      rounding: 0,
      items: [] as NonNullable<PurchaseDirectStoreRequest['items']>,
      global_discounts: [] as NonNullable<PurchaseDirectStoreRequest['global_discounts']>,
      additional_costs: [] as NonNullable<PurchaseDirectStoreRequest['additional_costs']>,
    });
  }

  public usePurchaseEditManualForm(ulid: string) {
    const url = route(
      'api.post.purchase.edit.manual',
      {
        purchase: ulid,
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
      purchase_order_id: null,
      tax_invoice_number: null,
      tax_invoice_vat_base: 0,
      tax_invoice_vat: 0,
      remarks: '',
      is_posted: false,
      additional_cost: 0,
      rounding: 0,
      delete_item_ids: [] as NonNullable<PurchaseManualUpdateRequest['delete_item_ids']>,
      items: [] as NonNullable<PurchaseManualUpdateRequest['items']>,
      delete_global_discount_ids: [] as NonNullable<PurchaseManualUpdateRequest['delete_global_discount_ids']>,
      global_discounts: [] as NonNullable<PurchaseManualUpdateRequest['global_discounts']>,
      delete_additional_cost_ids: [] as NonNullable<PurchaseManualUpdateRequest['delete_additional_cost_ids']>,
      additional_costs: [] as NonNullable<PurchaseManualUpdateRequest['additional_costs']>,
    });
  }

  public usePurchaseEditDirectForm(ulid: string) {
    const url = route(
      'api.post.purchase.edit.direct',
      {
        purchase: ulid,
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
      purchase_order_id: null,
      direct_receipt_warehouse_id: '',
      tax_invoice_number: null,
      tax_invoice_vat_base: 0,
      tax_invoice_vat: 0,
      remarks: '',
      is_posted: false,
      additional_cost: 0,
      rounding: 0,
      delete_item_ids: [] as NonNullable<PurchaseDirectUpdateRequest['delete_item_ids']>,
      items: [] as NonNullable<PurchaseDirectUpdateRequest['items']>,
      delete_global_discount_ids: [] as NonNullable<PurchaseDirectUpdateRequest['delete_global_discount_ids']>,
      global_discounts: [] as NonNullable<PurchaseDirectUpdateRequest['global_discounts']>,
      delete_additional_cost_ids: [] as NonNullable<PurchaseDirectUpdateRequest['delete_additional_cost_ids']>,
      additional_costs: [] as NonNullable<PurchaseDirectUpdateRequest['additional_costs']>,
    });
  }

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = { success: false };

    try {
      const url = route(
        'api.post.purchase.delete',
        {
          purchase: ulid,
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
