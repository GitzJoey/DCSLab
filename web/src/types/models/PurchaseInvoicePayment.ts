import type { PaymentType } from '@/types/enums/PaymentType';
import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { PurchaseInvoice } from './PurchaseInvoice';
import type { PurchaseOrderPayment } from './PurchaseOrderPayment';
import type { PurchaseReturn } from './PurchaseReturn';

/**
 * Alias of the shared `PaymentType` union, re-exported so the existing
 * purchase-invoice imports keep working.
 */
export type PurchaseInvoicePaymentType = PaymentType;

export interface PurchaseInvoicePayment {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  purchase_invoice?: PurchaseInvoice;
  code: string;
  date: string;
  payment_type: PurchaseInvoicePaymentType | null;
  cash_account?: CashAccount | null;
  purchase_order_payment?: PurchaseOrderPayment | null;
  purchase_return?: PurchaseReturn | null;
  amount: number;
  remarks: string | null;
}
