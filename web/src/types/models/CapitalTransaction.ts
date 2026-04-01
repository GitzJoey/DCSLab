import { Branch } from './Branch';
import { CashAccount } from './CashAccount';
import { Company } from './Company';
import { Investor } from './Investor';
import { CapitalTransactionType } from '../enums/CapitalTransactionType';

export interface CapitalTransaction {
  id: string;
  ulid: string;
  company: Company;
  branch: Branch;
  code: string;
  date: string;
  investor: Investor;
  cash_account: CashAccount;
  type: CapitalTransactionType;
  amount: number;
  remarks: string | null;
}
