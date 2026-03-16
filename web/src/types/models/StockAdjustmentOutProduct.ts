import { Company } from './Company';
import { Branch } from './Branch';
import { StockAdjustment } from './StockAdjustment';
import { ProductUnit } from './ProductUnit';
import { StockAdjustmentOutProductSerial } from './StockAdjustmentOutProductSerial';

export interface StockAdjustmentOutProduct {
  id: string;
  ulid: string;
  company: Company;
  branch: Branch;
  stock_adjustment: StockAdjustment;
  qty: number;
  product_unit: ProductUnit;
  product_unit_conversion_value: number;
  product_unit_qty_base: number;
  remarks: string | null;
  serials: StockAdjustmentOutProductSerial[];
}
