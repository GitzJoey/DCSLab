import { Branch } from './Branch';
import { CashAccount } from './CashAccount';
import { Company } from './Company';
import { ProductUnit } from './ProductUnit';
import { Supplier } from './Supplier';
import { VatProfile } from './VatProfile';

export interface PurchaseOrderGlobalDiscount {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  sequence: number;
  discount_type: string;
  discount_value: number;
}

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
  rounding: number;
  grand_total: number;
  cogs: number;
  total_cogs: number;
  base_unit_cogs: number;
  remarks: string | null;
}

export interface PurchaseOrderDownPayment {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  code: string;
  date: string;
  cash_account?: CashAccount | null;
  amount: number;
  remarks: string | null;
}

export interface PurchaseOrderDownPaymentRefund {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  code: string;
  date: string;
  cash_account?: CashAccount | null;
  amount: number;
  remarks: string | null;
}

export interface PurchaseOrder {
  id: string;
  ulid: string;
  company: Company;
  branch: Branch;
  code: string;
  date: string;
  due_days: number;
  supplier?: Supplier | null;
  remarks: string | null;
  item_total_before_global_discount: number;
  global_discount: number;
  item_total_after_global_discount: number;
  vat_base: number;
  vat: number;
  rounding: number;
  grand_total: number;
  amount_paid_down_payment: number;
  amount_allocated_down_payment: number;
  amount_refunded_down_payment: number;
  amount_available_down_payment: number;
  global_discounts: PurchaseOrderGlobalDiscount[];
  items: PurchaseOrderItem[];
  down_payments: PurchaseOrderDownPayment[];
  refunded_down_payments: PurchaseOrderDownPaymentRefund[];
}
