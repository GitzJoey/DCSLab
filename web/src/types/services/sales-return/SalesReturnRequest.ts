export interface SalesReturnReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date: string | null;
  end_date: string | null;
  customer_id: string | null;
  sales_invoice_id: string | null;
  warehouse_id: string | null;
  is_settled: boolean | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface SalesReturnReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id: string | null;
  search: string | null;
  start_date?: string | null;
  end_date?: string | null;
  customer_id?: string | null;
  sales_invoice_id?: string | null;
  warehouse_id?: string | null;
  is_settled?: boolean | null;
  refresh: boolean;
  limit: number;
}

export interface SalesReturnItemSerialNestedStoreRequest {
  serial: string;
}

export interface SalesReturnItemNestedStoreRequest {
  sales_order_delivery_item_id: string | null;
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
  serials: SalesReturnItemSerialNestedStoreRequest[];
}

export interface SalesReturnRefundNestedStoreRequest {
  code: string;
  date: string;
  cash_account_id: string;
  amount: number;
  remarks: string | null;
}

export interface SalesReturnStoreRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  customer_id: string | null;
  sales_invoice_id: string | null;
  warehouse_id: string | null;
  global_discount: number;
  rounding: number;
  remarks: string | null;
  is_posted: boolean;
  items: SalesReturnItemNestedStoreRequest[];
  refunds: SalesReturnRefundNestedStoreRequest[];
}

export interface SalesReturnItemSerialNestedUpdateRequest {
  id: string | null;
  serial: string;
}

export interface SalesReturnItemNestedUpdateRequest {
  id: string | null;
  sales_order_delivery_item_id: string | null;
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
  serials: SalesReturnItemSerialNestedUpdateRequest[];
}

export interface SalesReturnRefundNestedUpdateRequest {
  id: string | null;
  code: string;
  date: string;
  cash_account_id: string;
  amount: number;
  remarks: string | null;
}

export interface SalesReturnUpdateRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  customer_id: string | null;
  sales_invoice_id: string | null;
  warehouse_id: string | null;
  global_discount: number;
  rounding: number;
  remarks: string | null;
  is_posted: boolean;
  delete_item_ids: string[];
  items: SalesReturnItemNestedUpdateRequest[];
  delete_refund_ids: string[];
  refunds: SalesReturnRefundNestedUpdateRequest[];
}
