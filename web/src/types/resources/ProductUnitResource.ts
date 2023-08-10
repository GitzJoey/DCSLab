import { CompanyResource } from "./CompanyResource"
import { ProductResource } from "./ProductResource"
import { UnitResource } from "./UnitResource"

export interface ProductUnitResource {
    id: string,
    ulid: string,
    company: CompanyResource[],
    product: ProductResource[],
    code: string,
    unit: UnitResource[],
    is_base: boolean,
    conversion_value: number,
    is_primary_unit: boolean,
    remarks: string,
}