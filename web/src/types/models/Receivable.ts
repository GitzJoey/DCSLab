import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { Customer } from './Customer';
import type { ReceivableCategory } from './ReceivableCategory';
import type { ReceivablePayment } from './ReceivablePayment';

export interface Receivable {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  code: string;
  date: string;
  category?: ReceivableCategory | null;
  customer?: Customer | null;
  cash_account?: CashAccount | null;
  direct_amount_received: number;
  opening_amount_due: number;
  amount_total: number;
  due_days: number;
  amount_paid_by_cash_account: number;
  amount_paid_by_stock_adjustment: number;
  amount_due: number;
  is_paid_off: boolean;
  remarks: string | null;
  payments?: ReceivablePayment[];
}

