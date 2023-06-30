export interface LoginRequest {
    email: string,
    password: string,
    remember: boolean | string | undefined,
}

export interface RegisterRequest {
    name: string,
    email: string,
    password: string,
    terms: boolean,
}

export interface ForgotPasswordRequest {
    email: string,
}

export interface ResetPasswordRequest {
    token: string,
    email: string,
}