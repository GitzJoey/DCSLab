import { Company } from "./Company"
import { Customer } from "./Customer"

export interface CustomerAddress {
    ulid: string,
    company: Array<Company>,
    customer: Array<Customer>,
    address: string,
    city: string,
    contact: string,
    is_main: boolean,
    remarks: string,
}