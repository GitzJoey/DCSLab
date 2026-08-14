import type { Company } from './Company';

export interface AssetUnit {
  id: string;
  ulid: string;
  company?: Company;
  code: string;
  name: string;
  description: string | null;
}
