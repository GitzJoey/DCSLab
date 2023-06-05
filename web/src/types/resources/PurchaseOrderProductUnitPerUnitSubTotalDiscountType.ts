import { CompanyType } from "./CompanyType"
import { BranchType } from "./BranchType"
import { PurchaseOrderType } from "./PurchaseOrderType"
import { PurchaseOrderProductUnitType } from "./PurchaseOrderProductUnitType"

export interface PurchaseOrderProductUnitPerUnitSubTotalDiscountType {
    id: string,
    ulid: string,
    company: CompanyType,
    branch: BranchType,
    purchase_order: PurchaseOrderType,
    purchase_order_product_unit: PurchaseOrderProductUnitType,
    discount_type: string,
    amount: number,
}