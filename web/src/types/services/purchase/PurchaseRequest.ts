export interface PurchaseReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  start_date?: string | null;
  end_date?: string;
  supplier_id?: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface PurchaseReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  start_date?: string | null;
  end_date?: string;
  supplier_id?: string | null;
  refresh: boolean;
  limit: number;
}

export interface PurchaseDiscountNestedStoreRequest {
  sequence: number;
  discount_type: string;
  discount_value: number;
}

export interface PurchaseItemSerialNestedStoreRequest {
  serial: string;
}

export interface PurchaseItemNestedStoreRequest {
  purchase_order_item_id: string | null;
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  product_unit_price: number;
  product_unit_is_price_include_vat: boolean;
  product_unit_price_discounts: PurchaseDiscountNestedStoreRequest[];
  subtotal_discounts: PurchaseDiscountNestedStoreRequest[];
  vat_profile_id: string | null;
  vat_rate: number;
  vat_base_numerator: number;
  vat_base_denominator: number;
  remarks: string | null;
}

export interface PurchaseDirectItemNestedStoreRequest extends PurchaseItemNestedStoreRequest {
  serials: PurchaseItemSerialNestedStoreRequest[];
}

export interface PurchaseGlobalDiscountNestedStoreRequest {
  sequence: number;
  discount_type: string;
  discount_value: number;
}

export interface PurchaseAdditionalCostNestedStoreRequest {
  purchase_additional_cost_category_id: string;
  code: string;
  date: string;
  due_days: number;
  paid_immediately_cash_account_id: string | null;
  amount_paid_immediately: number;
  amount_payable: number;
  remarks: string | null;
}

export interface PurchaseStoreRequest {
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
  additional_cost: number;
  rounding: number;
  items: PurchaseItemNestedStoreRequest[];
  global_discounts: PurchaseGlobalDiscountNestedStoreRequest[];
  additional_costs: PurchaseAdditionalCostNestedStoreRequest[];
}

export interface PurchaseManualStoreRequest extends PurchaseStoreRequest {}

export interface PurchaseDirectStoreRequest extends Omit<PurchaseStoreRequest, 'items'> {
  direct_receipt_warehouse_id: string;
  items: PurchaseDirectItemNestedStoreRequest[];
}

export interface PurchaseDiscountNestedUpdateRequest {
  id: string | null;
  sequence: number;
  discount_type: string;
  discount_value: number;
}

export interface PurchaseItemSerialNestedUpdateRequest {
  id: string | null;
  serial: string;
}

export interface PurchaseItemNestedUpdateRequest {
  id: string | null;
  purchase_order_item_id: string | null;
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  product_unit_price: number;
  product_unit_is_price_include_vat: boolean;
  delete_product_unit_price_discount_ids: string[];
  product_unit_price_discounts: PurchaseDiscountNestedUpdateRequest[];
  delete_subtotal_discount_ids: string[];
  subtotal_discounts: PurchaseDiscountNestedUpdateRequest[];
  vat_profile_id: string | null;
  vat_rate: number;
  vat_base_numerator: number;
  vat_base_denominator: number;
  remarks: string | null;
}

export interface PurchaseDirectItemNestedUpdateRequest extends PurchaseItemNestedUpdateRequest {
  serials: PurchaseItemSerialNestedUpdateRequest[];
}

export interface PurchaseGlobalDiscountNestedUpdateRequest {
  id: string | null;
  sequence: number;
  discount_type: string;
  discount_value: number;
}

export interface PurchaseAdditionalCostNestedUpdateRequest {
  id: string | null;
  purchase_additional_cost_category_id: string;
  code: string;
  date: string;
  due_days: number;
  paid_immediately_cash_account_id: string | null;
  amount_paid_immediately: number;
  amount_payable: number;
  remarks: string | null;
}

export interface PurchaseUpdateRequest {
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
  additional_cost: number;
  rounding: number;
  delete_item_ids: string[];
  items: PurchaseItemNestedUpdateRequest[];
  delete_global_discount_ids: string[];
  global_discounts: PurchaseGlobalDiscountNestedUpdateRequest[];
  delete_additional_cost_ids: string[];
  additional_costs: PurchaseAdditionalCostNestedUpdateRequest[];
}

export interface PurchaseManualUpdateRequest extends PurchaseUpdateRequest {}

export interface PurchaseDirectUpdateRequest extends Omit<PurchaseUpdateRequest, 'items'> {
  direct_receipt_warehouse_id: string;
  items: PurchaseDirectItemNestedUpdateRequest[];
}
