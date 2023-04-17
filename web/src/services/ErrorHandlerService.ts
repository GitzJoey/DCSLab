import { AxiosError } from "axios";
import { ServiceResponseType } from "../types/ServiceResponseType";
import { ErrorResponseType } from "../types/ErrorResponseType";

export default class ErrorHandlerService {
    public generateErrorServiceResponse(e: AxiosError): ServiceResponseType<null> {
        const errorsResponse: ErrorResponseType = {};

        if (e.response) {
            for (const outerErr in (e.response.data as { errors: unknown[] }).errors) {
                console.log(outerErr);
                for (const innerErr in (e.response.data as { errors: unknown[] }).errors[outerErr] as { [key: number]: string }) {
                    console.log(innerErr);
                }
                
                
            }
            
            return {
                success: false,
                statusCode: e.response.status,
                statusDescription: e.response.statusText,
                data: null,
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