import { client, useForm } from "laravel-precognition-vue";

export default class AuthService {
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
        await fetch('/sanctum/csrf-cookie');
    }

    public useLoginForm() {
        client.axios().defaults.withCredentials = true;
        const form = useForm('post', import.meta.env.VITE_BACKEND_URL + '/login', {
            email: '',
            password: '',
            remember: false,
        });

        return form;
    }

    public useRegisterForm() {
        client.axios().defaults.withCredentials = true;
        const form = useForm('post', import.meta.env.VITE_BACKEND_URL + '/register', {
            name: '',
            email: '',
            password: '',
            password_confirmation: '',
            terms: false,
        });

        return form;
    }

    public useRequestResetPasswordForm() {
        client.axios().defaults.withCredentials = true;
        const form = useForm('post', import.meta.env.VITE_BACKEND_URL + '/forgot-password', {
            email: '',
        });

        return form;
    }

    public useResetPasswordForm() {
        client.axios().defaults.withCredentials = true;
        const form = useForm('post', import.meta.env.VITE_BACKEND_URL + '/reset-password', {
            email: '',
        });

        return form;
    }
}