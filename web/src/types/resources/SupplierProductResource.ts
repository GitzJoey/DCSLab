import { CompanyResource } from "./CompanyResource"
import { SupplierResource } from "./SupplierResource"
import { ProductResource } from "./ProductResource"

export interface SupplierProductResource {
    id: string,
    ulid: string,
    company: CompanyResource[],
    supplier: SupplierResource[],
    product: ProductResource[],
    main_product: boolean,
}