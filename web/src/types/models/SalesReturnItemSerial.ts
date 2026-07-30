import type { Branch } from './Branch';
import type { Company } from './Company';
import type { SalesReturn } from './SalesReturn';
import type { SalesReturnItem } from './SalesReturnItem';

export interface SalesReturnItemSerial {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  sales_return?: SalesReturn;
  sales_return_item?: SalesReturnItem;
  serial: string;
}
