import type { Branch } from './Branch';
import type { Company } from './Company';
import type { PurchaseReceipt } from './PurchaseReceipt';
import type { PurchaseReceiptItem } from './PurchaseReceiptItem';

export interface PurchaseReceiptItemSerial {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  purchase_receipt?: PurchaseReceipt;
  purchase_receipt_item?: PurchaseReceiptItem;
  serial: string;
}
