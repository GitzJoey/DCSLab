export interface AssetPurchaseReadAnyPaginateRequest {
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

export interface AssetPurchaseReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  refresh: boolean;
  limit: number;
}

export interface AssetPurchaseItemNestedStoreRequest {
  qty: number;
  asset_id: string;
  unit_price: number;
  remarks: string | null;
  serials: { serial: string }[];
}

export interface AssetPurchaseStoreRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  due_days: number;
  supplier_id: string | null;
  remarks: string | null;
  is_posted: boolean;
  additional_cost: number;
  rounding: number;
  items: AssetPurchaseItemNestedStoreRequest[];
}

export interface AssetPurchaseItemNestedUpdateRequest {
  id: string | null;
  qty: number;
  asset_id: string;
  unit_price: number;
  remarks: string | null;
  delete_serial_ids: string[];
  serials: { id: string | null; serial: string }[];
}

export interface AssetPurchaseUpdateRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  due_days: number;
  supplier_id: string | null;
  remarks: string | null;
  is_posted: boolean;
  additional_cost: number;
  rounding: number;
  delete_item_ids: string[];
  items: AssetPurchaseItemNestedUpdateRequest[];
}
