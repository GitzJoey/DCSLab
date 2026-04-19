export interface PurchaseReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  start_date?: string;
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
  search?: string;
  start_date?: string;
  end_date?: string;
  supplier_id?: string | null;
  refresh: boolean;
  limit: number;
}
