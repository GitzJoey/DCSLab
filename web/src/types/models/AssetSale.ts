import type { Branch } from './Branch';
import type { Company } from './Company';
import type { Customer } from './Customer';
import type { AssetSaleItem } from './AssetSaleItem';

export interface AssetSale {
  id: string;
  ulid: string;
  company: Company;
  branch: Branch;
  customer: Customer;
  code: string;
  date: string;
  due_days: number;
  remarks: string | null;
  is_posted: boolean;
  item_total: number;
  rounding: number;
  amount_receivable: number;
  items: AssetSaleItem[];
}
