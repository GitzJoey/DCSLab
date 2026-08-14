import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, Config } from 'ziggy-js';
import { Asset } from '../types/models/Asset';
import { Resource } from '../types/resources/Resource';
import { Collection } from '../types/resources/Collection';
import { ServiceResponse } from '../types/services/ServiceResponse';
import { AxiosError, AxiosResponse, isAxiosError } from 'axios';
import ErrorHandlerService from './ErrorHandlerService';
import { AssetReadAnyPaginateRequest, AssetReadAnyGetRequest } from '../types/services/asset/AssetRequest';
import { StatusCode } from '../types/enums/StatusCode';
import { client, useForm } from 'laravel-precognition-vue';

export default class AssetService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();

  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public useAssetCreateForm() {
    const url = route('api.post.asset.save', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;
    const form = useForm('post', url, {
      company_id: '',
      asset_category_id: '',
      code: '_AUTO_',
      name: '',
      asset_unit_id: '',
      status: 1,
      remarks: '',
    });

    return form;
  }

  public async readAnyPaginate(
    args: AssetReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<Asset>> | null>> {
    const result: ServiceResponse<Collection<Array<Asset>> | null> = {
      success: false,
    };

    try {
      const queryParams: Record<string, any> = {};
      if (args.with_trashed !== undefined) queryParams['with_trashed'] = args.with_trashed;
      queryParams['company_id'] = args.company_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.asset_category_id) queryParams['asset_category_id'] = args.asset_category_id;
      if (args.asset_unit_id) queryParams['asset_unit_id'] = args.asset_unit_id;
      if (args.status !== undefined && args.status !== null && args.status !== '') queryParams['status'] = args.status;
      if (args.include_id) queryParams['include_id'] = args.include_id;
      queryParams['refresh'] = args.refresh;
      queryParams['paginate'] = {
        page: args.page,
        per_page: args.per_page,
      };

      const url = route(
        'api.get.asset.read_any',
        {
          _query: queryParams,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Collection<Array<Asset>>> = await axios.get(url);

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

  public async readAnyGet(args: AssetReadAnyGetRequest): Promise<ServiceResponse<Resource<Array<Asset>> | null>> {
    const result: ServiceResponse<Resource<Array<Asset>> | null> = {
      success: false,
    };

    try {
      const queryParams: Record<string, any> = {};
      queryParams['with_trashed'] = args.with_trashed ? 1 : 0;
      queryParams['company_id'] = args.company_id;
      queryParams['search'] = args.search ? args.search : '';
      if (args.asset_category_id) queryParams['asset_category_id'] = args.asset_category_id;
      if (args.asset_unit_id) queryParams['asset_unit_id'] = args.asset_unit_id;
      if (args.status !== undefined && args.status !== null && args.status !== '') queryParams['status'] = args.status;
      if (args.include_id) queryParams['include_id'] = args.include_id;
      queryParams['refresh'] = args.refresh;
      queryParams['get'] = {
        limit: args.limit,
      };

      const url = route(
        'api.get.asset.read_any',
        {
          _query: queryParams,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<Array<Asset>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<Asset | null>> {
    const result: ServiceResponse<Asset | null> = {
      success: false,
    };

    try {
      const url = route(
        'api.get.asset.read',
        {
          asset: ulid,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<Asset>> = await axios.get(url);

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

  public useAssetEditForm(ulid: string) {
    const url = route(
      'api.post.asset.edit',
      {
        asset: ulid,
      },
      true,
      this.ziggyRoute,
    );

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;
    const form = useForm('post', url, {
      company_id: '',
      asset_category_id: '',
      code: '_AUTO_',
      name: '',
      asset_unit_id: '',
      status: 1,
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
        'api.post.asset.delete',
        {
          asset: ulid,
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
