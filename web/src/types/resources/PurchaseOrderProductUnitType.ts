import { CompanyType } from "./CompanyType"
import { BranchType } from "./BranchType"
import { PurchaseOrderType } from "./PurchaseOrderType"
import { ProductUnitType } from "./ProductUnitType"
import { ProductType } from "./ProductType"
import { PurchaseOrderProductUnitPerUnitDiscountType } from "./PurchaseOrderProductUnitPerUnitDiscountType"
import { PurchaseOrderProductUnitPerUnitSubTotalDiscountType } from "./PurchaseOrderProductUnitPerUnitSubTotalDiscountType"

export interface PurchaseOrderProductUnitType {
    id: string,
    ulid: string,
    company: CompanyType,
    branch: BranchType,
    purchase_order: PurchaseOrderType,
    product_unit: ProductUnitType,
    product: ProductType,
    qty: number,
    product_unit_amount_per_unit: number,
    product_unit_amount_total: number,
    product_unit_initial_price: number,
    product_unit_per_unit_discount: PurchaseOrderProductUnitPerUnitDiscountType[],
    product_unit_sub_total: number,
    product_unit_per_unit_sub_total_discount: PurchaseOrderProductUnitPerUnitSubTotalDiscountType[],
    product_unit_total: number,
    product_unit_global_discount_: number,
    product_unit_final_price: number,
    vat_status: string,
    vat_rate: number,
    vat_amount: number,
    remarks: string,
}