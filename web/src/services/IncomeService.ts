import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, Config } from 'ziggy-js';
import { AxiosError, AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import ErrorHandlerService from './ErrorHandlerService';
import { StatusCode } from '../types/enums/StatusCode';
import type { Income } from '../types/models/Income';
import type { Collection } from '../types/resources/Collection';
import type { Resource } from '../types/resources/Resource';
import type { ServiceResponse } from '../types/services/ServiceResponse';
import type {
  IncomeReadAnyGetRequest,
  IncomeReadAnyPaginateRequest,
} from '../types/services/income/IncomeRequest';

export default class IncomeService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: IncomeReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<Income>> | null>> {
    const result: ServiceResponse<Collection<Array<Income>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed,
        company_id: args.company_id,
        refresh: args.refresh,
        paginate: { page: args.page, per_page: args.per_page },
      };
      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.income_category_id) queryParams['income_category_id'] = args.income_category_id;
      if (args.is_amount_receivable_paid_off !== null && args.is_amount_receivable_paid_off !== undefined) {
        queryParams['is_amount_receivable_paid_off'] = args.is_amount_receivable_paid_off;
      }
      if (args.include_id) queryParams['include_id'] = args.include_id;

      const url = route('api.get.income.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<Income>>> = await axios.get(url);

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
    args: IncomeReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<Income>> | null>> {
    const result: ServiceResponse<Resource<Array<Income>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed ? 1 : 0,
        company_id: args.company_id,
        refresh: args.refresh,
        get: { limit: args.limit },
      };
      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.income_category_id) queryParams['income_category_id'] = args.income_category_id;
      if (args.is_amount_receivable_paid_off !== null && args.is_amount_receivable_paid_off !== undefined) {
        queryParams['is_amount_receivable_paid_off'] = args.is_amount_receivable_paid_off ? 1 : 0;
      }
      if (args.include_id) queryParams['include_id'] = args.include_id;

      const url = route('api.get.income.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<Income>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<Income | null>> {
    const result: ServiceResponse<Income | null> = { success: false };

    try {
      const url = route('api.get.income.read', { income: ulid }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Income>> = await axios.get(url);

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

  public useIncomeCreateForm() {
    const url = route('api.post.income.save', undefined, true, this.ziggyRoute);
    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      income_category_id: '',
      paid_immediately_cash_account_id: '',
      amount_paid_immediately: 0,
      amount_receivable: 0,
      due_days: 0,
      remarks: '',
      image_hashes: [] as {
        hash: string;
        is_main: boolean;
      }[],
      payments: [] as Array<Record<string, unknown>>,
    });
  }

  public useIncomeEditForm(ulid: string) {
    const url = route('api.post.income.edit', { income: ulid }, true, this.ziggyRoute);
    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '',
      income_category_id: '',
      paid_immediately_cash_account_id: '',
      amount_paid_immediately: 0,
      amount_receivable: 0,
      due_days: 0,
      remarks: '',
      delete_image_ids: [] as Array<string>,
      image_hashes: [] as {
        hash: string;
        is_main: boolean;
      }[],
      delete_payment_ids: [] as Array<string>,
      payments: [] as Array<Record<string, unknown>>,
    });
  }

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = { success: false };

    try {
      const url = route('api.post.income.delete', { income: ulid }, false, this.ziggyRoute);
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
