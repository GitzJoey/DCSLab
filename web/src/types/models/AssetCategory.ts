import type { Company } from './Company';

export interface AssetCategory {
  id: string;
  ulid: string;
  company?: Company;
  code: string;
  name: string;
  estimated_useful_life_months: number | null;
  remarks: string | null;
}
