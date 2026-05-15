import type { Asset } from './Asset';
import type { AssetAdjustmentOutItemSerial } from './AssetAdjustmentOutItemSerial';

export interface AssetAdjustmentOutItem {
  id: string;
  ulid: string;
  asset: Asset;
  qty: number;
  remarks: string | null;
  serials: AssetAdjustmentOutItemSerial[];
}
