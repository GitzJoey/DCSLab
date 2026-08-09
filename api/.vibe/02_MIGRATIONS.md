# Migration

This is to create all the migration files.
Read RULES_MIGRATIONS.md for the rules on migration files.

1. Alter `users` table
    - Add column `password_changed_at` after `password`

2. Create `profiles` table
    a. Columns
        - `user_id` — references `id` on `users`
        - `first_name` (string)
        - `last_name` (string)
        - `address` (string)
        - `city` (string)
        - `postal_code` (string)
        - `country` (string)
        - `tax_id` (string)
        - `ic_num` (string)
        - `img_path` (string)
        - `status` (integer)
        - `remarks` (string)
    b. Index
        - `first_name`
        - `last_name`

3. Create `settings` table
    a. Columns
        - `user_id` — references `id` on `users`
        - `type` (string)
        - `key` (string)
        - `value` (string)

4. Create `companies` table
    a. Columns
        - `code` (string) (not null)
        - `name` (string) (not null)
        - `address` (string)
        - `default` (boolean) (not null) (default false)
        - `status` (integer) (not null) (default 0)
    b. Index
        - `code`
        - `name`

5. Create `company_user` table (relationship table for Company and User)
    a. Columns
        - `user_id` — references `id` on `users`
        - `company_id` — references `id` on `companies`

6. Create `branches` table
    a. Columns
        - `code` (string) (not null)
        - `name` (string) (not null)
        - `address` (string)
        - `main_branch` (boolean) (not null) (default false)
        - `status` (integer) (not null) (default 0)
    b. Index
        - `code`
        - `name`
