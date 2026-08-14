import type { Branch } from './Branch';
import type { Company } from './Company';
import type { Supplier } from './Supplier';
import type { AssetPurchaseItem } from './AssetPurchaseItem';

export interface AssetPurchase {
  id: string;
  ulid: string;
  company: Company;
  branch: Branch;
  supplier: Supplier;
  code: string;
  date: string;
  due_days: number;
  remarks: string | null;
  is_posted: boolean;
  item_total: number;
  additional_cost: number;
  rounding: number;
  amount_payable: number;
  items: AssetPurchaseItem[];
}
