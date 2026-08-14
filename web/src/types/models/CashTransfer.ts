import { Branch } from './Branch';
import { CashAccount } from './CashAccount';
import { Company } from './Company';

export interface CashTransfer {
  id: string;
  ulid: string;
  company: Company;
  branch: Branch;
  code: string;
  date: string;
  source_cash_account: CashAccount;
  destination_cash_account: CashAccount;
  amount: number;
  remarks: string | null;
}

