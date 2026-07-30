import type { Branch } from './Branch';
import type { Company } from './Company';
import type { SalesOrderDelivery } from './SalesOrderDelivery';
import type { SalesOrderDeliveryItem } from './SalesOrderDeliveryItem';

export interface SalesOrderDeliveryItemSerial {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  sales_order_delivery?: SalesOrderDelivery;
  sales_order_delivery_item?: SalesOrderDeliveryItem;
  serial: string;
}
