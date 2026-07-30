import type { PaymentType } from '@/types/enums/PaymentType';

export interface SalesInvoiceReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  customer_id: string | null;
  sales_order_id: string | null;
  is_posted: boolean | null;
  is_paid_off: boolean | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface SalesInvoiceReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date?: string | null;
  end_date?: string | null;
  customer_id?: string | null;
  sales_order_id?: string | null;
  is_posted?: boolean | null;
  is_paid_off?: boolean | null;
  refresh: boolean;
  limit: number;
}

export interface SalesInvoiceItemNestedStoreRequest {
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  product_unit_price: number;
  product_unit_is_price_include_vat: boolean;
  price_discount: number;
  subtotal_discount: number;
  vat_profile_id: string | null;
  vat_rate: number;
  vat_base_numerator: number;
  vat_base_denominator: number;
  sales_order_item_id: string | null;
  remarks: string | null;
}

export interface SalesInvoicePaymentNestedStoreRequest {
  code: string;
  date: string;
  payment_type: PaymentType;
  cash_account_id: string | null;
  sales_order_payment_id: string | null;
  sales_return_id: string | null;
  amount: number;
  remarks: string | null;
}

export interface SalesInvoiceStoreRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  due_days: number;
  customer_id: string | null;
  sales_order_id: string | null;
  tax_invoice_number: string | null;
  tax_invoice_vat_base: number;
  tax_invoice_vat: number;
  remarks: string | null;
  is_posted: boolean;
  global_discount: number;
  rounding: number;
  items: SalesInvoiceItemNestedStoreRequest[];
  payments: SalesInvoicePaymentNestedStoreRequest[];
}

export interface SalesInvoiceItemNestedUpdateRequest {
  id: string | null;
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  product_unit_price: number;
  product_unit_is_price_include_vat: boolean;
  price_discount: number;
  subtotal_discount: number;
  vat_profile_id: string | null;
  vat_rate: number;
  vat_base_numerator: number;
  vat_base_denominator: number;
  sales_order_item_id: string | null;
  remarks: string | null;
}

export interface SalesInvoicePaymentNestedUpdateRequest {
  id: string | null;
  code: string;
  date: string;
  payment_type: PaymentType;
  cash_account_id: string | null;
  sales_order_payment_id: string | null;
  sales_return_id: string | null;
  amount: number;
  remarks: string | null;
}

export interface SalesInvoiceUpdateRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  due_days: number;
  customer_id: string | null;
  sales_order_id: string | null;
  tax_invoice_number: string | null;
  tax_invoice_vat_base: number;
  tax_invoice_vat: number;
  remarks: string | null;
  is_posted: boolean;
  global_discount: number;
  rounding: number;
  delete_item_ids: string[];
  items: SalesInvoiceItemNestedUpdateRequest[];
  delete_payment_ids: string[];
  payments: SalesInvoicePaymentNestedUpdateRequest[];
}
