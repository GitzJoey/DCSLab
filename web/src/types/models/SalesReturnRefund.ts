import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { SalesReturn } from './SalesReturn';

export interface SalesReturnRefund {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  code: string;
  date: string;
  sales_return?: SalesReturn;
  cash_account?: CashAccount | null;
  amount: number;
  remarks: string | null;
}
