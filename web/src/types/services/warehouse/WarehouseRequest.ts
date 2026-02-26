export interface WarehouseReadAnyPaginateRequest {
    with_trashed: boolean;
    company_id: string;
    search?: string | null;
    branch_id?: string | null;
    status?: string | number;
    include_id?: string | null;
    refresh: boolean;
    page: number;
    per_page: number;
}

export interface WarehouseReadAnyGetRequest {
    with_trashed: boolean;
    company_id: string;
    search?: string | null;
    branch_id?: string | null;
    status?: string | number;
    include_id?: string | null;
    refresh: boolean;
    limit: number;
}
