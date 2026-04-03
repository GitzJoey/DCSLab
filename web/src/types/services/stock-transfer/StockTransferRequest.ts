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

export interface StockTransferItemSerialNestedStoreRequest {
  serial: string;
}

export interface StockTransferItemNestedStoreRequest {
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  remarks: string | null;
  serials: StockTransferItemSerialNestedStoreRequest[];
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
  items?: StockTransferItemNestedStoreRequest[];
}

export interface StockTransferItemSerialNestedUpdateRequest {
  id: string | null;
  serial: string;
}

export interface StockTransferItemNestedUpdateRequest {
  id: string | null;
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  remarks: string | null;
  delete_serial_ids: string[];
  serials: StockTransferItemSerialNestedUpdateRequest[];
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
  delete_item_ids: string[];
  items?: StockTransferItemNestedUpdateRequest[];
}
