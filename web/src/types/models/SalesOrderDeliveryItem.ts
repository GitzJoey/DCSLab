import type { Branch } from './Branch';
import type { Company } from './Company';
import type { ProductUnit } from './ProductUnit';
import type { SalesOrderDelivery } from './SalesOrderDelivery';
import type { SalesOrderDeliveryItemSerial } from './SalesOrderDeliveryItemSerial';
import type { SalesOrderItem } from './SalesOrderItem';

export interface SalesOrderDeliveryItem {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  sales_order_delivery?: SalesOrderDelivery;
  sales_order_item?: SalesOrderItem | null;
  has_sales_order_item_product: boolean;
  qty?: string | null;
  product_unit?: ProductUnit | null;
  product_unit_conversion_value?: string | null;
  product_unit_qty_base?: string | null;
  base_unit_cogs?: string | null;
  total_cogs?: string | null;
  remarks?: string | null;
  serials?: SalesOrderDeliveryItemSerial[];
}
