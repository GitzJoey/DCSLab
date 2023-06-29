export interface SearchRequest {
    search: string | null,
    refresh: boolean,
    paginate: boolean,
    page?: number,
    per_page?: number,
}