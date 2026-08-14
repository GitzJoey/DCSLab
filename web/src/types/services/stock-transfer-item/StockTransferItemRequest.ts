export interface StockTransferItemReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  stock_transfer_code: string | null;
  stock_transfer_start_date: string | null;
  stock_transfer_end_date: string | null;
  stock_transfer_source_warehouse_id: string | null;
  stock_transfer_destination_warehouse_id: string | null;
  product_unit_code: string | null;
  product_unit_product_name: string | null;
  product_unit_product_category_id: string | null;
  product_unit_product_brand_id: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface StockTransferItemReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  stock_transfer_code: string | null;
  stock_transfer_start_date: string | null;
  stock_transfer_end_date: string | null;
  stock_transfer_source_warehouse_id: string | null;
  stock_transfer_destination_warehouse_id: string | null;
  product_unit_code: string | null;
  product_unit_product_name: string | null;
  product_unit_product_category_id: string | null;
  product_unit_product_brand_id: string | null;
  refresh: boolean;
  limit: number;
}

export interface StockTransferItemSerialNestedStoreRequest {
  serial: string;
}

export interface StockTransferItemStoreRequest {
  company_id: string;
  branch_id: string;
  stock_transfer_id: string;
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  remarks: string | null;
  serials: StockTransferItemSerialNestedStoreRequest[];
}

export interface StockTransferItemSerialNestedUpdateRequest {
  id: string | null;
  serial: string;
}

export interface StockTransferItemUpdateRequest {
  company_id: string;
  branch_id: string;
  stock_transfer_id: string;
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  remarks: string | null;
  delete_serial_ids: string[];
  serials: StockTransferItemSerialNestedUpdateRequest[];
}
