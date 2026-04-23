export interface LiabilityReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  category_id?: string | null;
  creditor_id?: string | null;
  supplier_id?: string | null;
  is_paid_off?: boolean | null;
  include_id?: string;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface LiabilityReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  category_id?: string | null;
  creditor_id?: string | null;
  supplier_id?: string | null;
  is_paid_off?: boolean | null;
  include_id?: string;
  refresh: boolean;
  limit: number;
}
