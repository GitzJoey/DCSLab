import { Company } from "./Company";
import { PaymentTermType } from "../enums/PaymentTermType";

export interface Supplier {
    id: string;
    ulid: string;
    company: Company;
    code: string;
    name: string;
    address: string;
    city: string;
    payment_term_type: PaymentTermType;
    payment_term: number;
    taxable_enterprise: boolean;
    tax_id: string;
    status: string;
    remarks: string;
}
