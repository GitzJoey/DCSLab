export interface FormRequest<T> {
    data: T,
    additional?: Record<string, string>
}