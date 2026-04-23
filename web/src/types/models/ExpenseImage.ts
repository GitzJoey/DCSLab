export interface ExpenseImage {
  id: string;
  expense_id?: string | null;
  path: string;
  hash: string;
  url: string;
  is_main: boolean;
}
