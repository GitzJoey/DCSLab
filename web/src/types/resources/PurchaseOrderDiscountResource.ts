import { CompanyResource } from "./CompanyResource"
import { BranchResource } from "./BranchResource"
import { PurchaseOrderResource } from "./PurchaseOrderResource"

export interface PurchaseOrderDiscountResource {
    id: string,
    ulid: string,
    company: CompanyResource[],
    branch: BranchResource[],
    purchase_order: PurchaseOrderResource[],
    discount_type: string,
    amount: number,
}