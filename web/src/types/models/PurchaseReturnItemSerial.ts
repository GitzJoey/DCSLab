import type { Branch } from './Branch';
import type { Company } from './Company';
import type { PurchaseReturn } from './PurchaseReturn';
import type { PurchaseReturnItem } from './PurchaseReturnItem';

export interface PurchaseReturnItemSerial {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  purchase_return?: PurchaseReturn;
  purchase_return_item?: PurchaseReturnItem;
  serial: string;
}
