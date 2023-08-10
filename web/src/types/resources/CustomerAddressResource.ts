import { CompanyResource } from "./CompanyResource"
import { CustomerResource } from "./CustomerResource"

export interface CustomerAddressResource {
    ulid: string,
    company: CompanyResource[],
    customer: CustomerResource[],
    address: string,
    city: string,
    contact: string,
    is_main: boolean,
    remarks: string,
}