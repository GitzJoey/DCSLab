import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import { Config, route } from 'ziggy-js';
import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { StatusCode } from '../types/enums/StatusCode';
import type { AssetSale } from '../types/models/AssetSale';
import type { Collection } from '../types/resources/Collection';
import type { Resource } from '../types/resources/Resource';
import type { ServiceResponse } from '../types/services/ServiceResponse';
import {
  type AssetSaleReadAnyGetRequest,
  type AssetSaleReadAnyPaginateRequest,
  type AssetSaleStoreRequest,
  type AssetSaleUpdateRequest,
} from '../types/services/asset-sale/AssetSaleRequest';
import ErrorHandlerService from './ErrorHandlerService';

export default class AssetSaleService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();

  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public useAssetSaleCreateForm() {
    const url = route('api.post.asset_sale.save', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      due_days: 0,
      customer_id: null,
      remarks: '',
      is_posted: true,
      rounding: 0,
      items: [] as AssetSaleStoreRequest['items'],
    });
  }

  public useAssetSaleEditForm(ulid: string) {
    const url = route('api.post.asset_sale.edit', { asset_sale: ulid }, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '',
      due_days: 0,
      customer_id: null,
      remarks: '',
      is_posted: false,
      rounding: 0,
      delete_item_ids: [] as AssetSaleUpdateRequest['delete_item_ids'],
      items: [] as AssetSaleUpdateRequest['items'],
    });
  }

  public async readAnyPaginate(
    args: AssetSaleReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<AssetSale>> | null>> {
    const result: ServiceResponse<Collection<Array<AssetSale>> | null> = { success: false };

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

      const url = route('api.get.asset_sale.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<AssetSale>>> = await axios.get(url);

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
    args: AssetSaleReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<AssetSale>> | null>> {
    const result: ServiceResponse<Resource<Array<AssetSale>> | null> = { success: false };

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

      const url = route('api.get.asset_sale.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<AssetSale>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<AssetSale | null>> {
    const result: ServiceResponse<AssetSale | null> = { success: false };

    try {
      const url = route('api.get.asset_sale.read', { asset_sale: ulid }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<AssetSale>> = await axios.get(url);

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
      const url = route('api.post.asset_sale.delete', { asset_sale: ulid }, false, this.ziggyRoute);
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
