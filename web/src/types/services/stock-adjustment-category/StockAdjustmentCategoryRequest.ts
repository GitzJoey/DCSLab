export interface StockAdjustmentCategoryReadAnyPaginateRequest {
    with_trashed: boolean;
    company_id: string;
    search?: string | null;
    include_id?: string;
    refresh: boolean;
    page: number;
    per_page: number;
}

export interface StockAdjustmentCategoryReadAnyGetRequest {
    with_trashed: boolean;
    company_id: string;
    search?: string | null;
    include_id?: string;
    refresh: boolean;
    limit: number;
}

