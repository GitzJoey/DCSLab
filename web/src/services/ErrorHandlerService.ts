import { AxiosError } from "axios";
import { ServiceResponseType } from "../types/ServiceResponseType";

export default class ErrorHandlerService {
    public handleLaravelError(data: any, actions: any, forceSetToField?: string): void {
        if (data.errors !== undefined && Object.keys(data.errors).length > 0) {
            for (let key in data.errors) {
                for (let i = 0; i < data.errors[key].length; i++) {
                    actions.setFieldError(key, data.errors[key][i]);
                }
            }
        } else {
            if (forceSetToField) {
                actions.setFieldError(forceSetToField, data.status + ' ' + data.statusText +': ' + data.message);
            } else {
                console.error(data.status + ' ' + data.statusText);
            }
        }
    }

    public generateErrorServiceResponse(e: AxiosError): ServiceResponseType<null> {
        if (e.response) {
            return {
                success: false,
                statusCode: e.response.status,
                statusDescription: e.response.statusText,
                data: null
            };
        } else {
            return {
                success: false,
                statusCode: 0,
                statusDescription: '',
                data: null
            };
        }
    }

    public generateZiggyUrlErrorServiceResponse(message?: string) {
        return {
            success: false,
            statusCode: 0,
            statusDescription: message ? message : 'Ziggy Error',
            data: null
        };
    }
}