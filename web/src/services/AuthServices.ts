import { authAxiosInstance } from "../axios";

export default class AuthService {
    public doLogin(emailText: string, passwordText: string, rememberMeCheck: boolean): boolean {
        let result = false;
        authAxiosInstance.get('/sanctum/csrf-cookie').then(() => {
            authAxiosInstance.post('login', { email: emailText, password: passwordText }).then(response => {
                result = true;
            })
        })

        return result;
    }
}