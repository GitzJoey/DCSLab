export interface SearchRequest {
    paginate: boolean,
    search: string | null,
    page?: number,
    per_page?: number,
    refresh: boolean,
}