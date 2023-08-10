import { CompanyResource } from "./CompanyResource"
import { BranchResource } from "./BranchResource"
import { PurchaseOrderResource } from "./PurchaseOrderResource"
import { ProductUnitResource } from "./ProductUnitResource"
import { ProductResource } from "./ProductResource"
import { PurchaseOrderProductUnitPerUnitDiscountResource } from "./PurchaseOrderProductUnitPerUnitDiscountResource"
import { PurchaseOrderProductUnitPerUnitSubTotalDiscountResource } from "./PurchaseOrderProductUnitPerUnitSubTotalDiscountResource"

export interface PurchaseOrderProductUnitResource {
    id: string,
    ulid: string,
    company: CompanyResource[],
    branch: BranchResource[],
    purchase_order: PurchaseOrderResource[],
    product_unit: ProductUnitResource[],
    product: ProductResource[],
    qty: number,
    product_unit_amount_per_unit: number,
    product_unit_amount_total: number,
    product_unit_initial_price: number,
    product_unit_per_unit_discount: PurchaseOrderProductUnitPerUnitDiscountResource[],
    product_unit_sub_total: number,
    product_unit_per_unit_sub_total_discount: PurchaseOrderProductUnitPerUnitSubTotalDiscountResource[],
    product_unit_total: number,
    product_unit_global_discount_: number,
    product_unit_final_price: number,
    vat_status: string,
    vat_rate: number,
    vat_amount: number,
    remarks: string,
}