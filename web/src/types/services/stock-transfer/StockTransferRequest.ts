export interface StockTransferReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  source_warehouse_id: string | null;
  destination_warehouse_id: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface StockTransferReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  source_warehouse_id: string | null;
  destination_warehouse_id: string | null;
  refresh: boolean;
  limit: number;
}

export interface StockTransferProductUnitSerialNestedStoreRequest {
  serial: string;
}

export interface StockTransferProductUnitNestedStoreRequest {
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  remarks: string | null;
  serials: StockTransferProductUnitSerialNestedStoreRequest[];
}

export interface StockTransferStoreRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  source_warehouse_id: string;
  destination_warehouse_id: string;
  remarks: string | null;
  is_posted: boolean;
  product_units?: StockTransferProductUnitNestedStoreRequest[];
}

export interface StockTransferProductUnitSerialNestedUpdateRequest {
  id: string | null;
  serial: string;
}

export interface StockTransferProductUnitNestedUpdateRequest {
  id: string | null;
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  remarks: string | null;
  delete_serial_ids: string[];
  serials: StockTransferProductUnitSerialNestedUpdateRequest[];
}

export interface StockTransferUpdateRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  source_warehouse_id: string;
  destination_warehouse_id: string;
  remarks: string | null;
  is_posted: boolean;
  delete_product_unit_ids: string[];
  product_units?: StockTransferProductUnitNestedUpdateRequest[];
}
