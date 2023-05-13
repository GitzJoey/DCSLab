import { CompanyType } from "./CompanyType"
import { BranchType } from "./BranchType"
import { PurchaseOrderType } from "./PurchaseOrderType"

export interface PurchaseOrderDiscountType {
    id: string,
    ulid: string,
    company: CompanyType,
    branch: BranchType,
    purchase_order: PurchaseOrderType,
    discount_type: string,
    amount: number,
}