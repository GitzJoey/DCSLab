export interface CompanyReadAnyPaginateRequest {
    with_trashed: boolean;
    search?: string | null;
    default?: boolean;
    status?: string | number;
    include_id?: string;
    refresh: boolean;
    page: number;
    per_page: number;
}

export interface CompanyReadAnyGetRequest {
    with_trashed: boolean;
    search?: string | null;
    default?: boolean;
    status?: string | number;
    include_id?: string;
    refresh: boolean;
    limit: number;
}
