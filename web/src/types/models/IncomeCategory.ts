import { Company } from './Company';

export interface IncomeCategoryParent {
  id: string;
  ulid: string;
  code: string;
  display_code: string;
  name: string;
}

export interface IncomeCategory {
  id: string;
  ulid: string;
  company: Company;
  parent?: IncomeCategoryParent | null;
  code: string;
  display_code: string;
  name: string;
  sequence: number;
  children?: Array<IncomeCategory>;
}
