import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { PrepaidExpense } from './PrepaidExpense';

export interface PrepaidExpensePayment {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  prepaid_expense?: PrepaidExpense | null;
  cash_account?: CashAccount | null;
  code: string;
  date: string;
  amount: number;
  remarks: string | null;
}
