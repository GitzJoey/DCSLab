import type { PurchaseInvoicePaymentType } from '../../models/PurchaseInvoicePayment';

export interface PurchaseInvoicePaymentReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  purchase_invoice_id: string | null;
  payment_type: PurchaseInvoicePaymentType | null;
  cash_account_id: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface PurchaseInvoicePaymentReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date?: string | null;
  end_date?: string | null;
  purchase_invoice_id?: string | null;
  payment_type?: PurchaseInvoicePaymentType | null;
  cash_account_id?: string | null;
  refresh: boolean;
  limit: number;
}
