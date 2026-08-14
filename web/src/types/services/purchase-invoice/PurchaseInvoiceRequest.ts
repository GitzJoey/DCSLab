import type { PurchaseInvoicePaymentType } from '../../models/PurchaseInvoicePayment';

export interface PurchaseInvoiceReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  supplier_id: string | null;
  purchase_order_id: string | null;
  is_posted?: boolean | null;
  is_paid_off?: boolean | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface PurchaseInvoiceReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date?: string | null;
  end_date?: string | null;
  supplier_id?: string | null;
  purchase_order_id?: string | null;
  is_posted?: boolean | null;
  is_paid_off?: boolean | null;
  include_id?: string;
  refresh: boolean;
  limit: number;
}

export interface PurchaseInvoiceItemNestedStoreRequest {
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
  purchase_order_item_id: string | null;
  remarks: string | null;
}

export interface PurchaseInvoicePaymentNestedStoreRequest {
  code: string;
  date: string;
  payment_type: PurchaseInvoicePaymentType;
  cash_account_id: string | null;
  purchase_order_payment_id: string | null;
  purchase_return_id: string | null;
  amount: number;
  remarks: string | null;
}

export interface PurchaseInvoiceStoreRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  due_days: number;
  supplier_id: string | null;
  purchase_order_id: string | null;
  tax_invoice_number: string | null;
  tax_invoice_vat_base: number;
  tax_invoice_vat: number;
  remarks: string | null;
  is_posted: boolean;
  global_discount: number;
  rounding: number;
  items: PurchaseInvoiceItemNestedStoreRequest[];
  payments: PurchaseInvoicePaymentNestedStoreRequest[];
}

export interface PurchaseInvoiceItemNestedUpdateRequest extends PurchaseInvoiceItemNestedStoreRequest {
  id: string | null;
}

export interface PurchaseInvoicePaymentNestedUpdateRequest extends PurchaseInvoicePaymentNestedStoreRequest {
  id: string | null;
}

export interface PurchaseInvoiceUpdateRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  due_days: number;
  supplier_id: string | null;
  purchase_order_id: string | null;
  tax_invoice_number: string | null;
  tax_invoice_vat_base: number;
  tax_invoice_vat: number;
  remarks: string | null;
  is_posted: boolean;
  global_discount: number;
  rounding: number;
  delete_item_ids: string[];
  items: PurchaseInvoiceItemNestedUpdateRequest[];
  delete_payment_ids: string[];
  payments: PurchaseInvoicePaymentNestedUpdateRequest[];
}
