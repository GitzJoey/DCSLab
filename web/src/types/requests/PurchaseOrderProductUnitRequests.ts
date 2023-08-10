import { CompanyRequest } from "./CompanyRequests"
import { BranchRequest } from "./BranchFormRequest"
import { PurchaseOrderRequest } from "./PurchaseOrderRequests"
import { ProductUnitRequest } from "./ProductUnitRequests"
import { ProductRequest } from "./ProductRequests"
import { PurchaseOrderProductUnitPerUnitDiscountRequest } from "./PurchaseOrderProductUnitPerUnitDiscountRequests"
import { PurchaseOrderProductUnitPerUnitSubTotalDiscountRequest } from "./PurchaseOrderProductUnitPerUnitSubTotalDiscountRequests"

export interface PurchaseOrderProductUnitRequest {
    id: string,
    ulid: string,
    company: CompanyRequest,
    branch: BranchRequest,
    purchase_order: PurchaseOrderRequest,
    product_unit: ProductUnitRequest,
    product: ProductRequest,
    qty: number,
    product_unit_amount_per_unit: number,
    product_unit_amount_total: number,
    product_unit_initial_price: number,
    product_unit_per_unit_discount: PurchaseOrderProductUnitPerUnitDiscountRequest[],
    product_unit_sub_total: number,
    product_unit_per_unit_sub_total_discount: PurchaseOrderProductUnitPerUnitSubTotalDiscountRequest[],
    product_unit_total: number,
    product_unit_global_discount_: number,
    product_unit_final_price: number,
    vat_status: string,
    vat_rate: number,
    vat_amount: number,
    remarks: string,
}