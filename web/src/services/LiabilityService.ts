import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, Config } from 'ziggy-js';
import { AxiosError, AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import ErrorHandlerService from './ErrorHandlerService';
import { StatusCode } from '../types/enums/StatusCode';
import type { Liability } from '../types/models/Liability';
import type { Collection } from '../types/resources/Collection';
import type { Resource } from '../types/resources/Resource';
import type { ServiceResponse } from '../types/services/ServiceResponse';
import type {
  LiabilityReadAnyGetRequest,
  LiabilityReadAnyPaginateRequest,
} from '../types/services/liability/LiabilityRequest';

export default class LiabilityService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: LiabilityReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<Liability>> | null>> {
    const result: ServiceResponse<Collection<Array<Liability>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed,
        company_id: args.company_id,
        refresh: args.refresh,
        paginate: { page: args.page, per_page: args.per_page },
      };
      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.category_id) queryParams['category_id'] = args.category_id;
      if (args.creditor_id) queryParams['creditor_id'] = args.creditor_id;
      if (args.supplier_id) queryParams['supplier_id'] = args.supplier_id;
      if (args.is_paid_off !== null && args.is_paid_off !== undefined) queryParams['is_paid_off'] = args.is_paid_off;
      if (args.include_id) queryParams['include_id'] = args.include_id;

      const url = route('api.get.liability.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<Liability>>> = await axios.get(url);

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
    args: LiabilityReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<Liability>> | null>> {
    const result: ServiceResponse<Resource<Array<Liability>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed ? 1 : 0,
        company_id: args.company_id,
        refresh: args.refresh,
        get: { limit: args.limit },
      };
      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.category_id) queryParams['category_id'] = args.category_id;
      if (args.creditor_id) queryParams['creditor_id'] = args.creditor_id;
      if (args.supplier_id) queryParams['supplier_id'] = args.supplier_id;
      if (args.is_paid_off !== null && args.is_paid_off !== undefined) queryParams['is_paid_off'] = args.is_paid_off ? 1 : 0;
      if (args.include_id) queryParams['include_id'] = args.include_id;

      const url = route('api.get.liability.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<Liability>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<Liability | null>> {
    const result: ServiceResponse<Liability | null> = { success: false };

    try {
      const url = route('api.get.liability.read', { liability: ulid }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Liability>> = await axios.get(url);

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

  public useLiabilityCreateForm() {
    const url = route('api.post.liability.save', undefined, true, this.ziggyRoute);
    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      category_id: '',
      creditor_id: '',
      supplier_id: '',
      cash_account_id: '',
      amount_received: 0,
      amount_payable: 0,
      due_days: 0,
      remarks: '',
      payments: [] as Array<Record<string, unknown>>,
    });
  }

  public useLiabilityEditForm(ulid: string) {
    const url = route('api.post.liability.edit', { liability: ulid }, true, this.ziggyRoute);
    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '',
      category_id: '',
      creditor_id: '',
      supplier_id: '',
      cash_account_id: '',
      amount_received: 0,
      amount_payable: 0,
      due_days: 0,
      remarks: '',
      delete_payment_ids: [] as Array<string>,
      payments: [] as Array<Record<string, unknown>>,
    });
  }

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = { success: false };

    try {
      const url = route('api.post.liability.delete', { liability: ulid }, false, this.ziggyRoute);
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
