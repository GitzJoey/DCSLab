import type { Branch } from './Branch';
import type { Company } from './Company';
import type { PurchaseOrderReceipt } from './PurchaseOrderReceipt';
import type { PurchaseOrderReceiptItem } from './PurchaseOrderReceiptItem';

export interface PurchaseOrderReceiptItemSerial {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  purchase_order_receipt?: PurchaseOrderReceipt;
  purchase_order_receipt_item?: PurchaseOrderReceiptItem;
  serial: string;
}
