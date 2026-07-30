export interface PurchaseReturnReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  supplier_id: string | null;
  purchase_invoice_id: string | null;
  warehouse_id: string | null;
  is_settled?: boolean | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface PurchaseReturnReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date?: string | null;
  end_date?: string | null;
  supplier_id?: string | null;
  purchase_invoice_id?: string | null;
  warehouse_id?: string | null;
  is_settled?: boolean | null;
  include_id?: string;
  refresh: boolean;
  limit: number;
}

export interface PurchaseReturnItemSerialNestedStoreRequest {
  serial: string;
}

export interface PurchaseReturnItemNestedStoreRequest {
  purchase_order_receipt_item_id: string | null;
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
  serials: PurchaseReturnItemSerialNestedStoreRequest[];
}

export interface PurchaseReturnRefundNestedStoreRequest {
  code: string;
  date: string;
  cash_account_id: string | null;
  amount: number;
  remarks: string | null;
}

export interface PurchaseReturnStoreRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  supplier_id: string | null;
  purchase_invoice_id: string | null;
  warehouse_id: string | null;
  global_discount: number;
  rounding: number;
  remarks: string | null;
  is_posted: boolean;
  items: PurchaseReturnItemNestedStoreRequest[];
  refunds: PurchaseReturnRefundNestedStoreRequest[];
}

export interface PurchaseReturnItemSerialNestedUpdateRequest {
  id: string | null;
  serial: string;
}

export interface PurchaseReturnItemNestedUpdateRequest {
  id: string | null;
  purchase_order_receipt_item_id: string | null;
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
  delete_serial_ids: string[];
  serials: PurchaseReturnItemSerialNestedUpdateRequest[];
}

export interface PurchaseReturnRefundNestedUpdateRequest extends PurchaseReturnRefundNestedStoreRequest {
  id: string | null;
}

export interface PurchaseReturnUpdateRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  supplier_id: string | null;
  purchase_invoice_id: string | null;
  warehouse_id: string | null;
  global_discount: number;
  rounding: number;
  remarks: string | null;
  is_posted: boolean;
  delete_item_ids: string[];
  items: PurchaseReturnItemNestedUpdateRequest[];
  delete_refund_ids: string[];
  refunds: PurchaseReturnRefundNestedUpdateRequest[];
}
