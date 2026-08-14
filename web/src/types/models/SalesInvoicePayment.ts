import type { PaymentType } from '@/types/enums/PaymentType';
import type { Branch } from './Branch';
import type { CashAccount } from './CashAccount';
import type { Company } from './Company';
import type { SalesInvoice } from './SalesInvoice';
import type { SalesOrderPayment } from './SalesOrderPayment';
import type { SalesReturn } from './SalesReturn';

export interface SalesInvoicePayment {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  sales_invoice?: SalesInvoice;
  code: string;
  date: string;
  payment_type: PaymentType | null;
  cash_account?: CashAccount | null;
  sales_order_payment?: SalesOrderPayment | null;
  sales_return?: SalesReturn | null;
  amount: number;
  remarks: string | null;
}
