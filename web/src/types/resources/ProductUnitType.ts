import { CompanyType } from "./CompanyType"
import { ProductType } from "./ProductType"
import { UnitType } from "./UnitType"

export interface ProductUnitType {
    id: string,
    ulid: string,
    company: CompanyType,
    product: ProductType,
    code: string,
    unit: UnitType,
    is_base: boolean,
    conversion_value: number,
    is_primary_unit: boolean,
    remarks: string,
}