export interface JournalEntryItemReadAnyPaginateRequest {
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  start_date?: string | null;
  end_date?: string | null;
  journal_entry_id?: string | null;
  chart_of_account_id?: string | null;
  include_id?: string | null;
  refresh: boolean;
  page: number;
  per_page: number;
}

export interface JournalEntryItemReadAnyGetRequest {
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  start_date?: string | null;
  end_date?: string | null;
  journal_entry_id?: string | null;
  chart_of_account_id?: string | null;
  include_id?: string | null;
  refresh: boolean;
  limit: number;
}
