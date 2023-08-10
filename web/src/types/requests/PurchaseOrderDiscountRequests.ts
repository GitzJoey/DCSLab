import { CompanyRequest } from "./CompanyRequests"
import { BranchRequest } from "./BranchFormRequest"
import { PurchaseOrderRequest } from "./PurchaseOrderRequests"

export interface PurchaseOrderDiscountRequest {
    id: string,
    ulid: string,
    company: CompanyRequest,
    branch: BranchRequest,
    purchase_order: PurchaseOrderRequest,
    discount_type: string,
    amount: number,
}