import { authAxiosInstance } from "../axios";

export class AuthService {
    public doLogin(): boolean {
        let result = false;
        authAxiosInstance.get('/sanctum/csrf-cookie').then(() => {
            authAxiosInstance.post('login', { email: 'gitzjoey@yahoo.com', password: 'qweadszxc' }).then(response => {
                result = true;
            }).catch(e => {
                throw e;
            })
        }).catch(e => {
            throw e;
        });

        return result;
    }
}