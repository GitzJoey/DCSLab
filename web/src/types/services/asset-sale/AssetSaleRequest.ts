export interface AssetSaleReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface AssetSaleReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  refresh: boolean;
  limit: number;
}

export interface AssetSaleItemNestedStoreRequest {
  qty: number;
  asset_id: string;
  unit_price: number;
  remarks: string | null;
  serials: { serial: string }[];
}

export interface AssetSaleStoreRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  due_days: number;
  customer_id: string | null;
  remarks: string | null;
  is_posted: boolean;
  rounding: number;
  items: AssetSaleItemNestedStoreRequest[];
}

export interface AssetSaleItemNestedUpdateRequest {
  id: string | null;
  qty: number;
  asset_id: string;
  unit_price: number;
  remarks: string | null;
  delete_serial_ids: string[];
  serials: { id: string | null; serial: string }[];
}

export interface AssetSaleUpdateRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  due_days: number;
  customer_id: string | null;
  remarks: string | null;
  is_posted: boolean;
  rounding: number;
  delete_item_ids: string[];
  items: AssetSaleItemNestedUpdateRequest[];
}
