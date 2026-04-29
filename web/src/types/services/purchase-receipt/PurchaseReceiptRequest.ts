export interface PurchaseReceiptReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  supplier_id?: string | null;
  purchase_id?: string | null;
  is_from_direct_purchase?: boolean | null;
  start_date?: string | null;
  end_date?: string | null;
  warehouse_id?: string | null;
  is_posted?: boolean | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface PurchaseReceiptReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  supplier_id?: string | null;
  purchase_id?: string | null;
  is_from_direct_purchase?: boolean | null;
  start_date?: string | null;
  end_date?: string | null;
  warehouse_id?: string | null;
  is_posted?: boolean | null;
  include_id?: string;
  refresh: boolean;
  limit: number;
}

export interface PurchaseReceiptItemSerialNestedStoreRequest {
  serial: string;
}

export interface PurchaseReceiptItemNestedStoreRequest {
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  remarks: string | null;
  serials: PurchaseReceiptItemSerialNestedStoreRequest[];
}

export interface PurchaseReceiptStoreRequest {
  company_id: string;
  branch_id: string;
  supplier_id: string | null;
  purchase_id: string | null;
  code: string;
  date: string;
  warehouse_id: string | null;
  remarks: string | null;
  is_posted: boolean;
  items: PurchaseReceiptItemNestedStoreRequest[];
}

export interface PurchaseReceiptItemSerialNestedUpdateRequest {
  serial: string;
}

export interface PurchaseReceiptItemNestedUpdateRequest {
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  remarks: string | null;
  serials: PurchaseReceiptItemSerialNestedUpdateRequest[];
}

export interface PurchaseReceiptUpdateRequest {
  supplier_id: string | null;
  purchase_id: string | null;
  code: string;
  date: string;
  warehouse_id: string | null;
  remarks: string | null;
  is_posted: boolean;
  items: PurchaseReceiptItemNestedUpdateRequest[];
}
