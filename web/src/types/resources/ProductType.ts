import { CompanyType } from "./CompanyType"
import { ProductGroupType } from "./ProductGroupType"
import { BrandType } from "./BrandType"
import { ProductUnitType } from "./ProductUnitType"

export interface ProductType {
    id: string,
    ulid: string,
    company: CompanyType,
    product_group: ProductGroupType,
    brand: BrandType,
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
    product_units:ProductUnitType[],
}