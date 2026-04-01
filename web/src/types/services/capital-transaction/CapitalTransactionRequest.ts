import { CapitalTransactionType } from '../../enums/CapitalTransactionType';

export interface CapitalTransactionReadAnyPaginateRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  investor_id?: string | null;
  cash_account_id?: string | null;
  type?: CapitalTransactionType | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface CapitalTransactionReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  investor_id?: string | null;
  cash_account_id?: string | null;
  type?: CapitalTransactionType | null;
  refresh: boolean;
  limit: number;
}

export interface CapitalTransactionStoreRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  investor_id: string;
  cash_account_id: string;
  type: CapitalTransactionType | '';
  amount: number;
  remarks: string | null;
}

export interface CapitalTransactionUpdateRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  investor_id: string;
  cash_account_id: string;
  type: CapitalTransactionType | '';
  amount: number;
  remarks: string | null;
}
