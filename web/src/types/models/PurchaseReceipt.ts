import type { Branch } from './Branch';
import type { Company } from './Company';
import type { Warehouse } from './Warehouse';

export interface PurchaseReceipt {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  code: string;
  date: string;
  warehouse?: Warehouse | null;
  remarks?: string | null;
  is_posted?: boolean;
}
