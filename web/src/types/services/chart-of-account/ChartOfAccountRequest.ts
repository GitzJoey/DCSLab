export interface ChartOfAccountReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  search?: string | null;
  parent_id?: string | null;
  has_parent?: boolean;
  has_children?: boolean;
  scope?: string | null;
  system_key?: string | null;
  account_type?: string | null;
  normal_balance?: string | null;
  is_group?: boolean;
  is_active?: boolean;
  include_id?: string;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface ChartOfAccountReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  search?: string | null;
  parent_id?: string | null;
  has_parent?: boolean;
  has_children?: boolean;
  scope?: string | null;
  system_key?: string | null;
  account_type?: string | null;
  normal_balance?: string | null;
  is_group?: boolean;
  is_active?: boolean;
  include_id?: string;
  refresh: boolean;
  limit: number;
}
