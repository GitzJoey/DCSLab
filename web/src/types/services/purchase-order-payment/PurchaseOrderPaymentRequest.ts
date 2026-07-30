import type { AllocationStatus } from '@/types/enums/AllocationStatus';

export interface PurchaseOrderPaymentReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  start_date?: string;
  end_date?: string;
  purchase_order_id?: string | null;
  supplier_id?: string | null;
  cash_account_id?: string | null;
  allocation_status?: AllocationStatus | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface PurchaseOrderPaymentReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  start_date?: string;
  end_date?: string;
  purchase_order_id?: string | null;
  supplier_id?: string | null;
  cash_account_id?: string | null;
  allocation_status?: AllocationStatus | null;
  refresh: boolean;
  limit: number;
}
