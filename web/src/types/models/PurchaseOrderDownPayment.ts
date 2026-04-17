import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { PurchaseOrder } from './PurchaseOrder';

export interface PurchaseOrderDownPayment {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  purchase_order?: PurchaseOrder;
  code: string;
  date: string;
  cash_account?: CashAccount | null;
  amount: number;
  amount_allocated: number;
  remarks: string | null;
}
