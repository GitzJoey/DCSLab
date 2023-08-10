import { CompanyRequest } from "./CompanyRequests"
import { CustomerGroupRequest } from "./CustomerGroupRequests"
import { CustomerAddressRequest } from "./CustomerAddressRequests"
import { UserRequest } from "./UserRequests"

export interface CustomerRequest {
    id: string,
    ulid: string,
    company: CompanyRequest,
    code: string,
    name: string,
    is_member: boolean,
    customer_group: CustomerGroupRequest,
    zone: string,
    customer_addresses: CustomerAddressRequest[],
    max_open_invoice: number,
    max_outstanding_invoice: number,
    max_invoice_age: number,
    payment_term_type: string,
    payment_term: number,
    tax_id: string,
    taxable_enterprise: boolean,
    remarks: string,
    status: string,
    customer_pic: UserRequest
}