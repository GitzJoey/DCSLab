import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { SalesOrder } from './SalesOrder';

export interface SalesOrderPayment {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  sales_order?: SalesOrder;
  code: string;
  date: string;
  cash_account?: CashAccount | null;
  amount: number;
  amount_allocated: number;
  remarks: string | null;
}
