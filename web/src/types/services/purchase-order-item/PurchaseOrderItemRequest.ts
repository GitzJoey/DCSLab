export interface PurchaseOrderItemReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  purchase_order_code?: string;
  purchase_order_start_date?: string;
  purchase_order_end_date?: string;
  purchase_order_supplier_id?: string | null;
  product_unit_code?: string;
  product_unit_product_name?: string;
  product_unit_product_category_id?: string | null;
  product_unit_product_brand_id?: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface PurchaseOrderItemReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  purchase_order_code?: string;
  purchase_order_start_date?: string;
  purchase_order_end_date?: string;
  purchase_order_supplier_id?: string | null;
  product_unit_code?: string;
  product_unit_product_name?: string;
  product_unit_product_category_id?: string | null;
  product_unit_product_brand_id?: string | null;
  refresh: boolean;
  limit: number;
}
