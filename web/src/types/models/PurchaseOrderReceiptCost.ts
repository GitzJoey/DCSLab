import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { PurchaseOrderReceipt } from './PurchaseOrderReceipt';

export interface PurchaseOrderReceiptCost {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  purchase_order_receipt?: PurchaseOrderReceipt;
  code: string;
  date: string;
  name: string;
  cash_account?: CashAccount | null;
  amount: number;
  remarks?: string | null;
}
