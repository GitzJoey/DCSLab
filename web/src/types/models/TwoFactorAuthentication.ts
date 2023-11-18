export interface TwoFactorResponse {
    message: string,
    error?: Record<string, Array<string>>
}

export interface QRCode {
    svg: string,
    url: string,
}

export interface ConfirmPasswordStatusResponse {
    confirmed: boolean
}

export interface SecretKeyResponse {
    secretKey: string
}