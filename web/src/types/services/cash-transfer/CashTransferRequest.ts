export interface CashTransferReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface CashTransferReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  refresh: boolean;
  limit: number;
}

export interface CashTransferStoreRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  source_cash_account_id: string;
  destination_cash_account_id: string;
  amount: number;
  remarks: string | null;
}

export interface CashTransferUpdateRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  source_cash_account_id: string;
  destination_cash_account_id: string;
  amount: number;
  remarks: string | null;
}

