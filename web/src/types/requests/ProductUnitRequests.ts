import { CompanyRequest } from "./CompanyRequests"
import { ProductRequest } from "./ProductRequests"
import { UnitRequest } from "./UnitRequests"

export interface ProductUnitRequest {
    id: string,
    ulid: string,
    company: CompanyRequest,
    product: ProductRequest,
    code: string,
    unit: UnitRequest,
    is_base: boolean,
    conversion_value: number,
    is_primary_unit: boolean,
    remarks: string,
}