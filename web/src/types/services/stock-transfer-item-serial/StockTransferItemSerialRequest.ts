export interface StockTransferItemSerialReadAnyPaginateRequest {
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

export interface StockTransferItemSerialReadAnyGetRequest {
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

export interface StockTransferItemSerialStoreRequest {
  company_id: string;
  branch_id: string;
  stock_transfer_id: string;
  stock_transfer_item_id: string;
  serial: string;
}

export interface StockTransferItemSerialUpdateRequest {
  serial: string;
}
