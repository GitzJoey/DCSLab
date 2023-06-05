import { CompanyType } from "./CompanyType"
import { CustomerType } from "./CustomerType"

export interface CustomerAddressType {
    ulid: string,
    company: CompanyType,
    customer: CustomerType,
    address: string,
    city: string,
    contact: string,
    is_main: boolean,
    remarks: string,
}