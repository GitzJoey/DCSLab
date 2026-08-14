export interface PrepaidExpenseReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  expense_category_id?: string | null;
  is_amount_payable_paid_off?: boolean | null;
  include_id?: string;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface PrepaidExpenseReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  expense_category_id?: string | null;
  is_amount_payable_paid_off?: boolean | null;
  include_id?: string;
  refresh: boolean;
  limit: number;
}
