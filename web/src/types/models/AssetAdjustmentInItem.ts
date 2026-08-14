import type { Asset } from './Asset';
import type { AssetAdjustmentInItemSerial } from './AssetAdjustmentInItemSerial';

export interface AssetAdjustmentInItem {
  id: string;
  ulid: string;
  asset: Asset;
  qty: number;
  remarks: string | null;
  serials: AssetAdjustmentInItemSerial[];
}
