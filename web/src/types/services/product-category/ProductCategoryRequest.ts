export interface ProductCategoryReadAnyPaginateRequest {
    with_trashed: boolean;
    company_id: string;
    search?: string | null;
    type?: number | null;
    include_id?: string;
    refresh: boolean;
    page: number;
    per_page: number;
}

export interface ProductCategoryReadAnyGetRequest {
    with_trashed: boolean;
    company_id: string;
    search?: string | null;
    type?: number | null;
    include_id?: string;
    refresh: boolean;
    limit: number;
}
