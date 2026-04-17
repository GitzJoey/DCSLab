export interface PurchaseOrderDownPaymentRefundReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  start_date?: string;
  end_date?: string;
  supplier_id?: string | null;
  cash_account_id?: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface PurchaseOrderDownPaymentRefundReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  start_date?: string;
  end_date?: string;
  supplier_id?: string | null;
  cash_account_id?: string | null;
  refresh: boolean;
  limit: number;
}
