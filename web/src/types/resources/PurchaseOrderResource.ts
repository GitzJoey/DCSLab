import { CompanyResource } from "./CompanyResource"
import { BranchResource } from "./BranchResource"
import { SupplierResource } from "./SupplierResource"
import { PurchaseOrderDiscountResource } from "./PurchaseOrderDiscountResource"
import { PurchaseOrderProductUnitResource } from "./PurchaseOrderProductUnitResource"

export interface PurchaseOrderResource {
    id: string,
    ulid: string,
    company: CompanyResource[],
    branch: BranchResource[],
    invoice_code: string,
    invoice_date: string,
    shipping_date: string,
    shipping_address: string,
    supplier: SupplierResource[],
    global_discounts: PurchaseOrderDiscountResource[],
    product_units: PurchaseOrderProductUnitResource[],
    remarks: string,
    status: string,
}