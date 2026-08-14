import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { Expense } from './Expense';

export interface ExpensePayment {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  expense?: Expense | null;
  cash_account?: CashAccount | null;
  code: string;
  date: string;
  amount: number;
  remarks: string | null;
}
