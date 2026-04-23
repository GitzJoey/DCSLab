import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { Debt } from './Debt';

export interface DebtPayment {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  debt?: Debt | null;
  cash_account?: CashAccount | null;
  code: string;
  date: string;
  amount: number;
  remarks: string | null;
}
