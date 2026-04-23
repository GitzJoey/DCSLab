import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, Config } from 'ziggy-js';
import { LiabilityCreditor } from '../types/models/LiabilityCreditor';
import { Resource } from '../types/resources/Resource';
import { Collection } from '../types/resources/Collection';
import { ServiceResponse } from '../types/services/ServiceResponse';
import { AxiosError, AxiosResponse, isAxiosError } from 'axios';
import ErrorHandlerService from './ErrorHandlerService';
import { StatusCode } from '../types/enums/StatusCode';
import { client, useForm } from 'laravel-precognition-vue';
import { LiabilityCreditorReadAnyGetRequest, LiabilityCreditorReadAnyPaginateRequest } from '../types/services/liability-creditor/LiabilityCreditorRequest';

export default class LiabilityCreditorService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();

  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;

    this.errorHandlerService = new ErrorHandlerService();
  }

  public useLiabilityCreditorCreateForm() {
    const url = route('api.post.liability_creditor.save', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;
    const form = useForm('post', url, {
      company_id: '',
      code: '_AUTO_',
      name: '',
      remarks: '',
    });

    return form;
  }

  public async readAnyPaginate(
    args: LiabilityCreditorReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<LiabilityCreditor>> | null>> {
    const result: ServiceResponse<Collection<Array<LiabilityCreditor>> | null> = {
      success: false,
    };

    try {
      const queryParams: Record<string, any> = {};
      if (args.with_trashed !== undefined) queryParams['with_trashed'] = args.with_trashed;
      if (args.company_id) queryParams['company_id'] = args.company_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.include_id) queryParams['include_id'] = args.include_id;

      queryParams['refresh'] = args.refresh;
      queryParams['paginate'] = {
        page: args.page,
        per_page: args.per_page,
      };

      const url = route(
        'api.get.liability_creditor.read_any',
        {
          _query: queryParams,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Collection<Array<LiabilityCreditor>>> = await axios.get(url);

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

  public async readAnyGet(args: LiabilityCreditorReadAnyGetRequest): Promise<ServiceResponse<Resource<Array<LiabilityCreditor>> | null>> {
    const result: ServiceResponse<Resource<Array<LiabilityCreditor>> | null> = {
      success: false,
    };

    try {
      const queryParams: Record<string, any> = {};
      if (args.with_trashed !== undefined) queryParams['with_trashed'] = args.with_trashed;
      if (args.company_id) queryParams['company_id'] = args.company_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.include_id) queryParams['include_id'] = args.include_id;

      queryParams['refresh'] = args.refresh;
      queryParams['get'] = {
        limit: args.limit,
      };

      const url = route(
        'api.get.liability_creditor.read_any',
        {
          _query: queryParams,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<Array<LiabilityCreditor>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<LiabilityCreditor | null>> {
    const result: ServiceResponse<LiabilityCreditor | null> = {
      success: false,
    };

    try {
      const url = route(
        'api.get.liability_creditor.read',
        {
          liability_creditor: ulid,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<LiabilityCreditor>> = await axios.get(url);

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

  public useLiabilityCreditorEditForm(ulid: string) {
    const url = route(
      'api.post.liability_creditor.edit',
      {
        liability_creditor: ulid,
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
        'api.post.liability_creditor.delete',
        {
          liability_creditor: ulid,
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
}
