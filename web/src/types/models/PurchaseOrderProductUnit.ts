import { Company } from "./Company"
import { Branch } from "./Branch"
import { PurchaseOrder } from "./PurchaseOrder"
import { ProductUnit } from "./ProductUnit"
import { Product } from "./Product"
import { PurchaseOrderProductUnitPerUnitDiscount } from "./PurchaseOrderProductUnitPerUnitDiscount"
import { PurchaseOrderProductUnitPerUnitSubTotalDiscount } from "./PurchaseOrderProductUnitPerUnitSubTotalDiscount"

export interface PurchaseOrderProductUnit {
    id: string,
    ulid: string,
    company: Company,
    branch: Branch,
    purchase_order: PurchaseOrder,
    product_unit: ProductUnit,
    product: Product,
    qty: number,
    product_unit_amount_per_unit: number,
    product_unit_amount_total: number,
    product_unit_initial_price: number,
    product_unit_per_unit_discount: Array<PurchaseOrderProductUnitPerUnitDiscount>,
    product_unit_sub_total: number,
    product_unit_per_unit_sub_total_discount: Array<PurchaseOrderProductUnitPerUnitSubTotalDiscount>,
    product_unit_total: number,
    product_unit_global_discount_: number,
    product_unit_final_price: number,
    vat_status: string,
    vat_rate: number,
    vat_amount: number,
    remarks: string,
}