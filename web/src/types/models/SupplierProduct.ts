import { Company } from "./Company"
import { Supplier } from "./Supplier"
import { Product } from "./Product"

export interface SupplierProduct {
    id: string,
    ulid: string,
    company: Company,
    supplier: Supplier,
    product: Product,
    main_product: boolean,
}