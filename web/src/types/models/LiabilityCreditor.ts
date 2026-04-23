import { Company } from './Company';

export interface LiabilityCreditor {
  id: string;
  ulid: string;
  company: Company;
  code: string;
  name: string;
  remarks: string;
}
