import type { Company } from './Company';
import type { AssetCategory } from './AssetCategory';
import type { AssetUnit } from './AssetUnit';

export interface Asset {
  id: string;
  ulid: string;
  company?: Company;
  asset_category?: AssetCategory;
  code: string;
  name: string;
  asset_unit?: AssetUnit;
  status: number;
  remarks: string | null;
}
