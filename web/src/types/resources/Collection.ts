export interface Collection<T extends Array<unknown> | readonly unknown[]> {
    data: T,
    meta: {
        current_page: number,
        from: number | null,
        last_page: number,
        path: string,
        per_page: number,
        to: number | null,
        total: number,
    },
    links: {
        first: string,
        last: string,
        prev: string | null,
        next: string | null,
    }
}