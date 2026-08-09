# Global rules when creating migration files

## Naming

1. Always start the filename with `3000_01_01_XXXXXX`.
   This separates Laravel/Vendor generated migrations from any migration generated for DCSLab.
2. `XXXXXX` should increment in steps of 100 (e.g. `000100`, `000200`, `000300`, ...).
   This leaves spare numbers in between so a migration can be slipped in later if needed.

## Structure

3. All tables should have column `ulid` right after `id`.
4. If the table is a relationship table, skip creating `ulid`, `created_by`, `updated_by`, and `timestamps`.
5. Always add `company_id` referencing `id` in `companies` for all tables, except `users`, `profiles`, `settings`, `companies`, and relationship tables.

## Columns

6. All columns are nullable unless stated otherwise.
7. Add columns `created_by` and `updated_by`, type `unsignedBigInteger`, default `0`.
8. Add column `deleted_by`, type `unsignedBigInteger`, default `0`, except on `users`, `profiles`, `settings` tables.
9. Add `timestamps`.
10. Add soft deletes, except on `users`, `profiles`, `settings` tables.

## Safety

11. Always create the `down()` function.
12. Always check if the column already exists before adding it, and skip if it does.