import type { Company } from './Company';

export interface DebtCategory {
  id: string;
  ulid: string;
  company?: Company;
  code: string;
  name: string;
  sequence: number;
}
