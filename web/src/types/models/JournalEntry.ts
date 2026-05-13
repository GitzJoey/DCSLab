import type { Branch } from './Branch';
import type { ChartOfAccount } from './ChartOfAccount';
import type { Company } from './Company';

export interface JournalEntryItem {
  id: string;
  journal_entry_id: string;
  chart_of_account_id: string;
  sequence: number;
  debit: number;
  credit: number;
  remarks: string | null;
  chart_of_account?: Pick<ChartOfAccount, 'id' | 'ulid' | 'code' | 'name' | 'account_type' | 'normal_balance'>;
}

export interface JournalEntry {
  id: string;
  ulid: string;
  company?: Company;
  branch?: Branch | null;
  code: string;
  date: string;
  source_type: string | null;
  source_id: number | null;
  reference_no: string | null;
  total_debit: number;
  total_credit: number;
  remarks: string | null;
  items: JournalEntryItem[];
}
