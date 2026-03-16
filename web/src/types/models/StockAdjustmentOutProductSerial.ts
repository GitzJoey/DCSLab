import { Company } from './Company';
import { Branch } from './Branch';
import { StockAdjustment } from './StockAdjustment';
import { StockAdjustmentOutProduct } from './StockAdjustmentOutProduct';

export interface StockAdjustmentOutProductSerial {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  stock_adjustment?: StockAdjustment;
  stock_adjustment_out_product?: StockAdjustmentOutProduct;
  serial: string;
}
