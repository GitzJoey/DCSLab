import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, Config } from 'ziggy-js';
import { PurchaseAdditionalCostPayment } from '../types/models/PurchaseAdditionalCostPayment';
import { Resource } from '../types/resources/Resource';
import { Collection } from '../types/resources/Collection';
import { ServiceResponse } from '../types/services/ServiceResponse';
import { AxiosError, AxiosResponse, isAxiosError } from 'axios';
import ErrorHandlerService from './ErrorHandlerService';
import {
  PurchaseAdditionalCostPaymentReadAnyGetRequest,
  PurchaseAdditionalCostPaymentReadAnyPaginateRequest,
} from '../types/services/purchase-additional-cost-payment/PurchaseAdditionalCostPaymentRequest';
import { StatusCode } from '../types/enums/StatusCode';
import { client, useForm } from 'laravel-precognition-vue';

export default class PurchaseAdditionalCostPaymentService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: PurchaseAdditionalCostPaymentReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<PurchaseAdditionalCostPayment>> | null>> {
    const result: ServiceResponse<Collection<Array<PurchaseAdditionalCostPayment>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed,
        company_id: args.company_id,
        refresh: args.refresh,
        paginate: { page: args.page, per_page: args.per_page },
      };
      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.purchase_additional_cost_id) queryParams['purchase_additional_cost_id'] = args.purchase_additional_cost_id;
      if (args.include_id) queryParams['include_id'] = args.include_id;

      const url = route('api.get.purchase_additional_cost_payment.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<PurchaseAdditionalCostPayment>>> = await axios.get(url);

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
    args: PurchaseAdditionalCostPaymentReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<PurchaseAdditionalCostPayment>> | null>> {
    const result: ServiceResponse<Resource<Array<PurchaseAdditionalCostPayment>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed ? 1 : 0,
        company_id: args.company_id,
        refresh: args.refresh,
        get: { limit: args.limit },
      };
      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.purchase_additional_cost_id) queryParams['purchase_additional_cost_id'] = args.purchase_additional_cost_id;
      if (args.include_id) queryParams['include_id'] = args.include_id;

      const url = route('api.get.purchase_additional_cost_payment.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<PurchaseAdditionalCostPayment>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<PurchaseAdditionalCostPayment | null>> {
    const result: ServiceResponse<PurchaseAdditionalCostPayment | null> = { success: false };

    try {
      const url = route(
        'api.get.purchase_additional_cost_payment.read',
        { purchase_additional_cost_payment: ulid },
        false,
        this.ziggyRoute,
      );
      const response: AxiosResponse<Resource<PurchaseAdditionalCostPayment>> = await axios.get(url);

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

  public usePurchaseAdditionalCostPaymentCreateForm() {
    const url = route('api.post.purchase_additional_cost_payment.save', undefined, true, this.ziggyRoute);
    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      purchase_additional_cost_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      cash_account_id: '',
      amount: 0,
      remarks: '',
    });
  }

  public usePurchaseAdditionalCostPaymentEditForm(ulid: string) {
    const url = route(
      'api.post.purchase_additional_cost_payment.edit',
      { purchase_additional_cost_payment: ulid },
      true,
      this.ziggyRoute,
    );
    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      purchase_additional_cost_id: '',
      code: '_AUTO_',
      date: '',
      cash_account_id: '',
      amount: 0,
      remarks: '',
    });
  }

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = { success: false };

    try {
      const url = route(
        'api.post.purchase_additional_cost_payment.delete',
        { purchase_additional_cost_payment: ulid },
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
