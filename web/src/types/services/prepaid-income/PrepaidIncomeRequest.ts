export interface PrepaidIncomeReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  income_category_id?: string | null;
  is_amount_receivable_paid_off?: boolean | null;
  include_id?: string;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface PrepaidIncomeReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  income_category_id?: string | null;
  is_amount_receivable_paid_off?: boolean | null;
  include_id?: string;
  refresh: boolean;
  limit: number;
}
