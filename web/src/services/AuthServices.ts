import { AxiosError, AxiosResponse, isAxiosError } from "axios";
import { authAxiosInstance } from "../axios";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { Resource } from "../types/resources/Resource";
import { UserProfile } from "../types/models/UserProfile";
import { LoginRequest, RegisterRequest, ForgotPasswordRequest, ResetPasswordRequest } from "../types/requests/AuthRequests";
import ErrorHandlerService from "./ErrorHandlerService";
import { ForgotPassword } from "../types/models/ForgotPassword";
import { ResetPassword } from "../types/models/ResetPassword";

export default class AuthService {
    private errorHandlerService;

    constructor() {
        this.errorHandlerService = new ErrorHandlerService();
    }

    public async doLogin(request: LoginRequest): Promise<ServiceResponse<UserProfile | null>> {
        const result: ServiceResponse<UserProfile | null> = {
            success: false
        }

        try {
            await authAxiosInstance.get('/sanctum/csrf-cookie');
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

    public async register(request: RegisterRequest): Promise<ServiceResponse<UserProfile | null>> {
        const result: ServiceResponse<UserProfile | null> = {
            success: false
        }

        try {
            await authAxiosInstance.get('/sanctum/csrf-cookie');
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

    public async requestResetPassword(request: ForgotPasswordRequest): Promise<ServiceResponse<ForgotPassword | null>> {
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

    public async resetPassword(request: ResetPasswordRequest): Promise<ServiceResponse<ResetPassword | null>> {
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