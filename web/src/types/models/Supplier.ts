import { Company } from "./Company"
import { User } from "./User"
import { SupplierProduct } from "./SupplierProduct"

export interface Supplier {
    id: string,
    ulid: string,
    company: Company,
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
    supplier_pic: User,
    supplier_products: Array<SupplierProduct>,
    selected_products: Array<string>,
    main_products: Array<string>,
}