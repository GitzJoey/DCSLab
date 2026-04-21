import type { Branch } from './Branch';
import type { Company } from './Company';
import type { PurchaseReceipt } from './PurchaseReceipt';
import type { Supplier } from './Supplier';

export interface Purchase {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  code: string;
  date: string;
  due_days: number;
  supplier?: Supplier | null;
  remarks?: string | null;
  additional_cost?: number;
  amount_payable?: number;
  amount_due?: number;
  is_paid_off?: boolean;
  receipts?: PurchaseReceipt[];
}
