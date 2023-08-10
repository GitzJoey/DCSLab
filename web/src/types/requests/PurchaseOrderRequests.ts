import { CompanyRequest } from "./CompanyRequests"
import { BranchRequest } from "./BranchFormRequest"
import { SupplierRequest } from "./SupplierRequests"
import { PurchaseOrderDiscountRequest } from "./PurchaseOrderDiscountRequests"
import { PurchaseOrderProductUnitRequest } from "./PurchaseOrderProductUnitRequests"

export interface PurchaseOrderRequest {
    id: string,
    ulid: string,
    company: CompanyRequest,
    branch: BranchRequest,
    invoice_code: string,
    invoice_date: string,
    shipping_date: string,
    shipping_address: string,
    supplier: SupplierRequest,
    global_discounts: PurchaseOrderDiscountRequest[],
    product_units: PurchaseOrderProductUnitRequest[],
    remarks: string,
    status: string,
}