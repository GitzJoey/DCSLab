import type { Branch } from './Branch';
import type { Company } from './Company';
import type { ProductUnit } from './ProductUnit';
import type { PurchaseReceipt } from './PurchaseReceipt';
import type { PurchaseReceiptItemSerial } from './PurchaseReceiptItemSerial';

export interface PurchaseReceiptItem {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  purchase_receipt?: PurchaseReceipt;
  has_purchase_item_product: boolean;
  qty?: string | null;
  product_unit?: ProductUnit | null;
  product_unit_conversion_value?: string | null;
  product_unit_qty_base?: string | null;
  remarks?: string | null;
  serials?: PurchaseReceiptItemSerial[];
}
