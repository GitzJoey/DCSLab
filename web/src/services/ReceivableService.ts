import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, Config } from 'ziggy-js';
import { AxiosError, AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import ErrorHandlerService from './ErrorHandlerService';
import { StatusCode } from '../types/enums/StatusCode';
import type { Receivable } from '../types/models/Receivable';
import type { Collection } from '../types/resources/Collection';
import type { Resource } from '../types/resources/Resource';
import type { ServiceResponse } from '../types/services/ServiceResponse';
import type {
  ReceivableReadAnyGetRequest,
  ReceivableReadAnyPaginateRequest,
} from '../types/services/receivable/ReceivableRequest';

export default class ReceivableService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: ReceivableReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<Receivable>> | null>> {
    const result: ServiceResponse<Collection<Array<Receivable>> | null> = { success: false };

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
      if (args.customer_id) queryParams['customer_id'] = args.customer_id;
      if (args.is_paid_off !== null && args.is_paid_off !== undefined) queryParams['is_paid_off'] = args.is_paid_off;
      if (args.include_id) queryParams['include_id'] = args.include_id;

      const url = route('api.get.receivable.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<Receivable>>> = await axios.get(url);

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
    args: ReceivableReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<Receivable>> | null>> {
    const result: ServiceResponse<Resource<Array<Receivable>> | null> = { success: false };

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
      if (args.customer_id) queryParams['customer_id'] = args.customer_id;
      if (args.is_paid_off !== null && args.is_paid_off !== undefined) queryParams['is_paid_off'] = args.is_paid_off ? 1 : 0;
      if (args.include_id) queryParams['include_id'] = args.include_id;

      const url = route('api.get.receivable.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<Receivable>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<Receivable | null>> {
    const result: ServiceResponse<Receivable | null> = { success: false };

    try {
      const url = route('api.get.receivable.read', { receivable: ulid }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Receivable>> = await axios.get(url);

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

  public useReceivableCreateForm() {
    const url = route('api.post.receivable.save', undefined, true, this.ziggyRoute);
    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      category_id: '',
      customer_id: '',
      cash_account_id: '',
      direct_amount_received: 0,
      opening_amount_due: 0,
      due_days: 0,
      remarks: '',
      payments: [] as Array<Record<string, unknown>>,
    });
  }

  public useReceivableEditForm(ulid: string) {
    const url = route('api.post.receivable.edit', { receivable: ulid }, true, this.ziggyRoute);
    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '',
      category_id: '',
      customer_id: '',
      cash_account_id: '',
      direct_amount_received: 0,
      opening_amount_due: 0,
      due_days: 0,
      remarks: '',
      delete_payment_ids: [] as Array<string>,
      payments: [] as Array<Record<string, unknown>>,
    });
  }

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = { success: false };

    try {
      const url = route('api.post.receivable.delete', { receivable: ulid }, false, this.ziggyRoute);
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

