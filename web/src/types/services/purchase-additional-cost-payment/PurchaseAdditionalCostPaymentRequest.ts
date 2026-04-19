export interface PurchaseAdditionalCostPaymentReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  purchase_additional_cost_id?: string | null;
  include_id?: string;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface PurchaseAdditionalCostPaymentReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string;
  purchase_additional_cost_id?: string | null;
  include_id?: string;
  refresh: boolean;
  limit: number;
}
