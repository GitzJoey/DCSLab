export interface PurchaseOrderPaymentRefundReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  start_date?: string;
  end_date?: string;
  purchase_order_id?: string | null;
  supplier_id?: string | null;
  cash_account_id?: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface PurchaseOrderPaymentRefundReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  start_date?: string;
  end_date?: string;
  purchase_order_id?: string | null;
  supplier_id?: string | null;
  cash_account_id?: string | null;
  refresh: boolean;
  limit: number;
}
