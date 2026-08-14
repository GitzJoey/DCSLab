import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { IncomeCategory } from './IncomeCategory';
import type { IncomeImage } from './IncomeImage';
import type { IncomePayment } from './IncomePayment';

export interface Income {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  code: string;
  date: string;
  category?: IncomeCategory | null;
  paid_immediately_cash_account?: CashAccount | null;
  amount_paid_immediately: number;
  amount_receivable: number;
  due_days: number;
  amount_receivable_paid: number;
  amount_receivable_due: number;
  is_amount_receivable_paid_off: boolean;
  amount_total: number;
  remarks: string | null;
  income_images?: IncomeImage[];
  main_income_image?: IncomeImage | null;
  payments?: IncomePayment[];
}
