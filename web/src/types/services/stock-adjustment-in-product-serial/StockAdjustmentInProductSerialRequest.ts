export interface StockAdjustmentInProductSerialReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  stock_adjustment_id?: string | null;
  product_id?: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface StockAdjustmentInProductSerialReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  stock_adjustment_id?: string | null;
  product_id?: string | null;
  search?: string | null;
  refresh: boolean;
  limit: number;
}

export interface StockAdjustmentInProductSerialStoreRequest {
  company_id: string;
  branch_id: string;
  stock_adjustment_id: string;
  stock_adjustment_in_product_id: string;
  serial: string;
}

export interface StockAdjustmentInProductSerialUpdateRequest {
  serial: string;
}
