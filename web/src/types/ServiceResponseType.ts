export interface ServiceResponseType<T> {
    success: boolean,
    statusCode: number,
    statusDescription: string,
    data: T,
}
