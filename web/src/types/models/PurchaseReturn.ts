import type { Branch } from './Branch';
import type { Company } from './Company';
import type { PurchaseInvoice } from './PurchaseInvoice';
import type { PurchaseReturnItem } from './PurchaseReturnItem';
import type { PurchaseReturnRefund } from './PurchaseReturnRefund';
import type { Supplier } from './Supplier';
import type { Warehouse } from './Warehouse';

export interface PurchaseReturn {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  code: string;
  date: string;
  supplier?: Supplier | null;
  purchase_invoice?: PurchaseInvoice | null;
  warehouse?: Warehouse | null;
  item_total_before_global_discount: number;
  global_discount: number;
  item_total_after_global_discount: number;
  vat_base: number;
  vat: number;
  item_total_after_vat: number;
  rounding: number;
  amount_payable: number;
  amount_allocated_to_invoice: number;
  amount_received_total: number;
  amount_settled_total: number;
  amount_available: number;
  is_settled: boolean;
  remarks: string | null;
  is_posted: boolean;
  items?: PurchaseReturnItem[];
  refunds?: PurchaseReturnRefund[];
}
