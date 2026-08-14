export interface SalesOrderItemReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  sales_order_code?: string;
  sales_order_start_date?: string;
  sales_order_end_date?: string;
  sales_order_customer_id?: string | null;
  product_unit_code?: string;
  product_unit_product_name?: string;
  product_unit_product_category_id?: string | null;
  product_unit_product_brand_id?: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface SalesOrderItemReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  sales_order_code?: string;
  sales_order_start_date?: string;
  sales_order_end_date?: string;
  sales_order_customer_id?: string | null;
  product_unit_code?: string;
  product_unit_product_name?: string;
  product_unit_product_category_id?: string | null;
  product_unit_product_brand_id?: string | null;
  refresh: boolean;
  limit: number;
}
