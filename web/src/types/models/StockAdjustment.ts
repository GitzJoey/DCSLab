import { Company } from './Company';
import { Branch } from './Branch';
import { Warehouse } from './Warehouse';
import { StockAdjustmentCategory } from './StockAdjustmentCategory';
import { StockAdjustmentInItem } from './StockAdjustmentInItem';
import { StockAdjustmentOutItem } from './StockAdjustmentOutItem';

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
  total_incoming_item_qty: number;
  total_incoming_item_cogs: number;
  total_outgoing_item_qty: number;
  in_items: StockAdjustmentInItem[];
  out_items: StockAdjustmentOutItem[];
}
