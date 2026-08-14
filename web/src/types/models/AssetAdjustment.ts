import type { Branch } from './Branch';
import type { Company } from './Company';
import type { AssetAdjustmentInItem } from './AssetAdjustmentInItem';
import type { AssetAdjustmentOutItem } from './AssetAdjustmentOutItem';

export interface AssetAdjustment {
  id: string;
  ulid: string;
  company: Company;
  branch: Branch;
  code: string;
  date: string;
  remarks: string | null;
  is_posted: boolean;
  total_incoming_asset_qty: number;
  total_outgoing_asset_qty: number;
  in_items: AssetAdjustmentInItem[];
  out_items: AssetAdjustmentOutItem[];
}
