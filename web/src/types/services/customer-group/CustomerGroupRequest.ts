export interface CustomerGroupReadAnyPaginateRequest {
    with_trashed: boolean;
    company_id: string;
    search?: string | null;
    include_id?: string;
    refresh: boolean;
    page: number;
    per_page: number;
}

export interface CustomerGroupReadAnyGetRequest {
    with_trashed: boolean;
    company_id: string;
    search?: string | null;
    include_id?: string;
    refresh: boolean;
    limit: number;
}

