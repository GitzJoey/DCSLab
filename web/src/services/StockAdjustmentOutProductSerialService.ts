import axios from '../axios';
import { useZiggyRouteStore } from '../stores/ziggy-route';
import { route, type Config } from 'ziggy-js';
import { StockAdjustmentOutProductSerial } from '../types/models/StockAdjustmentOutProductSerial';
import { type Resource } from '../types/resources/Resource';
import { type Collection } from '../types/resources/Collection';
import { type ServiceResponse } from '../types/services/ServiceResponse';
import { type AxiosError, type AxiosResponse, isAxiosError } from 'axios';
import ErrorHandlerService from './ErrorHandlerService';
import {
  type StockAdjustmentOutProductSerialReadAnyGetRequest,
  type StockAdjustmentOutProductSerialReadAnyPaginateRequest,
} from '../types/services/stock-adjustment-out-product-serial/StockAdjustmentOutProductSerialRequest';
import { StatusCode } from '../types/enums/StatusCode';
import { client, useForm } from 'laravel-precognition-vue';

export default class StockAdjustmentOutProductSerialService {
  private ziggyRoute: Config;
  private ziggyRouteStore = useZiggyRouteStore();

  private errorHandlerService;

  constructor() {
    this.ziggyRoute = this.ziggyRouteStore.getZiggy;

    this.errorHandlerService = new ErrorHandlerService();
  }

  public useStockAdjustmentOutProductSerialCreateForm() {
    const url = route('api.post.stock_adjustment_out_product_serial.save', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    const form = useForm('post', url, {
      company_id: '',
      branch_id: '',
      stock_adjustment_id: '',
      stock_adjustment_out_product_id: '',
      serial: '',
    });

    return form;
  }

  public useStockAdjustmentOutProductSerialEditForm(ulid: string) {
    const url = route(
      'api.post.stock_adjustment_out_product_serial.edit',
      {
        stock_adjustment_out_product_serial: ulid,
      },
      true,
      this.ziggyRoute,
    );

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;

    const form = useForm('post', url, {
      serial: '',
    });

    return form;
  }

  public async readAnyPaginate(
    args: StockAdjustmentOutProductSerialReadAnyPaginateRequest,
  ): Promise<ServiceResponse<Collection<Array<StockAdjustmentOutProductSerial>> | null>> {
    const result: ServiceResponse<Collection<Array<StockAdjustmentOutProductSerial>> | null> = {
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
        'api.get.stock_adjustment_out_product_serial.read_any',
        {
          _query: queryParams,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Collection<Array<StockAdjustmentOutProductSerial>>> = await axios.get(url);

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
    args: StockAdjustmentOutProductSerialReadAnyGetRequest,
  ): Promise<ServiceResponse<Resource<Array<StockAdjustmentOutProductSerial>> | null>> {
    const result: ServiceResponse<Resource<Array<StockAdjustmentOutProductSerial>> | null> = {
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
        'api.get.stock_adjustment_out_product_serial.read_any',
        {
          _query: queryParams,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<Array<StockAdjustmentOutProductSerial>>> = await axios.get(url);

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

  public async read(ulid: string): Promise<ServiceResponse<StockAdjustmentOutProductSerial | null>> {
    const result: ServiceResponse<StockAdjustmentOutProductSerial | null> = {
      success: false,
    };

    try {
      const url = route(
        'api.get.stock_adjustment_out_product_serial.read',
        {
          stock_adjustment_out_product_serial: ulid,
        },
        false,
        this.ziggyRoute,
      );

      const response: AxiosResponse<Resource<StockAdjustmentOutProductSerial>> = await axios.get(url);

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
        'api.post.stock_adjustment_out_product_serial.delete',
        {
          stock_adjustment_out_product_serial: ulid,
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
