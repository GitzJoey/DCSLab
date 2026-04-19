import { Company } from './Company';

export interface PurchaseAdditionalCostCategory {
  id: string;
  ulid: string;
  company: Company;
  code: string;
  name: string;
}
