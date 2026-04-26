import type { Branch } from './Branch';
import type { Company } from './Company';
import type { ProductUnit } from './ProductUnit';
import type { PurchaseItem } from './PurchaseItem';
import type { PurchaseReceipt } from './PurchaseReceipt';
import type { PurchaseReceiptItemSerial } from './PurchaseReceiptItemSerial';

export interface PurchaseReceiptItem {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  purchase_receipt?: PurchaseReceipt;
  purchase_item?: PurchaseItem | null;
  product_unit?: ProductUnit | null;
  qty?: string | null;
  product_unit_conversion_value?: string | null;
  product_unit_qty_base?: string | null;
  remarks?: string | null;
  serials?: PurchaseReceiptItemSerial[];
}
