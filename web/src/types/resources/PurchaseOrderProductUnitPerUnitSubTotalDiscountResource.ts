import { CompanyResource } from "./CompanyResource"
import { BranchResource } from "./BranchResource"
import { PurchaseOrderResource } from "./PurchaseOrderResource"
import { PurchaseOrderProductUnitResource } from "./PurchaseOrderProductUnitResource"

export interface PurchaseOrderProductUnitPerUnitSubTotalDiscountResource {
    id: string,
    ulid: string,
    company: CompanyResource[],
    branch: BranchResource[],
    purchase_order: PurchaseOrderResource[],
    purchase_order_product_unit: PurchaseOrderProductUnitResource[],
    discount_type: string,
    amount: number,
}