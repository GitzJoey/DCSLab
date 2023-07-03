import { LaravelError } from "../errors/LaravelError";

export interface ServiceResponse<T> {
    success: boolean;
    data?: T;
    message?: string,
    errors?: LaravelError;
}