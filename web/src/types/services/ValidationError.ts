export interface ValidationError {
    message: string,
    errors: {
        [key: string]: string[]
    }
}