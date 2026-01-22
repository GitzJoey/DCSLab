export interface CashAccountReadAnyPaginateRequest {
    with_trashed: boolean;
    company_id: string;
    search?: string | null;
    branch_id?: string | null;
    include_id?: string;
    refresh: boolean;
    page: number;
    per_page: number;
}

export interface CashAccountReadAnyGetRequest {
    with_trashed: boolean;
    company_id: string;
    search?: string | null;
    branch_id?: string | null;
    include_id?: string;
    refresh: boolean;
    limit: number;
}
