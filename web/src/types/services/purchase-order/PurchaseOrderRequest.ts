export interface PurchaseOrderReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  supplier_id: string | null;
  progress_status: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface PurchaseOrderReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  supplier_id: string | null;
  progress_status?: string | null;
  include_id?: string;
  refresh: boolean;
  limit: number;
}

export interface PurchaseOrderItemNestedStoreRequest {
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
  remarks: string | null;
}

export interface PurchaseOrderPaymentNestedStoreRequest {
  code: string;
  date: string;
  cash_account_id: string;
  amount: number;
  remarks: string | null;
}

export interface PurchaseOrderPaymentRefundNestedStoreRequest {
  code: string;
  date: string;
  cash_account_id: string;
  amount: number;
  remarks: string | null;
}

export interface PurchaseOrderStoreRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  due_days: number;
  supplier_id: string | null;
  remarks: string | null;
  global_discount: number;
  rounding: number;
  items: PurchaseOrderItemNestedStoreRequest[];
  payments: PurchaseOrderPaymentNestedStoreRequest[];
  refunded_payments: PurchaseOrderPaymentRefundNestedStoreRequest[];
}

export interface PurchaseOrderItemNestedUpdateRequest {
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
  remarks: string | null;
}

export interface PurchaseOrderPaymentNestedUpdateRequest {
  id: string | null;
  code: string;
  date: string;
  cash_account_id: string;
  amount: number;
  amount_allocated?: number;
  remarks: string | null;
}

export interface PurchaseOrderPaymentRefundNestedUpdateRequest {
  id: string | null;
  code: string;
  date: string;
  cash_account_id: string;
  amount: number;
  remarks: string | null;
}

export interface PurchaseOrderUpdateRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  due_days: number;
  supplier_id: string | null;
  remarks: string | null;
  global_discount: number;
  rounding: number;
  delete_item_ids: string[];
  items: PurchaseOrderItemNestedUpdateRequest[];
  delete_payment_ids: string[];
  payments: PurchaseOrderPaymentNestedUpdateRequest[];
  delete_refunded_payment_ids: string[];
  refunded_payments: PurchaseOrderPaymentRefundNestedUpdateRequest[];
}
