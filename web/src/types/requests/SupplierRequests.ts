import { CompanyRequest } from "./CompanyRequests"
import { UserRequest } from "./UserRequests"
import { SupplierProductRequest } from "./SupplierProductRequests"

export interface SupplierRequest {
    id: string,
    ulid: string,
    company: CompanyRequest,
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
    supplier_pic: UserRequest,
    supplier_products: SupplierProductRequest[],
    selected_products: string[],
    main_products: string[],
}