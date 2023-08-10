import { CompanyRequest } from "./CompanyRequests"
import { BranchRequest } from "./BranchFormRequest"
import { PurchaseOrderRequest } from "./PurchaseOrderRequests"
import { PurchaseOrderProductUnitRequest } from "./PurchaseOrderProductUnitRequests"

export interface PurchaseOrderProductUnitPerUnitSubTotalDiscountRequest {
    id: string,
    ulid: string,
    company: CompanyRequest,
    branch: BranchRequest,
    purchase_order: PurchaseOrderRequest,
    purchase_order_product_unit: PurchaseOrderProductUnitRequest,
    discount_type: string,
    amount: number,
}