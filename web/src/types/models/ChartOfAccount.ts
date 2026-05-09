import { Company } from './Company';

export type ChartOfAccountScope = 'system' | 'user';
export type ChartOfAccountAccountType = 'asset' | 'liability' | 'equity' | 'income' | 'expense';
export type ChartOfAccountNormalBalance = 'debit' | 'credit';

export interface ChartOfAccountParent {
  id: string;
  ulid: string;
  code: string;
  name: string;
}

export interface ChartOfAccount {
  id: string;
  ulid: string;
  company: Company;
  parent?: ChartOfAccountParent | null;
  scope: ChartOfAccountScope;
  system_key?: string | null;
  source_type?: string | null;
  source_id?: number | null;
  code: string;
  name: string;
  account_type: ChartOfAccountAccountType;
  normal_balance: ChartOfAccountNormalBalance;
  level: number;
  is_group: boolean;
  is_active: boolean;
  remarks?: string | null;
  children?: Array<ChartOfAccount>;
}

export const CHART_OF_ACCOUNT_SYSTEM_KEYS = [
  'asset_root',
  'asset_current',
  'asset_current_cash_and_cash_equivalents',
  'asset_current_account_receivable',
  'asset_current_inventory',
  'asset_current_prepaid_expense',
  'asset_non_current',
  'asset_non_current_fixed_asset',
  'asset_non_current_accumulated_depreciation',
  'asset_non_current_intangible_asset',
  'liability_root',
  'liability_account_payable',
  'liability_tax_payable',
  'equity_root',
  'equity_owner_capital',
  'equity_retained_earnings',
  'income_root',
  'income_sales',
  'income_service',
  'cogs_root',
  'cogs_material_cost',
  'cogs_direct_labor_cost',
  'expense_root',
  'expense_salary',
  'expense_electricity',
  'expense_rent',
  'other_income_root',
  'other_income_interest',
  'other_expense_root',
  'other_expense_interest',
] as const;
