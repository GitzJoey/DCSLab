import { Company } from './Company';
import { Branch } from './Branch';
import { StockTransfer } from './StockTransfer';
import { ProductUnit } from './ProductUnit';
import { StockTransferItemSerial } from './StockTransferItemSerial';

export interface StockTransferItem {
  id: string;
  ulid: string;
  company: Company;
  branch: Branch;
  stock_transfer: StockTransfer;
  qty: number;
  product_unit: ProductUnit;
  product_unit_conversion_value: number;
  product_unit_qty_base: number;
  remarks: string | null;
  serials: StockTransferItemSerial[];
}
