import { AxiosError, AxiosResponse, isAxiosError } from 'axios';
import { client, useForm } from 'laravel-precognition-vue';
import { route, Config } from 'ziggy-js';
import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { StatusCode } from '../types/enums/StatusCode';
import { SalesOrderDelivery } from '../types/models/SalesOrderDelivery';
import { Collection } from '../types/resources/Collection';
import { Resource } from '../types/resources/Resource';
import { ServiceResponse } from '../types/services/ServiceResponse';
import {
  SalesOrderDeliveryReadAnyGetRequest,
  SalesOrderDeliveryReadAnyPaginateRequest,
  SalesOrderDeliveryStoreRequest,
  SalesOrderDeliveryUpdateRequest,
} from '../types/services/sales-order-delivery/SalesOrderDeliveryRequest';
import ErrorHandlerService from './ErrorHandlerService';

export default class SalesOrderDeliveryService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: SalesOrderDeliveryReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<SalesOrderDelivery>> | null>> {
    const result: ServiceResponse<Collection<Array<SalesOrderDelivery>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed,
        company_id: args.company_id,
        refresh: args.refresh,
        paginate: {
          page: args.page,
          per_page: args.per_page,
        },
      };

      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.customer_id) queryParams['customer_id'] = args.customer_id;
      if (args.sales_order_id) queryParams['sales_order_id'] = args.sales_order_id;
      if (args.start_date) queryParams['start_date'] = args.start_date;
      if (args.end_date) queryParams['end_date'] = args.end_date;
      if (args.warehouse_id) queryParams['warehouse_id'] = args.warehouse_id;
      if (args.is_posted !== undefined && args.is_posted !== null) queryParams['is_posted'] = args.is_posted;

      const url = route('api.get.sales_order_delivery.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<SalesOrderDelivery>>> = await axios.get(url);

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
    args: SalesOrderDeliveryReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<SalesOrderDelivery>> | null>> {
    const result: ServiceResponse<Resource<Array<SalesOrderDelivery>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed ? 1 : 0,
        company_id: args.company_id,
        refresh: args.refresh,
        get: {
          limit: args.limit,
        },
      };

      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.customer_id) queryParams['customer_id'] = args.customer_id;
      if (args.sales_order_id) queryParams['sales_order_id'] = args.sales_order_id;
      if (args.start_date) queryParams['start_date'] = args.start_date;
      if (args.end_date) queryParams['end_date'] = args.end_date;
      if (args.warehouse_id) queryParams['warehouse_id'] = args.warehouse_id;
      if (args.is_posted !== undefined && args.is_posted !== null) queryParams['is_posted'] = args.is_posted;
      if (args.include_id) queryParams['include_id'] = args.include_id;

      const url = route('api.get.sales_order_delivery.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<SalesOrderDelivery>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<SalesOrderDelivery | null>> {
    const result: ServiceResponse<SalesOrderDelivery | null> = { success: false };

    try {
      const url = route(
        'api.get.sales_order_delivery.read',
        {
          sales_order_delivery: ulid,
        },
        false,
        this.ziggyRoute,
      );
      const response: AxiosResponse<Resource<SalesOrderDelivery>> = await axios.get(url);

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

  public useSalesOrderDeliveryCreateForm() {
    const url = route('api.post.sales_order_delivery.save', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      company_id: '',
      branch_id: '',
      customer_id: null,
      sales_order_id: null,
      code: '_AUTO_',
      date: '_AUTO_',
      warehouse_id: null,
      remarks: '',
      is_posted: false,
      items: [] as NonNullable<SalesOrderDeliveryStoreRequest['items']>,
      costs: [] as NonNullable<SalesOrderDeliveryStoreRequest['costs']>,
    });
  }

  public useSalesOrderDeliveryEditForm(ulid: string) {
    const url = route(
      'api.post.sales_order_delivery.edit',
      {
        sales_order_delivery: ulid,
      },
      true,
      this.ziggyRoute,
    );

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    return useForm('post', url, {
      customer_id: null,
      sales_order_id: null,
      code: '_AUTO_',
      date: '',
      warehouse_id: null,
      remarks: '',
      is_posted: false,
      delete_item_ids: [] as NonNullable<SalesOrderDeliveryUpdateRequest['delete_item_ids']>,
      items: [] as NonNullable<SalesOrderDeliveryUpdateRequest['items']>,
      delete_cost_ids: [] as NonNullable<SalesOrderDeliveryUpdateRequest['delete_cost_ids']>,
      costs: [] as NonNullable<SalesOrderDeliveryUpdateRequest['costs']>,
    });
  }

  public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = { success: false };

    try {
      const url = route(
        'api.post.sales_order_delivery.delete',
        {
          sales_order_delivery: ulid,
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
