import { Company } from './Company';

export interface VatProfile {
  id: string;
  ulid: string;
  company: Company;
  code: string;
  name: string;
  vat_rate: number | string;
  vat_base_numerator: number;
  vat_base_denominator: number;
  remarks: string | null;
  is_active: boolean;
}
