import type { PurchaseOrderDownPaymentAllocationStatus } from '@/types/enums/PurchaseOrderDownPaymentAllocationStatus';

export interface PurchaseOrderDownPaymentReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  start_date?: string;
  end_date?: string;
  supplier_id?: string | null;
  cash_account_id?: string | null;
  allocation_status?: PurchaseOrderDownPaymentAllocationStatus | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface PurchaseOrderDownPaymentReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  start_date?: string;
  end_date?: string;
  supplier_id?: string | null;
  cash_account_id?: string | null;
  allocation_status?: PurchaseOrderDownPaymentAllocationStatus | null;
  refresh: boolean;
  limit: number;
}
