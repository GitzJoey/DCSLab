import { Company } from "./Company"
import { Branch } from "./Branch"
import { PurchaseOrder } from "./PurchaseOrder"

export interface PurchaseOrderDiscount {
    id: string,
    ulid: string,
    company: Company,
    branch: Branch,
    purchase_order?: PurchaseOrder | null,
    discount_type: string,
    amount: number,
}