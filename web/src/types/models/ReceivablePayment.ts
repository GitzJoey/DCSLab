import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { Receivable } from './Receivable';

export interface ReceivablePayment {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  receivable?: Receivable | null;
  cash_account?: CashAccount | null;
  code: string;
  date: string;
  amount: number;
  remarks: string | null;
}

