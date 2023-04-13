import { AxiosError } from "axios";
import { ServiceResponseType } from "../types/ServiceResponseType";

export default class ErrorHandlerService {
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