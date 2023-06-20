import { ServiceResponse } from "../types/services/ServiceResponse";

export default class ErrorHandlerService {
    public generateZiggyUrlErrorServiceResponse(message?: string): ServiceResponse<null> {
        return {
            success: false,
            error: {
                ziggy: message ? message : 'Ziggy error: unknown',
            }
        };
    }
}