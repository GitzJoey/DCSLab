import type { Branch } from './Branch';
import type { Company } from './Company';
import type { Customer } from './Customer';
import type { SalesInvoice } from './SalesInvoice';
import type { SalesReturnItem } from './SalesReturnItem';
import type { SalesReturnRefund } from './SalesReturnRefund';
import type { Warehouse } from './Warehouse';

export interface SalesReturn {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  code: string;
  date: string;
  customer?: Customer | null;
  sales_invoice?: SalesInvoice | null;
  warehouse?: Warehouse | null;
  remarks: string | null;
  is_posted: boolean;
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
  items?: SalesReturnItem[];
  refunds?: SalesReturnRefund[];
}
