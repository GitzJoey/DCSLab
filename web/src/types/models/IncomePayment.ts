import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { Income } from './Income';

export interface IncomePayment {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  income?: Income | null;
  cash_account?: CashAccount | null;
  code: string;
  date: string;
  amount: number;
  remarks: string | null;
}
