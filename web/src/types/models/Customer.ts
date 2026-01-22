import { Company } from "./Company";
import { CustomerGroup } from "./CustomerGroup";
import { User } from "./User";

export interface Customer {
    id: string,
    ulid: string,
    company: Company,
    user: User,
    code: string,
    is_member: boolean,
    name: string,
    group: CustomerGroup,
    zone: string,
    max_open_invoice: number,
    max_outstanding_invoice: number,
    max_invoice_age: number,
    payment_term_type: string,
    payment_term: number,
    taxable_enterprise: boolean,
    tax_id: string,
    status: string,
    remarks: string,
}

