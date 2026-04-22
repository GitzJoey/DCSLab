export interface ExpenseCategoryReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  search?: string | null;
  parent_id?: string | null;
  has_parent?: boolean;
  has_children?: boolean;
  include_id?: string;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface ExpenseCategoryReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  search?: string | null;
  parent_id?: string | null;
  has_parent?: boolean;
  has_children?: boolean;
  include_id?: string;
  refresh: boolean;
  limit: number;
}
