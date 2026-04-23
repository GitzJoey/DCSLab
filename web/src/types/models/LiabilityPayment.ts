import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { Liability } from './Liability';

export interface LiabilityPayment {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  liability?: Liability | null;
  cash_account?: CashAccount | null;
  code: string;
  date: string;
  amount: number;
  remarks: string | null;
}
