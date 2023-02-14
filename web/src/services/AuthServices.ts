import { authAxiosInstance } from "../axios";

export class AuthService {
    public doLogin(): boolean {
        let result = false;
        authAxiosInstance.get('/sanctum/csrf-cookie').then(() => {
            console.log('1');
        }).catch(e => {
            console.log(e);
            result = false;
        });

        authAxiosInstance.post('login', { email: 'gitzjoey@yahoo.com', password: 'qweadszxc' }).then(response => {
            console.log('2');
            result = true;
        }).catch(e => {
            console.log('3');
            result = false;
        })

        return result;
    }
}