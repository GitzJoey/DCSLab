export interface LoginFormFieldValues {
    email: string,
    password: string,
    remember: boolean | string | undefined,
}

export interface RegisterFormFieldValues {
    name: string,
    email: string,
    password: string,
    password_confirmation: string,
    terms: boolean,
}

export interface ForgotPasswordFormFieldValues {
    email: string,
}

export interface ResetPasswordFormFieldValues {
    token: string,
    email: string,
    password: string,
    password_confirmation: string,
}