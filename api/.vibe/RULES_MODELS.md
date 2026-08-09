# Global rules when creating Model files

## Naming & Structure

1. Namespace all models under `App\Models`.
2. Namespace all model-behavior traits under `App\Models\Concerns`.
3. Do not create a dedicated Pivot model for a default pivot table (just foreign keys). Only create one extending `Illuminate\Database\Eloquent\Relations\Pivot` and attach it via `->using()` when the pivot table has extra behavior (e.g. `deleted_by`, soft deletes, or other custom columns/logic).

## Traits

4. Always use the `HasFactory` trait.
5. Use the `SoftDeletes` trait, except on `User`, `Profile`, `Setting` models (matches tables that have no soft deletes).

## Columns

6. Use the Laravel 13 attribute style over legacy properties/methods where available:
   - `#[Fillable([...])]` instead of `protected $fillable`
   - `#[Hidden([...])]` instead of `protected $hidden`
   - `#[RouteKey('ulid')]` instead of overriding `getRouteKeyName()`
   - `#[Table('name')]` instead of `protected $table` (e.g. on Pivot models)
   - `protected function casts(): array` (Laravel 11+ style) instead of `protected $casts` — there is no attribute for casts yet.
7. Set `#[Fillable]` to match the columns defined in the model's migration (exclude `id`, `ulid`, timestamps, and soft-delete columns).
8. Set `casts()` for non-string columns (e.g. `boolean`, `integer`, `datetime`) and for `status` fields.
9. Use `#[Hidden]` to hide sensitive columns (e.g. `password`, tokens) from array/JSON output.
10. Use `#[RouteKey('ulid')]` so routes/URLs never expose the internal auto-increment `id`.
11. Auto-fill `created_by`, `updated_by`, and `deleted_by` (where applicable) from the authenticated user via model event hooks (using a `Concerns` trait) — never rely on the caller to set them manually.

## Relationships & Scoping

12. Define Eloquent relationships for every foreign key found in the migration (`belongsTo`, `hasMany`, `belongsToMany`, etc).
13. Scope all company-owned models (models with `company_id`) to the active company using a global scope, except `User`, `Profile`, `Setting`, `Company`, and relationship tables. Prefer the `#[ScopedBy([...])]` attribute over manually registering the scope in a `booted()` method.
