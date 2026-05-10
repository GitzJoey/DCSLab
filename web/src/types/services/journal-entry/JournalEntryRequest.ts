export interface JournalEntryReadAnyGetRequest {
  with_trashed: boolean;
  company_id: string;
  branch_id?: string | null;
  search?: string | null;
  start_date?: string | null;
  end_date?: string | null;
  source_type?: string | null;
  source_id?: number | null;
  refresh: boolean;
  limit: number;
}

export interface JournalEntryLineStoreRequest {
  chart_of_account_id: string;
  debit: number;
  credit: number;
  remarks: string | null;
}

export interface JournalEntryStoreRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  source_type: string | null;
  source_id: number | null;
  reference_no: string | null;
  remarks: string | null;
  lines: JournalEntryLineStoreRequest[];
}

export interface JournalEntryUpdateRequest {
  company_id: string;
  branch_id: string;
  code: string;
  date: string;
  reference_no: string | null;
  remarks: string | null;
  lines: JournalEntryLineStoreRequest[];
}
