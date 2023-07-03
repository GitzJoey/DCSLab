import { Company } from "./Company"
import { Branch } from "./Branch"
import { Supplier } from "./Supplier"
import { PurchaseOrderDiscount } from "./PurchaseOrderDiscount"
import { PurchaseOrderProductUnit } from "./PurchaseOrderProductUnit"

export interface PurchaseOrder {
    id: string,
    ulid: string,
    company: Company,
    branch: Branch,
    invoice_code: string,
    invoice_date: string,
    shipping_date: string,
    shipping_address: string,
    supplier: Supplier,
    global_discounts: Array<PurchaseOrderDiscount>,
    product_units: Array<PurchaseOrderProductUnit>,
    remarks: string,
    status: string,
}