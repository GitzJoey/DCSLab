import { ErrorResponseType } from "./ErrorResponseType";

export interface ServiceResponseType<T> {
    success: boolean,
    statusCode: number,
    statusDescription: string,
    data?: T,
    errors?: ErrorResponseType
}
