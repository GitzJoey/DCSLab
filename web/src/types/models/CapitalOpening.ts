import { Branch } from './Branch';
import { CashAccount } from './CashAccount';
import { Company } from './Company';
import { Investor } from './Investor';

export interface CapitalOpening {
  id: string;
  ulid: string;
  company: Company;
  branch: Branch;
  code: string;
  date: string;
  investor: Investor;
  cash_account: CashAccount;
  amount: number;
  remarks: string | null;
}
