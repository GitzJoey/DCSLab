import { Company } from './Company';
import { Branch } from './Branch';
import { StockAdjustment } from './StockAdjustment';
import { ProductUnit } from './ProductUnit';
import { StockAdjustmentInProductSerial } from './StockAdjustmentInProductSerial';

export interface StockAdjustmentInProduct {
  id: string;
  ulid: string;
  company: Company;
  branch: Branch;
  stock_adjustment: StockAdjustment;
  qty: number;
  product_unit: ProductUnit;
  product_unit_conversion_value: number;
  product_unit_qty_base: number;
  product_unit_cogs: number;
  product_unit_total_cogs: number;
  product_unit_base_unit_cogs: number;
  remarks: string | null;
  serials: StockAdjustmentInProductSerial[];
}
