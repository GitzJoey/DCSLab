import { Company } from './Company';
import { Branch } from './Branch';
import { StockAdjustment } from './StockAdjustment';
import { StockAdjustmentOutItem } from './StockAdjustmentOutItem';

export interface StockAdjustmentOutItemSerial {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  stock_adjustment?: StockAdjustment;
  stock_adjustment_out_item?: StockAdjustmentOutItem;
  serial: string;
}
