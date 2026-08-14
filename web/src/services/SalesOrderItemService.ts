import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, type Config } from 'ziggy-js';
import type { SalesOrderItem } from '../types/models/SalesOrderItem';
import type { Resource } from '../types/resources/Resource';
import type { Collection } from '../types/resources/Collection';
import type { ServiceResponse } from '../types/services/ServiceResponse';
import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import ErrorHandlerService from './ErrorHandlerService';
import {
  type SalesOrderItemReadAnyGetRequest,
  type SalesOrderItemReadAnyPaginateRequest,
} from '../types/services/sales-order-item/SalesOrderItemRequest';
import { StatusCode } from '../types/enums/StatusCode';

export default class SalesOrderItemService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();
  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;
    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: SalesOrderItemReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<SalesOrderItem>> | null>> {
    const result: ServiceResponse<Collection<Array<SalesOrderItem>> | null> = { success: false };

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
      if (args.sales_order_code) queryParams['sales_order_code'] = args.sales_order_code;
      if (args.sales_order_start_date) queryParams['sales_order_start_date'] = args.sales_order_start_date;
      if (args.sales_order_end_date) queryParams['sales_order_end_date'] = args.sales_order_end_date;
      if (args.sales_order_customer_id) queryParams['sales_order_customer_id'] = args.sales_order_customer_id;
      if (args.product_unit_code) queryParams['product_unit_code'] = args.product_unit_code;
      if (args.product_unit_product_name) queryParams['product_unit_product_name'] = args.product_unit_product_name;
      if (args.product_unit_product_category_id) queryParams['product_unit_product_category_id'] = args.product_unit_product_category_id;
      if (args.product_unit_product_brand_id) queryParams['product_unit_product_brand_id'] = args.product_unit_product_brand_id;

      const url = route('api.get.sales_order_item.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Collection<Array<SalesOrderItem>>> = await axios.get(url);

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
      }

      return result;
    }
  }

  public async readAnyGet(
    args: SalesOrderItemReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<SalesOrderItem>> | null>> {
    const result: ServiceResponse<Resource<Array<SalesOrderItem>> | null> = { success: false };

    try {
      const queryParams: Record<string, any> = {
        with_trashed: args.with_trashed,
        company_id: args.company_id,
        refresh: args.refresh,
        get: {
          limit: args.limit,
        },
      };

      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.sales_order_code) queryParams['sales_order_code'] = args.sales_order_code;
      if (args.sales_order_start_date) queryParams['sales_order_start_date'] = args.sales_order_start_date;
      if (args.sales_order_end_date) queryParams['sales_order_end_date'] = args.sales_order_end_date;
      if (args.sales_order_customer_id) queryParams['sales_order_customer_id'] = args.sales_order_customer_id;
      if (args.product_unit_code) queryParams['product_unit_code'] = args.product_unit_code;
      if (args.product_unit_product_name) queryParams['product_unit_product_name'] = args.product_unit_product_name;
      if (args.product_unit_product_category_id) queryParams['product_unit_product_category_id'] = args.product_unit_product_category_id;
      if (args.product_unit_product_brand_id) queryParams['product_unit_product_brand_id'] = args.product_unit_product_brand_id;

      const url = route('api.get.sales_order_item.read_any', { _query: queryParams }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<Array<SalesOrderItem>>> = await axios.get(url);

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
      }

      return result;
    }
  }

  public async read(ulid: string): Promise<ServiceResponse<SalesOrderItem | null>> {
    const result: ServiceResponse<SalesOrderItem | null> = { success: false };

    try {
      const url = route('api.get.sales_order_item.read', { sales_order_item: ulid }, false, this.ziggyRoute);
      const response: AxiosResponse<Resource<SalesOrderItem>> = await axios.get(url);

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
      }

      return result;
    }
  }
}
