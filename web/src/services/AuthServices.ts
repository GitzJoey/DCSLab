import { client, useForm } from "laravel-precognition-vue";
import { authAxiosInstance } from "../axios";
import { getBackendUrl } from "@/utils/config";

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
        await authAxiosInstance.get('/sanctum/csrf-cookie');
    }

    public useLoginForm() {
        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', getBackendUrl() + '/login', {
            email: '',
            password: '',
            remember: false,
        });

        return form;
    }

    public useTwoFactorLoginForm() {
        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', getBackendUrl() + '/two-factor-challenge', {
            code: '',
            recovery_code: '',
        });

        return form;
    }

    public useRegisterForm() {
        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', getBackendUrl() + '/register', {
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
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', getBackendUrl() + '/forgot-password', {
            email: '',
        });

        return form;
    }

    public useResetPasswordForm() {
        client.axios().defaults.withCredentials = true;
        client.axios().defaults.withXSRFToken = true;
        const form = useForm('post', getBackendUrl() + '/reset-password', {
            email: '',
            token: '',
            password: '',
            password_confirmation: '',
        });

        return form;
    }
}