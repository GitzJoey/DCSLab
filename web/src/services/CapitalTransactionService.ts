import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import { route, Config } from 'ziggy-js';
import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { type DropDownOption } from '../types/models/DropDownOption';
import { StatusCode } from '../types/enums/StatusCode';
import { CapitalTransaction } from '../types/models/CapitalTransaction';
import { type Collection } from '../types/resources/Collection';
import { type Resource } from '../types/resources/Resource';
import { type ServiceResponse } from '../types/services/ServiceResponse';
import {
  type CapitalTransactionReadAnyGetRequest,
  type CapitalTransactionReadAnyPaginateRequest,
  type CapitalTransactionStoreRequest,
  type CapitalTransactionUpdateRequest,
} from '../types/services/capital-transaction/CapitalTransactionRequest';
import CacheService from './CacheService';
import ErrorHandlerService from './ErrorHandlerService';

export default class CapitalTransactionService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();

  private errorHandlerService;
  private cacheService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;

    this.errorHandlerService = new ErrorHandlerService();
    this.cacheService = new CacheService();
  }

  public async readAnyPaginate(
    args: CapitalTransactionReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<CapitalTransaction>> | null>> {
    const result: ServiceResponse<Collection<Array<CapitalTransaction>> | null> = {
      success: false,
    };

    try {
      const queryParams: Record<string, any> = {};
      if (args.with_trashed !== undefined) queryParams['with_trashed'] = args.with_trashed;
      if (args.company_id) queryParams['company_id'] = args.company_id;
      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.investor_id) queryParams['investor_id'] = args.investor_id;
      if (args.cash_account_id) queryParams['cash_account_id'] = args.cash_account_id;
      if (args.type) queryParams['type'] = args.type;

      queryParams['refresh'] = args.refresh;
      queryParams['paginate'] = {
        page: args.page,
        per_page: args.per_page,
      };

      const url = route(
        'api.get.capital_transaction.read_any',
        {
          _query: queryParams,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Collection<Array<CapitalTransaction>>> = await axios.get(url);

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
    args: CapitalTransactionReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<CapitalTransaction>> | null>> {
    const result: ServiceResponse<Resource<Array<CapitalTransaction>> | null> = {
      success: false,
    };

    try {
      const queryParams: Record<string, any> = {};
      if (args.with_trashed !== undefined) queryParams['with_trashed'] = args.with_trashed;
      if (args.company_id) queryParams['company_id'] = args.company_id;
      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.investor_id) queryParams['investor_id'] = args.investor_id;
      if (args.cash_account_id) queryParams['cash_account_id'] = args.cash_account_id;
      if (args.type) queryParams['type'] = args.type;

      queryParams['refresh'] = args.refresh;
      queryParams['get'] = {
        limit: args.limit,
      };

      const url = route(
        'api.get.capital_transaction.read_any',
        {
          _query: queryParams,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<Array<CapitalTransaction>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<CapitalTransaction | null>> {
    const result: ServiceResponse<CapitalTransaction | null> = {
      success: false,
    };

    try {
      const url = route(
        'api.get.capital_transaction.read',
        {
          capital_transaction: ulid,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<CapitalTransaction>> = await axios.get(url);

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

  public useCapitalTransactionCreateForm() {
    const url = route('api.post.capital_transaction.save', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    const form = useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '',
      investor_id: '',
      cash_account_id: '',
      type: '',
      amount: 0,
      remarks: '',
    });

    return form;
  }

  public useCapitalTransactionEditForm(ulid: string) {
    const url = route(
      'api.post.capital_transaction.edit',
      {
        capital_transaction: ulid,
      },
      true,
      this.ziggyRoute,
    );

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    const form = useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '',
      investor_id: '',
      cash_account_id: '',
      type: '',
      amount: 0,
      remarks: '',
    });

    return form;
  }

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = {
      success: false,
    };

    try {
      const url = route(
        'api.post.capital_transaction.delete',
        {
          capital_transaction: ulid,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<boolean | null> = await axios.post(url);

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

  public async getTypes(): Promise<Array<DropDownOption> | null> {
    const ddlName = 'capitalTransactionTypesDDL';
    let result: Array<DropDownOption> = [];

    try {
      if (this.cacheService.getCachedDDL(ddlName) == null) {
        const url = route('api.get.capital_transaction.read_types', undefined, false, this.ziggyRoute);

        const response: AxiosResponse<Array<DropDownOption> | null> = await axios.get(url);

        this.cacheService.setCachedDDL(ddlName, response.data);
      }

      const cachedData: Array<DropDownOption> | null = this.cacheService.getCachedDDL(ddlName);

      if (cachedData != null) {
        result = cachedData as Array<DropDownOption>;
      }

      return result;
    } catch (e: unknown) {
      return result;
    }
  }
}
