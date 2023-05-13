import { CompanyType } from "./CompanyType"
import { SupplierType } from "./SupplierType"
import { ProductType } from "./ProductType"

export interface SupplierProductType {
    id: string,
    ulid: string,
    company: CompanyType,
    supplier: SupplierType,
    product: ProductType,
    main_product: boolean,
}