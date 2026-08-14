import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import { Config, route } from 'ziggy-js';
import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { StatusCode } from '../types/enums/StatusCode';
import type { AssetAdjustment } from '../types/models/AssetAdjustment';
import type { Collection } from '../types/resources/Collection';
import type { Resource } from '../types/resources/Resource';
import type { ServiceResponse } from '../types/services/ServiceResponse';
import {
  type AssetAdjustmentReadAnyGetRequest,
  type AssetAdjustmentReadAnyPaginateRequest,
  type AssetAdjustmentStoreRequest,
  type AssetAdjustmentUpdateRequest,
} from '../types/services/asset-adjustment/AssetAdjustmentRequest';
import ErrorHandlerService from './ErrorHandlerService';

export default class AssetAdjustmentService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();

  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public useAssetAdjustmentCreateForm() {
    const url = route('api.post.asset_adjustment.save', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      remarks: '',
      is_posted: true,
      in_items: [] as AssetAdjustmentStoreRequest['in_items'],
      out_items: [] as AssetAdjustmentStoreRequest['out_items'],
    });
  }

  public useAssetAdjustmentEditForm(ulid: string) {
    const url = route(
      'api.post.asset_adjustment.edit',
      { asset_adjustment: ulid },
      true,
      this.ziggyRoute,
    );

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '',
      remarks: '',
      is_posted: false,
      delete_in_item_ids: [] as AssetAdjustmentUpdateRequest['delete_in_item_ids'],
      in_items: [] as AssetAdjustmentUpdateRequest['in_items'],
      delete_out_item_ids: [] as AssetAdjustmentUpdateRequest['delete_out_item_ids'],
      out_items: [] as AssetAdjustmentUpdateRequest['out_items'],
    });
  }

  public async readAnyPaginate(
    args: AssetAdjustmentReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<AssetAdjustment>> | null>> {
    const result: ServiceResponse<Collection<Array<AssetAdjustment>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed,
        company_id: args.company_id,
        branch_id: args.branch_id,
        search: args.search,
        start_date: args.start_date,
        end_date: args.end_date,
        refresh: args.refresh,
        paginate: {
          page: args.page,
          per_page: args.per_page,
        },
      };

      const url = route(
        'api.get.asset_adjustment.read_any',
        { _query: queryParams },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Collection<Array<AssetAdjustment>>> = await axios.get(url);

      if (response.status === StatusCode.OK) {
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
    args: AssetAdjustmentReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<AssetAdjustment>> | null>> {
    const result: ServiceResponse<Resource<Array<AssetAdjustment>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed,
        company_id: args.company_id,
        branch_id: args.branch_id,
        search: args.search,
        start_date: args.start_date,
        end_date: args.end_date,
        refresh: args.refresh,
        get: {
          limit: args.limit,
        },
      };

      const url = route(
        'api.get.asset_adjustment.read_any',
        { _query: queryParams },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<Array<AssetAdjustment>>> = await axios.get(url);

      if (response.status === StatusCode.OK) {
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

  public async read(ulid: string): Promise<ServiceResponse<AssetAdjustment | null>> {
    const result: ServiceResponse<AssetAdjustment | null> = { success: false };

    try {
      const url = route(
        'api.get.asset_adjustment.read',
        { asset_adjustment: ulid },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<AssetAdjustment>> = await axios.get(url);

      if (response.status === StatusCode.OK) {
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

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = { success: false };

    try {
      const url = route(
        'api.post.asset_adjustment.delete',
        { asset_adjustment: ulid },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<boolean | null> = await axios.post(url);

      if (response.status === StatusCode.OK) {
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
