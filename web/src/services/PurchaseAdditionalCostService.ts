import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, Config } from 'ziggy-js';
import { PurchaseAdditionalCost } from '../types/models/PurchaseAdditionalCost';
import { Resource } from '../types/resources/Resource';
import { Collection } from '../types/resources/Collection';
import { ServiceResponse } from '../types/services/ServiceResponse';
import { AxiosError, AxiosResponse, isAxiosError } from 'axios';
import ErrorHandlerService from './ErrorHandlerService';
import {
  PurchaseAdditionalCostReadAnyGetRequest,
  PurchaseAdditionalCostReadAnyPaginateRequest,
} from '../types/services/purchase-additional-cost/PurchaseAdditionalCostRequest';
import { StatusCode } from '../types/enums/StatusCode';
import { client, useForm } from 'laravel-precognition-vue';

export default class PurchaseAdditionalCostService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: PurchaseAdditionalCostReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<PurchaseAdditionalCost>> | null>> {
    const result: ServiceResponse<Collection<Array<PurchaseAdditionalCost>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed,
        company_id: args.company_id,
        refresh: args.refresh,
        paginate: { page: args.page, per_page: args.per_page },
      };
      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.purchase_id) queryParams['purchase_id'] = args.purchase_id;
      if (args.purchase_additional_cost_category_id) {
        queryParams['purchase_additional_cost_category_id'] = args.purchase_additional_cost_category_id;
      }
      if (args.is_amount_payable_paid_off !== null && args.is_amount_payable_paid_off !== undefined) {
        queryParams['is_amount_payable_paid_off'] = args.is_amount_payable_paid_off;
      }
      if (args.include_id) queryParams['include_id'] = args.include_id;

      const url = route('api.get.purchase_additional_cost.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<PurchaseAdditionalCost>>> = await axios.get(url);

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
    args: PurchaseAdditionalCostReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<PurchaseAdditionalCost>> | null>> {
    const result: ServiceResponse<Resource<Array<PurchaseAdditionalCost>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed ? 1 : 0,
        company_id: args.company_id,
        refresh: args.refresh,
        get: { limit: args.limit },
      };
      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.purchase_id) queryParams['purchase_id'] = args.purchase_id;
      if (args.purchase_additional_cost_category_id) {
        queryParams['purchase_additional_cost_category_id'] = args.purchase_additional_cost_category_id;
      }
      if (args.is_amount_payable_paid_off !== null && args.is_amount_payable_paid_off !== undefined) {
        queryParams['is_amount_payable_paid_off'] = args.is_amount_payable_paid_off;
      }
      if (args.include_id) queryParams['include_id'] = args.include_id;

      const url = route('api.get.purchase_additional_cost.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<PurchaseAdditionalCost>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<PurchaseAdditionalCost | null>> {
    const result: ServiceResponse<PurchaseAdditionalCost | null> = { success: false };

    try {
      const url = route('api.get.purchase_additional_cost.read', { purchase_additional_cost: ulid }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<PurchaseAdditionalCost>> = await axios.get(url);

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

  public usePurchaseAdditionalCostCreateForm() {
    const url = route('api.post.purchase_additional_cost.save', undefined, true, this.ziggyRoute);
    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      purchase_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      due_days: 0,
      purchase_additional_cost_category_id: '',
      paid_immediately_cash_account_id: '',
      amount_paid_immediately: 0,
      amount_payable: 0,
      remarks: '',
    });
  }

  public usePurchaseAdditionalCostEditForm(ulid: string) {
    const url = route(
      'api.post.purchase_additional_cost.edit',
      { purchase_additional_cost: ulid },
      true,
      this.ziggyRoute,
    );
    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      purchase_id: '',
      code: '_AUTO_',
      date: '',
      due_days: 0,
      purchase_additional_cost_category_id: '',
      paid_immediately_cash_account_id: '',
      amount_paid_immediately: 0,
      amount_payable: 0,
      remarks: '',
    });
  }

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = { success: false };

    try {
      const url = route('api.post.purchase_additional_cost.delete', { purchase_additional_cost: ulid }, false, this.ziggyRoute);
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
