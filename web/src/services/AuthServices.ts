import { AxiosRequestConfig, AxiosResponse } from "axios";
import { authAxiosInstance } from "../axios";

export default class AuthService {
    public async doLogin(emailText: string, passwordText: string, rememberMeCheck: boolean): Promise<AxiosResponse> {
        let result = {} as AxiosResponse;

        try {
            await authAxiosInstance.get('/sanctum/csrf-cookie');
            result = await authAxiosInstance.post('login', { email: emailText, password: passwordText });
            return result;
        } catch (e: any) {
            return e.response;
        }
    }

    public async register(nameText: string, emailText: string, passwordText: string, termsCheck: boolean): Promise<AxiosResponse> {
        let result = {} as AxiosResponse;

        try {
            await authAxiosInstance.get('/sanctum/csrf-cookie');
            result = await authAxiosInstance.post('register', { name: nameText, email: emailText, password: passwordText, terms: termsCheck });
            return result;
        } catch (e: any) {
            return e.response;
        }
    }
}