import type { Branch } from './Branch';
import type { Company } from './Company';
import type { Customer } from './Customer';
import type { SalesOrder } from './SalesOrder';
import type { SalesOrderDeliveryCost } from './SalesOrderDeliveryCost';
import type { SalesOrderDeliveryItem } from './SalesOrderDeliveryItem';
import type { Warehouse } from './Warehouse';

export interface SalesOrderDelivery {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  customer?: Customer | null;
  sales_order?: SalesOrder | null;
  code: string;
  date: string;
  warehouse?: Warehouse | null;
  remarks?: string | null;
  is_posted: boolean;
  total_cogs: number;
  total_cost: number;
  items?: SalesOrderDeliveryItem[];
  costs?: SalesOrderDeliveryCost[];
}
