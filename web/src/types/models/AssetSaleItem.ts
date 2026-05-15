import type { Asset } from './Asset';
import type { AssetSaleItemSerial } from './AssetSaleItemSerial';

export interface AssetSaleItem {
  id: string;
  ulid: string;
  asset: Asset;
  qty: number;
  unit_price: number;
  subtotal: number;
  remarks: string | null;
  serials: AssetSaleItemSerial[];
}
