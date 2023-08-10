import { CompanyResource } from "./CompanyResource"
import { ProductGroupResource } from "./ProductGroupResource"
import { BrandResource } from "./BrandResource"
import { ProductUnitResource } from "./ProductUnitResource"

export interface ProductResource {
    id: string,
    ulid: string,
    company: CompanyResource,
    product_group: ProductGroupResource,
    brand: BrandResource,
    code: string,
    name: string,
    product_type: string,
    taxable_supply: string,
    standard_rated_supply: number,
    price_include_vat: boolean,
    point: number,
    use_serial_number: boolean,
    has_expiry_date: boolean,
    status: string,
    remarks: string,
    product_units: ProductUnitResource[],
}