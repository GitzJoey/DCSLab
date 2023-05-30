export interface LaravelErrorType {
    response: {
        data: {
            errors: Map<string, string>[],
            message: string,
        }
        status: number,
        statusText: string,
    }
}
