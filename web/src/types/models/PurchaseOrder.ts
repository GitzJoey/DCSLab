import { Branch } from './Branch';
import { Company } from './Company';
import type { PurchaseOrderDownPayment } from './PurchaseOrderDownPayment';
import type { PurchaseOrderDownPaymentRefund } from './PurchaseOrderDownPaymentRefund';
import type { PurchaseOrderItem } from './PurchaseOrderItem';
import { Supplier } from './Supplier';

export interface PurchaseOrderGlobalDiscount {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  sequence: number;
  discount_type: string;
  discount_value: number;
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
  item_total_after_vat: number;
  rounding: number;
  amount_payable: number;
  amount_paid_down_payment: number;
  amount_allocated_down_payment: number;
  amount_refunded_down_payment: number;
  amount_available_down_payment: number;
  global_discounts: PurchaseOrderGlobalDiscount[];
  items: PurchaseOrderItem[];
  down_payments: PurchaseOrderDownPayment[];
  refunded_down_payments: PurchaseOrderDownPaymentRefund[];
}
