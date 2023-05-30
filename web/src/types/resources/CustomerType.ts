import { CompanyType } from "./CompanyType"
import { CustomerGroupType } from "./CustomerGroupType"
import { CustomerAddressType } from "./CustomerAddressType"
import { UserType } from "./UserType"

export interface CustomerType {
    id: string,
    ulid: string,
    company: CompanyType,
    code: string,
    name: string,
    is_member: boolean,
    customer_group: CustomerGroupType,
    zone: string,
    customer_addresses: CustomerAddressType[],
    max_open_invoice: number,
    max_outstanding_invoice: number,
    max_invoice_age: number,
    payment_term_type: string,
    payment_term: number,
    tax_id: string,
    taxable_enterprise: boolean,
    remarks: string,
    status: string,
    customer_pic: UserType
}