import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { PrepaidIncome } from './PrepaidIncome';

export interface PrepaidIncomePayment {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  prepaid_income?: PrepaidIncome | null;
  cash_account?: CashAccount | null;
  code: string;
  date: string;
  amount: number;
  remarks: string | null;
}
