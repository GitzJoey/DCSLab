export interface ReceivableReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  category_id?: string | null;
  customer_id?: string | null;
  is_paid_off?: boolean | null;
  include_id?: string;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface ReceivableReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  category_id?: string | null;
  customer_id?: string | null;
  is_paid_off?: boolean | null;
  include_id?: string;
  refresh: boolean;
  limit: number;
}

