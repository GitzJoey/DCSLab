export interface BranchReadAnyPaginateRequest {
    with_trashed: boolean;
    company_id: string;
    search?: string | null;
    is_main?: boolean;
    status?: string | number;
    include_id?: string;
    refresh: boolean;
    page: number;
    per_page: number;
}

export interface BranchReadAnyGetRequest {
    with_trashed: boolean;
    company_id: string;
    search?: string | null;
    is_main?: boolean;
    status?: string | number;
    include_id?: string;
    refresh: boolean;
    limit: number;
}
