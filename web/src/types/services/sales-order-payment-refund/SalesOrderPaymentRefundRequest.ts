export interface SalesOrderPaymentRefundReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  start_date?: string;
  end_date?: string;
  sales_order_id?: string | null;
  customer_id?: string | null;
  cash_account_id?: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface SalesOrderPaymentRefundReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  start_date?: string;
  end_date?: string;
  sales_order_id?: string | null;
  customer_id?: string | null;
  cash_account_id?: string | null;
  refresh: boolean;
  limit: number;
}
