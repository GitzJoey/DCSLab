import { Company } from './Company';
import { Branch } from './Branch';
import { Warehouse } from './Warehouse';
import { StockAdjustmentCategory } from './StockAdjustmentCategory';
import { StockAdjustmentInProduct } from './StockAdjustmentInProduct';
import { StockAdjustmentOutProduct } from './StockAdjustmentOutProduct';

export interface StockAdjustment {
  id: string;
  ulid: string;
  company: Company;
  branch: Branch;
  code: string;
  date: string;
  category: StockAdjustmentCategory;
  in_warehouse: Warehouse | null;
  out_warehouse: Warehouse | null;
  remarks: string | null;
  is_posted: boolean;
  total_incoming_product_qty: number;
  total_incoming_product_cogs: number;
  total_outgoing_product_qty: number;
  in_products: StockAdjustmentInProduct[];
  out_products: StockAdjustmentOutProduct[];
}
