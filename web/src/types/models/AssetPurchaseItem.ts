import type { Asset } from './Asset';
import type { AssetPurchaseItemSerial } from './AssetPurchaseItemSerial';

export interface AssetPurchaseItem {
  id: string;
  ulid: string;
  asset: Asset;
  qty: number;
  unit_price: number;
  subtotal: number;
  allocated_additional_cost: number;
  subtotal_after_additional_cost: number;
  unit_acquisition_cost: number;
  remarks: string | null;
  serials: AssetPurchaseItemSerial[];
}
