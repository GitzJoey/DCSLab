export interface CapitalOpeningReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  investor_id?: string | null;
  cash_account_id?: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface CapitalOpeningReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  investor_id?: string | null;
  cash_account_id?: string | null;
  refresh: boolean;
  limit: number;
}

export interface CapitalOpeningStoreRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  investor_id: string;
  cash_account_id: string;
  amount: number;
  remarks: string | null;
}

export interface CapitalOpeningUpdateRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  investor_id: string;
  cash_account_id: string;
  amount: number;
  remarks: string | null;
}
