import type { Branch } from './Branch';
import type { Company } from './Company';
import type { Purchase } from './Purchase';

export interface PurchaseGlobalDiscount {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  purchase?: Purchase;
  sequence: number;
  discount_type?: string | null;
  discount_value?: string | null;
}
