export interface AssetReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  search?: string | null;
  asset_category_id?: string;
  asset_unit_id?: string;
  status?: number | string;
  include_id?: string;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface AssetReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  search?: string | null;
  asset_category_id?: string;
  asset_unit_id?: string;
  status?: number | string;
  include_id?: string;
  refresh: boolean;
  limit: number;
}
