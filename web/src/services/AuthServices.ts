import { AxiosError, AxiosResponse } from "axios";
import { authAxiosInstance } from "../axios";
import { ServiceResponseType } from "../types/ServiceResponseType";
import { UserType } from "../types/resources/UserType";
import ErrorHandlerService from "./ErrorHandlerService";

export default class AuthService {
    private errorHandlerService;
    
    constructor() {
        this.errorHandlerService = new ErrorHandlerService();
    }

    public async doLogin(emailText: string, passwordText: string, rememberMeCheck: boolean): Promise<ServiceResponseType<UserType | null>> {
        try {
            await authAxiosInstance.get('/sanctum/csrf-cookie');
            const response: AxiosResponse<UserType> = await authAxiosInstance.post('login', { email: emailText, password: passwordText, remember: rememberMeCheck });

            return {
                success: true,
                statusCode: response.status,
                statusDescription: response.statusText,
                data: response.data
            }
        } catch (e: unknown) {
            return this.errorHandlerService.generateErrorServiceResponse(e as AxiosError<unknown, unknown>);
        }
    }

    public async register(nameText: string, emailText: string, passwordText: string, termsCheck: boolean): Promise<ServiceResponseType<UserType | null>> {
        try {
            await authAxiosInstance.get('/sanctum/csrf-cookie');
            const response: AxiosResponse<UserType> = await authAxiosInstance.post('register', { name: nameText, email: emailText, password: passwordText, terms: termsCheck });

            return {
                success: true,
                statusCode: response.status,
                statusDescription: response.statusText,
                data: response.data
            }
        } catch (e: unknown) {
            return this.errorHandlerService.generateErrorServiceResponse(e as AxiosError<unknown, unknown>);
        }
    }
}