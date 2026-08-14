import type { Branch } from './Branch';
import type { Company } from './Company';
import type { ProductUnit } from './ProductUnit';
import type { SalesInvoice } from './SalesInvoice';
import type { SalesOrderItem } from './SalesOrderItem';
import type { VatProfile } from './VatProfile';

export interface SalesInvoiceItem {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  sales_invoice?: SalesInvoice;
  sales_order_item?: SalesOrderItem | null;
  qty: number;
  product_unit?: ProductUnit;
  product_unit_conversion_value: number;
  product_unit_qty_base: number;
  product_unit_price: number;
  product_unit_is_price_include_vat: boolean;
  price_discount: number;
  price_after_discount: number;
  subtotal: number;
  subtotal_discount: number;
  subtotal_after_discount: number;
  global_discount: number;
  subtotal_after_global_discount: number;
  vat_profile?: VatProfile | null;
  vat_rate: number;
  vat_base_numerator: number;
  vat_base_denominator: number;
  vat_base: number;
  vat: number;
  subtotal_after_vat: number;
  rounding: number;
  amount_payable: number;
  remarks: string | null;
}
