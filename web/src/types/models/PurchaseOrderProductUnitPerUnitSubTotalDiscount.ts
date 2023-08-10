import { Company } from "./Company"
import { Branch } from "./Branch"
import { PurchaseOrder } from "./PurchaseOrder"
import { PurchaseOrderProductUnit } from "./PurchaseOrderProductUnit"

export interface PurchaseOrderProductUnitPerUnitSubTotalDiscount {
    id: string,
    ulid: string,
    company: Array<Company>,
    branch: Array<Branch>,
    purchase_order: Array<PurchaseOrder>,
    purchase_order_product_unit: Array<PurchaseOrderProductUnit>,
    discount_type: string,
    amount: number,
}