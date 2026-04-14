export interface PurchaseOrderReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  supplier_id: string | null;
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
  refresh: boolean;
  limit: number;
}

export interface PurchaseOrderGlobalDiscountNestedStoreRequest {
  sequence: number;
  discount_type: string;
  discount_value: number;
}

export interface PurchaseOrderItemDiscountNestedStoreRequest {
  sequence: number;
  discount_type: string;
  discount_value: number;
}

export interface PurchaseOrderItemNestedStoreRequest {
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  product_unit_price: number;
  product_unit_price_discounts: PurchaseOrderItemDiscountNestedStoreRequest[];
  subtotal_discounts: PurchaseOrderItemDiscountNestedStoreRequest[];
  product_unit_is_price_include_vat: boolean;
  vat_profile_id: string | null;
  vat_rate: number;
  vat_base_numerator: number;
  vat_base_denominator: number;
  remarks: string | null;
}

export interface PurchaseOrderDownPaymentNestedStoreRequest {
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
  rounding: number;
  global_discounts: PurchaseOrderGlobalDiscountNestedStoreRequest[];
  items: PurchaseOrderItemNestedStoreRequest[];
  down_payments: PurchaseOrderDownPaymentNestedStoreRequest[];
}

export interface PurchaseOrderGlobalDiscountNestedUpdateRequest {
  id: string | null;
  sequence: number;
  discount_type: string;
  discount_value: number;
}

export interface PurchaseOrderItemDiscountNestedUpdateRequest {
  id: string | null;
  sequence: number;
  discount_type: string;
  discount_value: number;
}

export interface PurchaseOrderItemNestedUpdateRequest {
  id: string | null;
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  product_unit_price: number;
  delete_product_unit_price_discount_ids: string[];
  product_unit_price_discounts: PurchaseOrderItemDiscountNestedUpdateRequest[];
  delete_subtotal_discount_ids: string[];
  subtotal_discounts: PurchaseOrderItemDiscountNestedUpdateRequest[];
  product_unit_is_price_include_vat: boolean;
  vat_profile_id: string | null;
  vat_rate: number;
  vat_base_numerator: number;
  vat_base_denominator: number;
  remarks: string | null;
}

export interface PurchaseOrderDownPaymentNestedUpdateRequest {
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
  rounding: number;
  delete_global_discount_ids: string[];
  global_discounts: PurchaseOrderGlobalDiscountNestedUpdateRequest[];
  delete_item_ids: string[];
  items: PurchaseOrderItemNestedUpdateRequest[];
  delete_down_payment_ids: string[];
  down_payments: PurchaseOrderDownPaymentNestedUpdateRequest[];
}
