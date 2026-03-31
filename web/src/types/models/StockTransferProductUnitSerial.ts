import { Company } from './Company';
import { Branch } from './Branch';
import { StockTransfer } from './StockTransfer';
import { StockTransferProductUnit } from './StockTransferProductUnit';

export interface StockTransferProductUnitSerial {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  stock_transfer?: StockTransfer;
  stock_transfer_product_unit?: StockTransferProductUnit;
  serial: string;
}
