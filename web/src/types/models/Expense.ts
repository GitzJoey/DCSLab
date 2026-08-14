import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { ExpenseCategory } from './ExpenseCategory';
import type { ExpenseImage } from './ExpenseImage';
import type { ExpensePayment } from './ExpensePayment';

export interface Expense {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  code: string;
  date: string;
  category?: ExpenseCategory | null;
  paid_immediately_cash_account?: CashAccount | null;
  amount_paid_immediately: number;
  amount_payable: number;
  due_days: number;
  amount_payable_paid: number;
  amount_payable_due: number;
  is_amount_payable_paid_off: boolean;
  amount_total: number;
  remarks: string | null;
  expense_images?: ExpenseImage[];
  main_expense_image?: ExpenseImage | null;
  payments?: ExpensePayment[];
}
