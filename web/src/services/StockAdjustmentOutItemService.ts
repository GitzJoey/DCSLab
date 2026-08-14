import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, type Config } from 'ziggy-js';
import { StockAdjustmentOutItem } from '../types/models/StockAdjustmentOutItem';
import { type Resource } from '../types/resources/Resource';
import { type Collection } from '../types/resources/Collection';
import { type ServiceResponse } from '../types/services/ServiceResponse';
import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import ErrorHandlerService from './ErrorHandlerService';
import {
  type StockAdjustmentOutItemReadAnyGetRequest,
  type StockAdjustmentOutItemReadAnyPaginateRequest,
} from '../types/services/stock-adjustment-out-item/StockAdjustmentOutItemRequest';
import { StatusCode } from '../types/enums/StatusCode';
import { client, useForm } from 'laravel-precognition-vue';

export default class StockAdjustmentOutItemService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();

  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;

    this.errorHandlerService = new ErrorHandlerService();
  }

  public async readAnyPaginate(
    args: StockAdjustmentOutItemReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<StockAdjustmentOutItem>> | null>> {
    const result: ServiceResponse<Collection<Array<StockAdjustmentOutItem>> | null> = {
      success: false,
    };

    try {
      const queryParams: Record<string, any> = {};
      if (args.with_trashed !== undefined) queryParams['with_trashed'] = args.with_trashed;
      queryParams['company_id'] = args.company_id;
      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.stock_adjustment_code) queryParams['stock_adjustment_code'] = args.stock_adjustment_code;
      if (args.stock_adjustment_start_date) queryParams['stock_adjustment_start_date'] = args.stock_adjustment_start_date;
      if (args.stock_adjustment_end_date) queryParams['stock_adjustment_end_date'] = args.stock_adjustment_end_date;
      if (args.stock_adjustment_category_id) queryParams['stock_adjustment_category_id'] = args.stock_adjustment_category_id;
      if (args.stock_adjustment_in_warehouse_id) queryParams['stock_adjustment_in_warehouse_id'] = args.stock_adjustment_in_warehouse_id;
      if (args.stock_adjustment_out_warehouse_id) queryParams['stock_adjustment_out_warehouse_id'] = args.stock_adjustment_out_warehouse_id;
      if (args.product_unit_code) queryParams['product_unit_code'] = args.product_unit_code;
      if (args.product_unit_product_name) queryParams['product_unit_product_name'] = args.product_unit_product_name;
      if (args.product_unit_product_category_id) queryParams['product_unit_product_category_id'] = args.product_unit_product_category_id;
      if (args.product_unit_product_brand_id) queryParams['product_unit_product_brand_id'] = args.product_unit_product_brand_id;

      queryParams['refresh'] = args.refresh;
      queryParams['paginate'] = {
        page: args.page,
        per_page: args.per_page,
      };

      const url = route(
        'api.get.stock_adjustment_out_item.read_any',
        {
          _query: queryParams,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Collection<Array<StockAdjustmentOutItem>>> = await axios.get(url);

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
    args: StockAdjustmentOutItemReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<StockAdjustmentOutItem>> | null>> {
    const result: ServiceResponse<Resource<Array<StockAdjustmentOutItem>> | null> = {
      success: false,
    };

    try {
      const queryParams: Record<string, any> = {};
      queryParams['with_trashed'] = args.with_trashed;
      queryParams['company_id'] = args.company_id;
      if (args.branch_id) queryParams['branch_id'] = args.branch_id;
      if (args.search) queryParams['search'] = args.search;
      if (args.stock_adjustment_code) queryParams['stock_adjustment_code'] = args.stock_adjustment_code;
      if (args.stock_adjustment_start_date) queryParams['stock_adjustment_start_date'] = args.stock_adjustment_start_date;
      if (args.stock_adjustment_end_date) queryParams['stock_adjustment_end_date'] = args.stock_adjustment_end_date;
      if (args.stock_adjustment_category_id) queryParams['stock_adjustment_category_id'] = args.stock_adjustment_category_id;
      if (args.stock_adjustment_in_warehouse_id) queryParams['stock_adjustment_in_warehouse_id'] = args.stock_adjustment_in_warehouse_id;
      if (args.stock_adjustment_out_warehouse_id) queryParams['stock_adjustment_out_warehouse_id'] = args.stock_adjustment_out_warehouse_id;
      if (args.product_unit_code) queryParams['product_unit_code'] = args.product_unit_code;
      if (args.product_unit_product_name) queryParams['product_unit_product_name'] = args.product_unit_product_name;
      if (args.product_unit_product_category_id) queryParams['product_unit_product_category_id'] = args.product_unit_product_category_id;
      if (args.product_unit_product_brand_id) queryParams['product_unit_product_brand_id'] = args.product_unit_product_brand_id;

      queryParams['refresh'] = args.refresh;
      queryParams['get'] = {
        limit: args.limit,
      };

      const url = route(
        'api.get.stock_adjustment_out_item.read_any',
        {
          _query: queryParams,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<Array<StockAdjustmentOutItem>>> = await axios.get(url);

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

  public useStockAdjustmentOutItemCreateForm() {
    const url = route('api.post.stock_adjustment_out_item.save', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    const form = useForm('post', url, {
      company_id: '',
      branch_id: '',
      stock_adjustment_id: '',
      qty: 0,
      product_unit_id: '',
      product_unit_conversion_value: 1,
      remarks: '',
    });

    return form;
  }

  public useStockAdjustmentOutItemEditForm(ulid: string) {
    const url = route(
      'api.post.stock_adjustment_out_item.edit',
      {
        stock_adjustment_out_item: ulid,
      },
      true,
      this.ziggyRoute,
    );

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    const form = useForm('post', url, {
      company_id: '',
      branch_id: '',
      stock_adjustment_id: '',
      qty: 0,
      product_unit_id: '',
      product_unit_conversion_value: 1,
      remarks: '',
    });

    return form;
  }

  public async read(ulid: string): Promise<ServiceResponse<StockAdjustmentOutItem | null>> {
    const result: ServiceResponse<StockAdjustmentOutItem | null> = {
      success: false,
    };

    try {
      const url = route(
        'api.get.stock_adjustment_out_item.read',
        {
          stock_adjustment_out_item: ulid,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<StockAdjustmentOutItem>> = await axios.get(url);

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
        'api.post.stock_adjustment_out_item.delete',
        {
          stock_adjustment_out_item: ulid,
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
