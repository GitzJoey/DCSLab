import { CompanyResource } from "./CompanyResource"
import { CustomerGroupResource } from "./CustomerGroupResource"
import { CustomerAddressResource } from "./CustomerAddressResource"
import { UserResource } from "./UserResource"

export interface CustomerResource {
    id: string,
    ulid: string,
    company: CompanyResource[],
    code: string,
    name: string,
    is_member: boolean,
    customer_group: CustomerGroupResource[],
    zone: string,
    customer_addresses: CustomerAddressResource[],
    max_open_invoice: number,
    max_outstanding_invoice: number,
    max_invoice_age: number,
    payment_term_type: string,
    payment_term: number,
    tax_id: string,
    taxable_enterprise: boolean,
    remarks: string,
    status: string,
    customer_pic: UserResource[]
}