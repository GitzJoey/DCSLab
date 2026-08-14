import type { PaymentType } from '@/types/enums/PaymentType';

export interface SalesInvoicePaymentReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  sales_invoice_id: string | null;
  payment_type: PaymentType | null;
  cash_account_id: string | null;
  sales_order_payment_id: string | null;
  sales_return_id: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface SalesInvoicePaymentReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date?: string | null;
  end_date?: string | null;
  sales_invoice_id?: string | null;
  payment_type?: PaymentType | null;
  cash_account_id?: string | null;
  sales_order_payment_id?: string | null;
  sales_return_id?: string | null;
  refresh: boolean;
  limit: number;
}
