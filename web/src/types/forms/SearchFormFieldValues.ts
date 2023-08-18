export interface SearchFormFieldValues {
    search: string | null,
    refresh: boolean,
    paginate: boolean,
    page?: number,
    per_page?: number,
}