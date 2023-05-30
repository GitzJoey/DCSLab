import { CompanyType } from "./CompanyType"
import { BranchType } from "./BranchType"
import { SupplierType } from "./SupplierType"
import { PurchaseOrderDiscountType } from "./PurchaseOrderDiscountType"
import { PurchaseOrderProductUnitType } from "./PurchaseOrderProductUnitType"

export interface PurchaseOrderType {
    id: string,
    ulid: string,
    company: CompanyType,
    branch: BranchType,
    invoice_code: string,
    invoice_date: string,
    shipping_date: string,
    shipping_address: string,
    supplier: SupplierType,
    global_discounts: PurchaseOrderDiscountType[],
    product_units: PurchaseOrderProductUnitType[],
    remarks: string,
    status: string,
}