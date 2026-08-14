import type { Branch } from './Branch';
import type { Company } from './Company';
import type { Product } from './Product';
import type { ProductUnit } from './ProductUnit';
import type { PurchaseOrderItem } from './PurchaseOrderItem';
import type { PurchaseOrderReceipt } from './PurchaseOrderReceipt';
import type { PurchaseOrderReceiptItemSerial } from './PurchaseOrderReceiptItemSerial';

export interface PurchaseOrderReceiptItem {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  purchase_order_receipt?: PurchaseOrderReceipt;
  purchase_order_item?: PurchaseOrderItem | null;
  has_purchase_order_item_product: boolean;
  qty?: string | number | null;
  product_unit?: ProductUnit | null;
  product?: Product | null;
  product_unit_conversion_value?: string | number | null;
  product_unit_qty_base?: string | number | null;
  base_unit_value?: string | number | null;
  total_value?: string | number | null;
  remarks?: string | null;
  serials?: PurchaseOrderReceiptItemSerial[];
}
