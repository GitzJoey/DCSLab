export interface ConfirmPasswordStatusResponse {
    confirmed: boolean
}

export interface ConfirmPasswordResponse {
    message: string,
    error?: Record<string, Array<string>>
}