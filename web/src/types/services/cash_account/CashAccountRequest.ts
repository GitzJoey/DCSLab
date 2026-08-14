export interface CashAccountWithRemainingBalanceFilter {
  end_date?: string | null;
}

export interface CashAccountReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  search?: string | null;
  branch_id?: string | null;
  is_bank?: boolean | null;
  include_id?: string;
  with_remaining_balance?: CashAccountWithRemainingBalanceFilter | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface CashAccountReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  search?: string | null;
  branch_id?: string | null;
  is_bank?: boolean | null;
  include_id?: string;
  with_remaining_balance?: CashAccountWithRemainingBalanceFilter | null;
  refresh: boolean;
  limit: number;
}
