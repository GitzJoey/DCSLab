import { Branch } from './Branch';
import { Company } from './Company';
import type { PurchaseOrderItem } from './PurchaseOrderItem';
import type { PurchaseOrderPayment } from './PurchaseOrderPayment';
import type { PurchaseOrderPaymentRefund } from './PurchaseOrderPaymentRefund';
import { Supplier } from './Supplier';

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
  progress_status: string;
  item_total_count: number;
  item_matched_count: number;
  item_less_count: number;
  item_more_count: number;
  item_unlinked_count: number;
  items: PurchaseOrderItem[];
  payments: PurchaseOrderPayment[];
  refunded_payments: PurchaseOrderPaymentRefund[];
}
