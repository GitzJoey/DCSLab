import { AxiosError } from "axios";
import { ServiceResponseType } from "../types/ServiceResponseType";
import { ErrorResponseType } from "../types/ErrorResponseType";

export default class ErrorHandlerService {
    public generateErrorServiceResponse(e: AxiosError): ServiceResponseType<null> {
        const errorsResponse: ErrorResponseType = {};

        if (e.response) {
            const errorData = (e.response.data as { errors: unknown[] }).errors;
            for (const key in errorData) {
                if (Object.prototype.hasOwnProperty.call(errorData, key)) {
                    const errorArray = errorData[key] as { [key:number]: string };

                    errorsResponse[key] = {};
                    (errorArray as unknown[]).forEach((errorMsg, index) => {
                        errorsResponse[key][index] = errorMsg as string;
                    });
                }
            }

            return {
                success: false,
                statusCode: e.response.status,
                statusDescription: e.response.statusText,
                data: null,
                errors: errorsResponse
            };
        } else {
            return {
                success: false,
                statusCode: 0,
                statusDescription: '',
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