import { Company } from "./Company"
import { Supplier } from "./Supplier"
import { Product } from "./Product"

export interface SupplierProduct {
    id: string,
    ulid: string,
    company: Array<Company>,
    supplier: Array<Supplier>,
    product: Array<Product>,
    main_product: boolean,
}