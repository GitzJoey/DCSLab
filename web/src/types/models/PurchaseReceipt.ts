import type { Branch } from './Branch';
import type { Company } from './Company';
import type { Purchase } from './Purchase';
import type { PurchaseReceiptItem } from './PurchaseReceiptItem';
import type { Supplier } from './Supplier';
import type { Warehouse } from './Warehouse';

export interface PurchaseReceipt {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  supplier?: Supplier | null;
  purchase?: Purchase | null;
  code: string;
  date: string;
  is_linked_to_purchase: boolean;
  is_from_direct_purchase: boolean;
  warehouse?: Warehouse | null;
  remarks?: string | null;
  is_posted: boolean;
  items?: PurchaseReceiptItem[];
}
