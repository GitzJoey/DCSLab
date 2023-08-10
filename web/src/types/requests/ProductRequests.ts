import { CompanyRequest } from "./CompanyRequests"
import { ProductGroupRequest } from "./ProductGroupRequests"
import { BrandRequest } from "./BrandRequests"
import { ProductUnitRequest } from "./ProductUnitRequests"

export interface ProductRequest {
    id: string,
    ulid: string,
    company: CompanyRequest,
    product_group: ProductGroupRequest,
    brand: BrandRequest,
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
    product_units: ProductUnitRequest[],
}