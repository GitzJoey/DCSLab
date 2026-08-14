export interface DebtCategoryReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  search?: string | null;
  include_id?: string;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface DebtCategoryReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  search?: string | null;
  include_id?: string;
  refresh: boolean;
  limit: number;
}
