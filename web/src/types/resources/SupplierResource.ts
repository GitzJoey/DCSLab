import { CompanyResource } from "./CompanyResource"
import { UserResource } from "./UserResource"
import { SupplierProductResource } from "./SupplierProductResource"

export interface SupplierResource {
    id: string,
    ulid: string,
    company: CompanyResource[],
    code: string,
    name: string,
    contact: string,
    address: string,
    city: string,
    payment_term_type: string,
    payment_term: number,
    taxable_enterprise: number,
    tax_id: string,
    remarks: string,
    status: string,
    supplier_pic: UserResource[],
    supplier_products: SupplierProductResource[],
    selected_products: string[],
    main_products: string[],
}