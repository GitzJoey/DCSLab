import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { SalesOrderDelivery } from './SalesOrderDelivery';

export interface SalesOrderDeliveryCost {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  sales_order_delivery?: SalesOrderDelivery;
  code: string;
  date: string;
  name: string;
  cash_account?: CashAccount | null;
  amount: number;
  remarks?: string | null;
}
