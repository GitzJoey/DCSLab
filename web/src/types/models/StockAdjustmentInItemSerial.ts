import { Company } from './Company';
import { Branch } from './Branch';
import { StockAdjustment } from './StockAdjustment';
import { StockAdjustmentInItem } from './StockAdjustmentInItem';

export interface StockAdjustmentInItemSerial {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  stock_adjustment?: StockAdjustment;
  stock_adjustment_in_item?: StockAdjustmentInItem;
  serial: string;
}
