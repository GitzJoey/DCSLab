import { CompanyRequest } from "./CompanyRequests"
import { CustomerRequest } from "./CustomerRequests"

export interface CustomerAddressRequest {
    ulid: string,
    company: CompanyRequest,
    customer: CustomerRequest,
    address: string,
    city: string,
    contact: string,
    is_main: boolean,
    remarks: string,
}