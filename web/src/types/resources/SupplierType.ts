import { CompanyType } from "./CompanyType"
import { UserType } from "./UserType"
import { SupplierProductType } from "./SupplierProductType"

export interface SupplierType {
    id: string,
    ulid: string,
    company: CompanyType,
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
    supplier_pic: UserType,
    supplier_products: SupplierProductType[],
    selected_products: string[],
    main_products: string[],
}