import { Company } from './Company';

export interface ExpenseCategoryParent {
  id: string;
  ulid: string;
  code: string;
  display_code: string;
  name: string;
}

export interface ExpenseCategory {
  id: string;
  ulid: string;
  company: Company;
  parent?: ExpenseCategoryParent | null;
  code: string;
  display_code: string;
  name: string;
  sequence: number;
  children?: Array<ExpenseCategory>;
}
