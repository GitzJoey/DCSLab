import type { Branch } from './Branch';
import type { Company } from './Company';
import type { PurchaseOrder } from './PurchaseOrder';
import type { PurchaseOrderReceiptCost } from './PurchaseOrderReceiptCost';
import type { PurchaseOrderReceiptItem } from './PurchaseOrderReceiptItem';
import type { Supplier } from './Supplier';
import type { Warehouse } from './Warehouse';

export interface PurchaseOrderReceipt {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  code: string;
  date: string;
  supplier?: Supplier | null;
  purchase_order?: PurchaseOrder | null;
  warehouse?: Warehouse | null;
  remarks?: string | null;
  is_posted: boolean;
  total_value: number;
  total_cost: number;
  items?: PurchaseOrderReceiptItem[];
  costs?: PurchaseOrderReceiptCost[];
}
