import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { Purchase } from './Purchase';
import type { PurchaseAdditionalCostCategory } from './PurchaseAdditionalCostCategory';
import type { PurchaseAdditionalCostPayment } from './PurchaseAdditionalCostPayment';

export interface PurchaseAdditionalCost {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  purchase?: Purchase;
  code: string;
  date: string;
  due_days: number;
  category?: PurchaseAdditionalCostCategory;
  paid_immediately_cash_account?: CashAccount | null;
  amount_paid_immediately: number;
  amount_payable: number;
  amount_payable_paid: number;
  amount_payable_due: number;
  is_amount_payable_paid_off: boolean;
  amount_total: number;
  remarks: string | null;
  payments?: PurchaseAdditionalCostPayment[];
}
