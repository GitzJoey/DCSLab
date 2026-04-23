import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { LiabilityCategory } from './LiabilityCategory';
import type { LiabilityCreditor } from './LiabilityCreditor';
import type { LiabilityPayment } from './LiabilityPayment';
import type { Supplier } from './Supplier';

export interface Liability {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  code: string;
  date: string;
  category?: LiabilityCategory | null;
  creditor?: LiabilityCreditor | null;
  supplier?: Supplier | null;
  cash_account?: CashAccount | null;
  amount_received: number;
  amount_payable: number;
  amount_total: number;
  due_days: number;
  amount_paid_by_cash_account: number;
  amount_paid_by_stock_adjustment: number;
  amount_due: number;
  is_paid_off: boolean;
  remarks: string | null;
  payments?: LiabilityPayment[];
}
