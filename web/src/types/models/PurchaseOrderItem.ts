import type { Branch } from './Branch';
import type { Company } from './Company';
import type { ProductUnit } from './ProductUnit';
import type { PurchaseOrder } from './PurchaseOrder';
import type { VatProfile } from './VatProfile';

export interface PurchaseOrderItemPriceDiscount {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  sequence: number;
  discount_type: string;
  discount_value: number;
}

export interface PurchaseOrderItemSubtotalDiscount {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  sequence: number;
  discount_type: string;
  discount_value: number;
}

export interface PurchaseOrderItem {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  purchase_order?: PurchaseOrder;
  qty: number;
  product_unit: ProductUnit;
  product_unit_conversion_value: number;
  product_unit_qty_base: number;
  product_unit_price: number;
  price_discount: number;
  product_unit_price_discounts: PurchaseOrderItemPriceDiscount[];
  price_after_discount: number;
  subtotal: number;
  subtotal_discount: number;
  subtotal_discounts: PurchaseOrderItemSubtotalDiscount[];
  subtotal_after_discount: number;
  global_discount: number;
  subtotal_after_global_discount: number;
  product_unit_is_price_include_vat: boolean;
  vat_profile?: VatProfile | null;
  vat_rate: number;
  vat_base_numerator: number;
  vat_base_denominator: number;
  vat_base: number;
  vat: number;
  subtotal_after_vat: number;
  rounding: number;
  amount_payable: number;
  cogs: number;
  total_cogs: number;
  base_unit_cogs: number;
  remarks: string | null;
}
