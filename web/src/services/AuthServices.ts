import { AxiosError, AxiosResponse } from "axios";
import { authAxiosInstance } from "../axios";
import { ServiceResponse } from "../types/services/ServiceResponse";
import { Resource } from "../types/resources/Resource";
import { UserProfile } from "../types/models/UserProfile";
import { LoginRequest, RegisterRequest } from "../types/requests/AuthRequests";

export default class AuthService {
    public async doLogin(request: LoginRequest): Promise<ServiceResponse<UserProfile | null>> {
        try {
            await authAxiosInstance.get('/sanctum/csrf-cookie');
            const response: AxiosResponse<Resource<UserProfile>> = await authAxiosInstance.post('login', {
                email: request.email,
                password: request.password,
                remember: request.remember
            });

            return {
                success: true,
                data: response.data.data,
            }
        } catch (e: unknown) {
            return {
                success: false,
                error: e as AxiosError,
            };
        }
    }

    public async register(request: RegisterRequest): Promise<ServiceResponse<UserProfile | null>> {
        try {
            await authAxiosInstance.get('/sanctum/csrf-cookie');
            const response: AxiosResponse<Resource<UserProfile>> = await authAxiosInstance.post('register', {
                name: request.name,
                email: request.email,
                password: request.password,
                terms: request.terms
            });

            return {
                success: true,
                data: response.data.data
            }
        } catch (e: unknown) {
            return {
                success: false,
                error: e as AxiosError
            }
        }
    }
}