export interface PrepaidExpenseImage {
  id: string;
  prepaid_expense_id?: string | null;
  path: string;
  hash: string;
  url: string;
  is_main: boolean;
}
