import { Company } from "./Company"
import { ProductGroup } from "./ProductGroup"
import { Brand } from "./Brand"
import { ProductUnit } from "./ProductUnit"

export interface Product {
    id: string,
    ulid: string,
    company?: Company | null,
    code: string,
    product_group: ProductGroup,
    brand: Brand,
    name: string,
    product_units?: Array<ProductUnit | null>,
    product_type: string,
    taxable_supply: string,
    standard_rated_supply: number,
    price_include_vat: boolean,
    point: number,
    use_serial_number: boolean,
    has_expiry_date: boolean,
    status: string,
    remarks: string,
}