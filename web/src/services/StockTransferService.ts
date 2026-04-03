import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import { route, Config } from 'ziggy-js';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { StatusCode } from '../types/enums/StatusCode';
import { StockTransfer } from '../types/models/StockTransfer';
import { type Collection } from '../types/resources/Collection';
import { type Resource } from '../types/resources/Resource';
import { type ServiceResponse } from '../types/services/ServiceResponse';
import {
  type StockTransferReadAnyGetRequest,
  type StockTransferReadAnyPaginateRequest,
  type StockTransferStoreRequest,
  type StockTransferUpdateRequest,
} from '../types/services/stock-transfer/StockTransferRequest';
import axios from '../axios';
import ErrorHandlerService from './ErrorHandlerService';

export default class StockTransferService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();

  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;

    this.errorHandlerService = new ErrorHandlerService();
  }

  public useStockTransferCreateForm() {
    const url = route('api.post.stock_transfer.save', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    const form = useForm('post', url, {
      company_id: '',
      branch_id: '',
      code: '_AUTO_',
      date: '_AUTO_',
      source_warehouse_id: '',
      destination_warehouse_id: '',
      remarks: '',
      is_posted: true,
      items: [] as NonNullable<StockTransferStoreRequest['items']>,
    });

    return form;
  }

  public useStockTransferEditForm(ulid: string) {
    const url = route(
      'api.post.stock_transfer.edit',
      {
        stock_transfer: ulid,
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
      source_warehouse_id: '',
      destination_warehouse_id: '',
      remarks: '',
      is_posted: false,
      delete_item_ids: [] as NonNullable<StockTransferUpdateRequest['delete_item_ids']>,
      items: [] as NonNullable<StockTransferUpdateRequest['items']>,
    });

    return form;
  }

  public async readAnyPaginate(
    args: StockTransferReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<StockTransfer>> | null>> {
    const result: ServiceResponse<Collection<Array<StockTransfer>> | null> = {
      success: false,
    };

    try {
      const queryParams: Record<string, any> = {};
      queryParams['with_trashed'] = args.with_trashed;
      queryParams['company_id'] = args.company_id;
      queryParams['branch_id'] = args.branch_id;
      queryParams['search'] = args.search;
      
      queryParams['start_date'] = args.start_date;
      queryParams['end_date'] = args.end_date;
      queryParams['source_warehouse_id'] = args.source_warehouse_id;
      queryParams['destination_warehouse_id'] = args.destination_warehouse_id;

      queryParams['refresh'] = args.refresh;
      queryParams['paginate'] = {
        page: args.page,
        per_page: args.per_page,
      };

      const url = route(
        'api.get.stock_transfer.read_any',
        {
          _query: queryParams,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Collection<Array<StockTransfer>>> = await axios.get(url);

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
    args: StockTransferReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<StockTransfer>> | null>> {
    const result: ServiceResponse<Resource<Array<StockTransfer>> | null> = {
      success: false,
    };

    try {
      const queryParams: Record<string, any> = {};
      queryParams['with_trashed'] = args.with_trashed;
      queryParams['company_id'] = args.company_id;
      queryParams['branch_id'] = args.branch_id;
      queryParams['search'] = args.search;
      queryParams['start_date'] = args.start_date;
      queryParams['end_date'] = args.end_date;
      queryParams['source_warehouse_id'] = args.source_warehouse_id;
      queryParams['destination_warehouse_id'] = args.destination_warehouse_id;

      queryParams['refresh'] = args.refresh;
      queryParams['get'] = {
        limit: args.limit,
      };

      const url = route(
        'api.get.stock_transfer.read_any',
        {
          _query: queryParams,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<Array<StockTransfer>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<StockTransfer | null>> {
    const result: ServiceResponse<StockTransfer | null> = {
      success: false,
    };

    try {
      const url = route(
        'api.get.stock_transfer.read',
        {
          stock_transfer: ulid,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<StockTransfer>> = await axios.get(url);

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

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = {
      success: false,
    };

    try {
      const url = route(
        'api.post.stock_transfer.delete',
        {
          stock_transfer: ulid,
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
