import { AxiosError, AxiosResponse } from "axios";
import { ServiceResponse } from "../types/services/ServiceResponse";

export default class ErrorHandlerService {
    public generateZiggyUrlErrorServiceResponse(message?: string): ServiceResponse<null> {
        const result: ServiceResponse<null> = {
            success: false,
            errors: {
                ziggy: [message ? message : 'Ziggy error: unknown'],
            }
        }

        return result;
    }

    public generateAxiosValidationErrorServiceResponse(axiosErr: AxiosError): ServiceResponse<null> {
        const result: ServiceResponse<null> = {
            success: false,
        }

        const axiosResp = axiosErr.response as AxiosResponse;

        const keys = Object.keys(axiosResp.data.errors);
        for (const key of keys) {
            if (result.errors == undefined) {
                result.errors = {};
            }

            result.errors[key] = axiosResp.data.errors[key];
        }

        return result;
    }

    public generateAxiosErrorServiceResponse(axiosErr: AxiosError): ServiceResponse<null> {
        const axiosResp = axiosErr.response as AxiosResponse;

        const result: ServiceResponse<null> = {
            success: false,
            errors: {
                axios: [axiosResp.data.message + ' (' + axiosResp.status + ':' + axiosResp.statusText + ')']
            }
        }

        return result;
    }
}