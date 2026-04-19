import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { PurchaseAdditionalCost } from './PurchaseAdditionalCost';

export interface PurchaseAdditionalCostPayment {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  purchase_additional_cost?: PurchaseAdditionalCost;
  cash_account?: CashAccount | null;
  code: string;
  date: string;
  amount: number;
  remarks: string | null;
}
