import { Company } from "./Company"
import { CustomerGroup } from "./CustomerGroup"
import { CustomerAddress } from "./CustomerAddress"
import { User } from "./User"

export interface Customer {
    id: string,
    ulid: string,
    company: Company,
    code: string,
    name: string,
    is_member: boolean,
    customer_group: CustomerGroup,
    zone: string,
    customer_addresses: CustomerAddress[],
    max_open_invoice: number,
    max_outstanding_invoice: number,
    max_invoice_age: number,
    payment_term_type: string,
    payment_term: number,
    tax_id: string,
    taxable_enterprise: boolean,
    remarks: string,
    status: string,
    customer_pic: User
}