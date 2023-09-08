import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import { authAxiosInstance } from "../axios";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { Resource } from "../types/resources/Resource";
import { UserProfile } from "../types/models/UserProfile";
import { LoginFormFieldValues, RegisterFormFieldValues, ForgotPasswordFormFieldValues, ResetPasswordFormFieldValues } from "../types/forms/AuthFormFieldValues";
import ErrorHandlerService from "./ErrorHandlerService";
import { ForgotPassword } from "../types/models/ForgotPassword";
import { ResetPassword } from "../types/models/ResetPassword";
import { client, useForm } from "laravel-precognition-vue";

export default class AuthService {
    private errorHandlerService;

    constructor() {
        this.errorHandlerService = new ErrorHandlerService();
    }

    public async ensureCSRF(): Promise<void> {
        let resultXSRF = await this.checkCookieExists('XSRF-TOKEN');

        if (resultXSRF) return;

        await this.generateCSRF();
    }

    private checkCookieExists = (cookieName: string): Promise<boolean> => {
        return new Promise<boolean>((resolve) => {
            const cookies = document.cookie.split('; ');

            for (const cookie of cookies) {
                const [name] = cookie.split('=');
                if (name === cookieName) {
                    resolve(true);
                    return;
                }
            }

            resolve(false);
        });
    };

    public async generateCSRF(): Promise<void> {
        await authAxiosInstance.get('/sanctum/csrf-cookie');
    }

    public createLoginForm() {
        client.axios().defaults.withCredentials = true;
        const form = useForm('post', import.meta.env.VITE_BACKEND_URL + '/login', {
            email: '',
            password: '',
            remember: false,
        });

        return form;
    }

    public async doLogin(request: LoginFormFieldValues): Promise<ServiceResponse<UserProfile | null>> {
        const result: ServiceResponse<UserProfile | null> = {
            success: false
        }

        try {
            this.ensureCSRF();
            const response: AxiosResponse<Resource<UserProfile>> = await authAxiosInstance.post('login', {
                email: request.email,
                password: request.password,
                remember: request.remember
            });

            result.success = true;
            result.data = response.data.data;

            return result;
        } catch (e: unknown) {
            return result;
        }
    }

    public async register(request: RegisterFormFieldValues): Promise<ServiceResponse<UserProfile | null>> {
        const result: ServiceResponse<UserProfile | null> = {
            success: false
        }

        try {
            this.ensureCSRF();
            const response: AxiosResponse<Resource<UserProfile>> = await authAxiosInstance.post('register', {
                name: request.name,
                email: request.email,
                password: request.password,
                password_confirmation: request.password_confirmation,
                terms: request.terms
            });

            result.success = true;
            result.data = response.data.data;

            return result;
        } catch (e: unknown) {
            return result;
        }
    }

    public async requestResetPassword(request: ForgotPasswordFormFieldValues): Promise<ServiceResponse<ForgotPassword | null>> {
        const result: ServiceResponse<ForgotPassword> = {
            success: false
        }

        try {
            const response: AxiosResponse<ForgotPassword> = await authAxiosInstance.post('forgot-password', {
                email: request.email,
            });

            result.success = true;
            result.data = response.data as ForgotPassword;

            return result;
        } catch (e: unknown) {
            if (isAxiosError(e)) {
                return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
            } else {
                return result;
            }
        }
    }

    public async resetPassword(request: ResetPasswordFormFieldValues): Promise<ServiceResponse<ResetPassword | null>> {
        const result: ServiceResponse<ResetPassword> = {
            success: false
        }

        try {
            const response: AxiosResponse<ResetPassword> = await authAxiosInstance.post('reset-password', {
                email: request.email,
            });

            result.success = true;
            result.data = response.data as ResetPassword;

            return result;
        } catch (e: unknown) {
            if (isAxiosError(e)) {
                return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
            } else {
                return result;
            }
        }
    }
}