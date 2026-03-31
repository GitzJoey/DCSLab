import { Company } from './Company';
import { Branch } from './Branch';
import { Warehouse } from './Warehouse';
import { StockTransferProductUnit } from './StockTransferProductUnit';

export interface StockTransfer {
  id: string;
  ulid: string;
  company: Company;
  branch: Branch;
  code: string;
  date: string;
  source_warehouse: Warehouse | null;
  destination_warehouse: Warehouse | null;
  remarks: string | null;
  is_posted: boolean;
  product_units: StockTransferProductUnit[];
}
