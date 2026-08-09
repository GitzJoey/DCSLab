# Global rules when creating Model files

## Naming & Structure

1. Namespace all models under `App\Models`.
2. Namespace all model-behavior traits under `App\Models\Concerns`.
3. Namespace all enums under `App\Enums`.
4. Do not create a dedicated Pivot model for a default pivot table (just foreign keys). Only create one extending `Illuminate\Database\Eloquent\Relations\Pivot` and attach it via `->using()` when the pivot table has extra behavior (e.g. `deleted_by`, soft deletes, or other custom columns/logic).

## Traits

5. Always use the `HasFactory` trait.
6. Use the `SoftDeletes` trait, except on `User`, `Profile`, `Setting` models (matches tables that have no soft deletes).

## Columns

7. Use the Laravel 13 attribute style over legacy properties/methods where available:
   - `#[Fillable([...])]` instead of `protected $fillable`
   - `#[Hidden([...])]` instead of `protected $hidden`
   - `#[RouteKey('ulid')]` instead of overriding `getRouteKeyName()`
   - `#[Table('name')]` instead of `protected $table` (e.g. on Pivot models)
   - `protected function casts(): array` (Laravel 11+ style) instead of `protected $casts` — there is no attribute for casts yet.
8. Set `#[Fillable]` to match the columns defined in the model's migration (exclude `id`, `ulid`, timestamps, and soft-delete columns).
9. Set `casts()` for non-string columns (e.g. `boolean`, `integer`, `datetime`), and cast `status` columns to the `App\Enums\RecordStatus` enum (never a plain `integer`).
10. Use `#[Hidden]` to hide sensitive columns (e.g. `password`, tokens) from array/JSON output.
11. Use `#[RouteKey('ulid')]` so routes/URLs never expose the internal auto-increment `id`.
12. Auto-fill `created_by`, `updated_by`, and `deleted_by` (where applicable) from the authenticated user via model event hooks (using a `Concerns` trait) — never rely on the caller to set them manually.

## Relationships & Scoping

13. Define Eloquent relationships for every foreign key found in the migration (`belongsTo`, `hasMany`, `belongsToMany`, etc).
14. Do NOT add a global scope for company-owned models (models with `company_id`). Per RULES_ARCHITECTURE.md, all Eloquent calls are made from Actions, which always receive `company_id` as an explicit argument and filter with it directly (e.g. `Branch::where('company_id', $companyId)->...`).
