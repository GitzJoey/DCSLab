import { AxiosRequestConfig } from "axios";
import { authAxiosInstance } from "../axios";

export default class AuthService {
    private async axiosCall<T>(config: AxiosRequestConfig) {
        try {
            const { data } = await authAxiosInstance.request(config);
            return [ null, data];
        } catch (e) {
            return [ e ];
        }
    }


    public async doLogin(emailText: string, passwordText: string, rememberMeCheck: boolean): Promise<boolean> {
        let result = false;
        
        authAxiosInstance.get('/sanctum/csrf-cookie').then(() => {
            authAxiosInstance.post('login', { email: emailText, password: passwordText }).then(response => {
                result = true;
            })
        })

        return result;
    }
}