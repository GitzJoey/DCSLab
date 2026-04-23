import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, Config } from 'ziggy-js';
import { DebtCategory } from '../types/models/DebtCategory';
import { Resource } from '../types/resources/Resource';
import { Collection } from '../types/resources/Collection';
import { ServiceResponse } from '../types/services/ServiceResponse';
import { AxiosError, AxiosResponse, isAxiosError } from 'axios';
import ErrorHandlerService from './ErrorHandlerService';
import {
  DebtCategoryReadAnyPaginateRequest,
  DebtCategoryReadAnyGetRequest,
} from '../types/services/debt-category/DebtCategoryRequest';
import { StatusCode } from '../types/enums/StatusCode';
import { client, useForm } from 'laravel-precognition-vue';

export default class DebtCategoryService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();

  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public useDebtCategoryCreateForm() {
    const url = route('api.post.debt_category.save', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;
    const form = useForm('post', url, {
      company_id: '',
      code: '_AUTO_',
      name: '',
      sequence: 0,
    });

    return form;
  }

  public async readAnyPaginate(
    args: DebtCategoryReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<DebtCategory>> | null>> {
    const result: ServiceResponse<Collection<Array<DebtCategory>> | null> = {
      success: false,
    };

    try {
      const queryParams: Record<string, any> = {};
      if (args.with_trashed !== undefined) queryParams['with_trashed'] = args.with_trashed;
      queryParams['company_id'] = args.company_id;

      if (args.search) queryParams['search'] = args.search;
      if (args.include_id) queryParams['include_id'] = args.include_id;

      queryParams['refresh'] = args.refresh;
      queryParams['paginate'] = {
        page: args.page,
        per_page: args.per_page,
      };

      const url = route(
        'api.get.debt_category.read_any',
        {
          _query: queryParams,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Collection<Array<DebtCategory>>> = await axios.get(url);

      if (response.status == StatusCode.OK) {
        result.success = true;
        result.data = response.data;
      }

      return result;
    } catch (e: unknown) {
      if (e instanceof Error && e.message.includes('Ziggy error')) {
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
      } else if (isAxiosError(e)) {
        if (e.response) {
          switch (e.response.status) {
            case StatusCode.UnprocessableEntity:
              return this.errorHandlerService.generateAxiosValidationErrorServiceResponse(e as AxiosError);
            default:
              return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
          }
        } else {
          return result;
        }
      } else {
        return result;
      }
    }
  }

  public async readAnyGet(
    args: DebtCategoryReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<DebtCategory>> | null>> {
    const result: ServiceResponse<Resource<Array<DebtCategory>> | null> = {
      success: false,
    };

    try {
      const queryParams: Record<string, any> = {};
      queryParams['with_trashed'] = args.with_trashed ? 1 : 0;
      queryParams['company_id'] = args.company_id;
      queryParams['search'] = args.search ? args.search : '';
      if (args.include_id) queryParams['include_id'] = args.include_id;
      queryParams['refresh'] = args.refresh;
      queryParams['get'] = {
        limit: args.limit,
      };

      const url = route(
        'api.get.debt_category.read_any',
        {
          _query: queryParams,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<Array<DebtCategory>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<DebtCategory | null>> {
    const result: ServiceResponse<DebtCategory | null> = {
      success: false,
    };

    try {
      const url = route(
        'api.get.debt_category.read',
        {
          debt_category: ulid,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<DebtCategory>> = await axios.get(url);

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

  public useDebtCategoryEditForm(ulid: string) {
    const url = route(
      'api.post.debt_category.edit',
      {
        debt_category: ulid,
      },
      true,
      this.ziggyRoute,
    );

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;
    const form = useForm('post', url, {
      company_id: '',
      code: '_AUTO_',
      name: '',
      sequence: 0,
    });

    return form;
  }

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = {
      success: false,
    };

    try {
      const url = route(
        'api.post.debt_category.delete',
        {
          debt_category: ulid,
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
