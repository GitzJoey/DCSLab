export interface AssetAdjustmentReadAnyPaginateRequest {
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

export interface AssetAdjustmentReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  refresh: boolean;
  limit: number;
}

export interface AssetAdjustmentInItemNestedStoreRequest {
  qty: number;
  asset_id: string;
  remarks: string | null;
  serials: { serial: string }[];
}

export interface AssetAdjustmentOutItemNestedStoreRequest {
  qty: number;
  asset_id: string;
  remarks: string | null;
  serials: { serial: string }[];
}

export interface AssetAdjustmentStoreRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  remarks: string | null;
  is_posted: boolean;
  in_items: AssetAdjustmentInItemNestedStoreRequest[];
  out_items: AssetAdjustmentOutItemNestedStoreRequest[];
}

export interface AssetAdjustmentInItemNestedUpdateRequest {
  id: string | null;
  qty: number;
  asset_id: string;
  remarks: string | null;
  delete_serial_ids: string[];
  serials: { id: string | null; serial: string }[];
}

export interface AssetAdjustmentOutItemNestedUpdateRequest {
  id: string | null;
  qty: number;
  asset_id: string;
  remarks: string | null;
  delete_serial_ids: string[];
  serials: { id: string | null; serial: string }[];
}

export interface AssetAdjustmentUpdateRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  remarks: string | null;
  is_posted: boolean;
  delete_in_item_ids: string[];
  in_items: AssetAdjustmentInItemNestedUpdateRequest[];
  delete_out_item_ids: string[];
  out_items: AssetAdjustmentOutItemNestedUpdateRequest[];
}
