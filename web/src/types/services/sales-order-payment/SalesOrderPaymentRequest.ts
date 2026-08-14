import type { AllocationStatus } from '@/types/enums/AllocationStatus';

export interface SalesOrderPaymentReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  start_date?: string;
  end_date?: string;
  sales_order_id?: string | null;
  customer_id?: string | null;
  cash_account_id?: string | null;
  allocation_status?: AllocationStatus | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface SalesOrderPaymentReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  start_date?: string;
  end_date?: string;
  sales_order_id?: string | null;
  customer_id?: string | null;
  cash_account_id?: string | null;
  allocation_status?: AllocationStatus | null;
  refresh: boolean;
  limit: number;
}
