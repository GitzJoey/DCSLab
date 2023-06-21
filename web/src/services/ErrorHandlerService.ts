import { AxiosError, AxiosResponse } from "axios";
import { ServiceResponse } from "../types/services/ServiceResponse";

export default class ErrorHandlerService {
    public generateZiggyUrlErrorServiceResponse(message?: string): ServiceResponse<null> {
        return {
            success: false,
            errors: {
                ziggy: [message ? message : 'Ziggy error: unknown'],
            }
        };
    }

    public generateAxiosErrorServiceResponse(axiosErr: AxiosError): ServiceResponse<null> {
        const axiosResp = axiosErr.response as AxiosResponse;

        return {
            success: false,
            errors: {
                axios: [
                    axiosResp.data.message,
                    axiosResp.status,
                    axiosResp.statusText
                ]
            }
        }
    }
}