export interface StockAdjustmentOutProductSerialReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  stock_adjustment_code?: string | null;
  stock_adjustment_start_date?: string | null;
  stock_adjustment_end_date?: string | null;
  stock_adjustment_category_id?: string | null;
  stock_adjustment_in_warehouse_id?: string | null;
  stock_adjustment_out_warehouse_id?: string | null;
  product_unit_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_product_category_id?: string | null;
  product_unit_product_brand_id?: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface StockAdjustmentOutProductSerialReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  stock_adjustment_code?: string | null;
  stock_adjustment_start_date?: string | null;
  stock_adjustment_end_date?: string | null;
  stock_adjustment_category_id?: string | null;
  stock_adjustment_in_warehouse_id?: string | null;
  stock_adjustment_out_warehouse_id?: string | null;
  product_unit_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_product_category_id?: string | null;
  product_unit_product_brand_id?: string | null;
  refresh: boolean;
  limit: number;
}

export interface StockAdjustmentOutProductSerialStoreRequest {
  company_id: string;
  branch_id: string;
  stock_adjustment_id: string;
  stock_adjustment_out_product_id: string;
  serial: string;
}

export interface StockAdjustmentOutProductSerialUpdateRequest {
  serial: string;
}
