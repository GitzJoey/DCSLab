import { Company } from './Company';
import { Branch } from './Branch';
import { StockTransfer } from './StockTransfer';
import { StockTransferItem } from './StockTransferItem';

export interface StockTransferItemSerial {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  stock_transfer?: StockTransfer;
  stock_transfer_item?: StockTransferItem;
  serial: string;
}
