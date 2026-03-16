import { Company } from './Company';
import { Branch } from './Branch';
import { StockAdjustment } from './StockAdjustment';
import { StockAdjustmentInProduct } from './StockAdjustmentInProduct';

export interface StockAdjustmentInProductSerial {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch;
  stock_adjustment?: StockAdjustment;
  stock_adjustment_in_product?: StockAdjustmentInProduct;
  serial: string;
}
