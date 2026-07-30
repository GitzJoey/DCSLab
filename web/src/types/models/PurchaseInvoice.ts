import type { Branch } from './Branch';
import type { Company } from './Company';
import type { PurchaseInvoiceItem } from './PurchaseInvoiceItem';
import type { PurchaseInvoicePayment } from './PurchaseInvoicePayment';
import type { PurchaseOrder } from './PurchaseOrder';
import type { Supplier } from './Supplier';

export interface PurchaseInvoice {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  code: string;
  date: string;
  due_days: number;
  supplier?: Supplier | null;
  purchase_order?: PurchaseOrder | null;
  tax_invoice_number: string | null;
  tax_invoice_vat_base: number;
  tax_invoice_vat: number;
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
  amount_paid_down_payment: number;
  amount_paid_return: number;
  amount_paid_total: number;
  amount_due: number;
  is_paid_off: boolean;
  items?: PurchaseInvoiceItem[];
  payments?: PurchaseInvoicePayment[];
}
