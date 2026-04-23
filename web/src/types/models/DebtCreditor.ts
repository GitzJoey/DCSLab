import { Company } from './Company';

export interface DebtCreditor {
  id: string;
  ulid: string;
  company: Company;
  code: string;
  name: string;
  remarks: string;
}
