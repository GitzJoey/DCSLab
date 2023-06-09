import { Company } from "./Company"
import { Branch } from "./Branch"
import { PurchaseOrder } from "./PurchaseOrder"
import { PurchaseOrderProductUnit } from "./PurchaseOrderProductUnit"

export interface PurchaseOrderProductUnitPerUnitDiscount {
    id: string,
    ulid: string,
    company: Company,
    branch: Branch,
    purchase_order: PurchaseOrder,
    purchase_order_product_unit: PurchaseOrderProductUnit,
    discount_type: string,
    amount: number,
}