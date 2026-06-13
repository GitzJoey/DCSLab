export interface ReadAnyRequest {
    company_id?: string,
    search: string | null,
    refresh: boolean,
    paginate: boolean,
    page?: number,
    per_page?: number,
}