import { CompanyRequest } from "./CompanyRequests"
import { SupplierRequest } from "./SupplierRequests"
import { ProductRequest } from "./ProductRequests"

export interface SupplierProductRequest {
    id: string,
    ulid: string,
    company: CompanyRequest,
    supplier: SupplierRequest,
    product: ProductRequest,
    main_product: boolean,
}