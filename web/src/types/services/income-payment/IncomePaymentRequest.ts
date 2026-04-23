export interface IncomePaymentReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  income_id?: string | null;
  include_id?: string;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface IncomePaymentReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  income_id?: string | null;
  include_id?: string;
  refresh: boolean;
  limit: number;
}
