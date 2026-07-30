export interface PurchaseOrderReceiptReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  supplier_id?: string | null;
  purchase_order_id?: string | null;
  start_date?: string | null;
  end_date?: string | null;
  warehouse_id?: string | null;
  is_posted?: boolean | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface PurchaseOrderReceiptReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  supplier_id?: string | null;
  purchase_order_id?: string | null;
  start_date?: string | null;
  end_date?: string | null;
  warehouse_id?: string | null;
  is_posted?: boolean | null;
  include_id?: string;
  refresh: boolean;
  limit: number;
}

export interface PurchaseOrderReceiptItemSerialNestedStoreRequest {
  serial: string;
}

export interface PurchaseOrderReceiptItemNestedStoreRequest {
  purchase_order_item_id: string | null;
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  remarks: string | null;
  serials: PurchaseOrderReceiptItemSerialNestedStoreRequest[];
}

export interface PurchaseOrderReceiptCostNestedStoreRequest {
  code: string;
  date: string;
  name: string;
  cash_account_id: string;
  amount: number;
  remarks: string | null;
}

export interface PurchaseOrderReceiptStoreRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  supplier_id: string | null;
  purchase_order_id: string | null;
  warehouse_id: string | null;
  remarks: string | null;
  is_posted: boolean;
  items: PurchaseOrderReceiptItemNestedStoreRequest[];
  costs: PurchaseOrderReceiptCostNestedStoreRequest[];
}

export interface PurchaseOrderReceiptItemSerialNestedUpdateRequest {
  id: string | null;
  serial: string;
}

export interface PurchaseOrderReceiptItemNestedUpdateRequest {
  id: string | null;
  purchase_order_item_id: string | null;
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  remarks: string | null;
  delete_serial_ids: string[];
  serials: PurchaseOrderReceiptItemSerialNestedUpdateRequest[];
}

export interface PurchaseOrderReceiptCostNestedUpdateRequest {
  id: string | null;
  code: string;
  date: string;
  name: string;
  cash_account_id: string;
  amount: number;
  remarks: string | null;
}

export interface PurchaseOrderReceiptUpdateRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  supplier_id: string | null;
  purchase_order_id: string | null;
  warehouse_id: string | null;
  remarks: string | null;
  is_posted: boolean;
  delete_item_ids: string[];
  items: PurchaseOrderReceiptItemNestedUpdateRequest[];
  delete_cost_ids: string[];
  costs: PurchaseOrderReceiptCostNestedUpdateRequest[];
}
