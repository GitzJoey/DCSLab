export interface SalesOrderDeliveryReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  start_date?: string | null;
  end_date?: string | null;
  customer_id?: string | null;
  sales_order_id?: string | null;
  warehouse_id?: string | null;
  is_posted?: boolean | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface SalesOrderDeliveryReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  start_date?: string | null;
  end_date?: string | null;
  customer_id?: string | null;
  sales_order_id?: string | null;
  warehouse_id?: string | null;
  is_posted?: boolean | null;
  include_id?: string;
  refresh: boolean;
  limit: number;
}

export interface SalesOrderDeliveryItemSerialNestedStoreRequest {
  serial: string;
}

export interface SalesOrderDeliveryItemNestedStoreRequest {
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  remarks: string | null;
  serials: SalesOrderDeliveryItemSerialNestedStoreRequest[];
}

export interface SalesOrderDeliveryCostNestedStoreRequest {
  code: string;
  date: string;
  name: string;
  cash_account_id: string;
  amount: number;
  remarks: string | null;
}

export interface SalesOrderDeliveryStoreRequest {
  company_id: string;
  branch_id: string;
  customer_id: string | null;
  sales_order_id: string | null;
  code: string;
  date: string;
  warehouse_id: string | null;
  remarks: string | null;
  is_posted: boolean;
  items: SalesOrderDeliveryItemNestedStoreRequest[];
  costs: SalesOrderDeliveryCostNestedStoreRequest[];
}

export interface SalesOrderDeliveryItemSerialNestedUpdateRequest {
  id: string | null;
  serial: string;
}

export interface SalesOrderDeliveryItemNestedUpdateRequest {
  id: string | null;
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  remarks: string | null;
  delete_serial_ids: string[];
  serials: SalesOrderDeliveryItemSerialNestedUpdateRequest[];
}

export interface SalesOrderDeliveryCostNestedUpdateRequest {
  id: string | null;
  code: string;
  date: string;
  name: string;
  cash_account_id: string;
  amount: number;
  remarks: string | null;
}

export interface SalesOrderDeliveryUpdateRequest {
  customer_id: string | null;
  sales_order_id: string | null;
  code: string;
  date: string;
  warehouse_id: string | null;
  remarks: string | null;
  is_posted: boolean;
  delete_item_ids: string[];
  items: SalesOrderDeliveryItemNestedUpdateRequest[];
  delete_cost_ids: string[];
  costs: SalesOrderDeliveryCostNestedUpdateRequest[];
}
