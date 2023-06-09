import { ServiceResponse } from "../types/services/ServiceResponse";

export default class ErrorHandlerService {
    public generateZiggyUrlErrorServiceResponse(message?: string): ServiceResponse<null> {
        return {
            success: false,
            error: {
                message: message ? message : 'Ziggy Error',
            }
        };
    }
}