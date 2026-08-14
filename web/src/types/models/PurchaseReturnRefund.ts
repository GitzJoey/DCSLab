import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { PurchaseReturn } from './PurchaseReturn';

export interface PurchaseReturnRefund {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  purchase_return?: PurchaseReturn;
  code: string;
  date: string;
  cash_account?: CashAccount | null;
  amount: number;
  remarks: string | null;
}
