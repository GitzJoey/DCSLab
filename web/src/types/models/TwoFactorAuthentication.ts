export interface TwoFactorResponse {
    message: string
}

export interface QRCode {
    svg: string,
    url: string,
}

export interface ConfirmedPasswordStatus {
    confirmed: boolean
}

