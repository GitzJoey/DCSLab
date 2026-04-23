import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, Config } from 'ziggy-js';
import { AxiosError, AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import ErrorHandlerService from './ErrorHandlerService';
import { StatusCode } from '../types/enums/StatusCode';
import type { IncomePayment } from '../types/models/IncomePayment';
import type { Collection } from '../types/resources/Collection';
import type { Resource } from '../types/resources/Resource';
import type { ServiceResponse } from '../types/services/ServiceResponse';
import type {
  IncomePaymentReadAnyGetRequest,
  IncomePaymentReadAnyPaginateRequest,
} from '../types/services/income-payment/IncomePaymentRequest';

export default class IncomePaymentService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: IncomePaymentReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<IncomePayment>> | null>> {
    const result: ServiceResponse<Collection<Array<IncomePayment>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed,
        company_id: args.company_id,
        refresh: args.refresh,
        paginate: { page: args.page, per_page: args.per_page },
      };
      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.income_id) queryParams['income_id'] = args.income_id;
      if (args.include_id) queryParams['include_id'] = args.include_id;

      const url = route('api.get.income_payment.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<IncomePayment>>> = await axios.get(url);

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
    args: IncomePaymentReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<IncomePayment>> | null>> {
    const result: ServiceResponse<Resource<Array<IncomePayment>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed ? 1 : 0,
        company_id: args.company_id,
        refresh: args.refresh,
        get: { limit: args.limit },
      };
      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.income_id) queryParams['income_id'] = args.income_id;
      if (args.include_id) queryParams['include_id'] = args.include_id;

      const url = route('api.get.income_payment.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<IncomePayment>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<IncomePayment | null>> {
    const result: ServiceResponse<IncomePayment | null> = { success: false };

    try {
      const url = route('api.get.income_payment.read', { income_payment: ulid }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<IncomePayment>> = await axios.get(url);

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

  public useIncomePaymentCreateForm() {
    const url = route('api.post.income_payment.save', undefined, true, this.ziggyRoute);
    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      income_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      cash_account_id: '',
      amount: 0,
      remarks: '',
    });
  }

  public useIncomePaymentEditForm(ulid: string) {
    const url = route('api.post.income_payment.edit', { income_payment: ulid }, true, this.ziggyRoute);
    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      income_id: '',
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
      const url = route('api.post.income_payment.delete', { income_payment: ulid }, false, this.ziggyRoute);
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
