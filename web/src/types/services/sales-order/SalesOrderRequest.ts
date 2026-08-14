export interface SalesOrderReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  customer_id: string | null;
  progress_status: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface SalesOrderReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  customer_id: string | null;
  progress_status?: string | null;
  refresh: boolean;
  limit: number;
}

export interface SalesOrderItemNestedStoreRequest {
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

export interface SalesOrderPaymentNestedStoreRequest {
  code: string;
  date: string;
  cash_account_id: string;
  amount: number;
  amount_allocated?: number;
  remarks: string | null;
}

export interface SalesOrderPaymentRefundNestedStoreRequest {
  code: string;
  date: string;
  cash_account_id: string;
  amount: number;
  remarks: string | null;
}

export interface SalesOrderStoreRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  due_days: number;
  customer_id: string | null;
  remarks: string | null;
  global_discount: number;
  rounding: number;
  items: SalesOrderItemNestedStoreRequest[];
  payments: SalesOrderPaymentNestedStoreRequest[];
  refunded_payments: SalesOrderPaymentRefundNestedStoreRequest[];
}

export interface SalesOrderItemNestedUpdateRequest {
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

export interface SalesOrderPaymentNestedUpdateRequest {
  id: string | null;
  code: string;
  date: string;
  cash_account_id: string;
  amount: number;
  amount_allocated?: number;
  remarks: string | null;
}

export interface SalesOrderPaymentRefundNestedUpdateRequest {
  id: string | null;
  code: string;
  date: string;
  cash_account_id: string;
  amount: number;
  remarks: string | null;
}

export interface SalesOrderUpdateRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  due_days: number;
  customer_id: string | null;
  remarks: string | null;
  global_discount: number;
  rounding: number;
  delete_item_ids: string[];
  items: SalesOrderItemNestedUpdateRequest[];
  delete_payment_ids: string[];
  payments: SalesOrderPaymentNestedUpdateRequest[];
  delete_refunded_payment_ids: string[];
  refunded_payments: SalesOrderPaymentRefundNestedUpdateRequest[];
}
