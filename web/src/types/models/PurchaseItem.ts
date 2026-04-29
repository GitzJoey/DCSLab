import type { Branch } from './Branch';
import type { Company } from './Company';
import type { ProductUnit } from './ProductUnit';
import type { Purchase } from './Purchase';
import type { VatProfile } from './VatProfile';

export interface PurchaseItemProductUnitPriceDiscount {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  purchase_item?: PurchaseItem;
  sequence: number;
  discount_type?: string | null;
  discount_value?: string | null;
}

export interface PurchaseItemSubtotalDiscount {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  purchase_item?: PurchaseItem;
  sequence: number;
  discount_type?: string | null;
  discount_value?: string | null;
}

export interface PurchaseItem {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  purchase?: Purchase;
  product_unit?: ProductUnit | null;
  vat_profile?: VatProfile | null;
  product_unit_price_discounts?: PurchaseItemProductUnitPriceDiscount[];
  subtotal_discounts?: PurchaseItemSubtotalDiscount[];
  qty?: string | null;
  product_unit_conversion_value?: string | null;
  product_unit_qty_base?: string | null;
  qty_received_base?: string | null;
  qty_outstanding_base?: string | null;
  qty_excess_base?: string | null;
  product_unit_price?: string | null;
  product_unit_is_price_include_vat: boolean;
  price_discount?: string | null;
  price_after_discount?: string | null;
  subtotal?: string | null;
  subtotal_discount?: string | null;
  subtotal_after_discount?: string | null;
  global_discount?: string | null;
  subtotal_after_global_discount?: string | null;
  vat_rate?: string | null;
  vat_base_numerator?: number | null;
  vat_base_denominator?: number | null;
  vat_base?: string | null;
  vat?: string | null;
  subtotal_after_vat?: string | null;
  additional_cost?: string | null;
  rounding?: string | null;
  amount_payable?: string | null;
  cogs?: string | null;
  total_cogs?: string | null;
  base_unit_cogs?: string | null;
  remarks?: string | null;
}
