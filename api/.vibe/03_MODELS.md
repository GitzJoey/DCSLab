# Models

This is to create all the model files based on the migrations already created.
Read RULES_MODELS.md for the rules on model files.

## Prerequisite

Create shared model concerns under `app/Models/Concerns`
    - `HasUlid` — auto-generates the `ulid` column on creating; used by `Profile`, `Setting`, `Company`, `Branch`
    - `TracksCreatedByAndUpdatedBy` — auto-fills `created_by`/`updated_by` from the authenticated user; used by `Profile`, `Setting`, `Company`, `Branch`
    - `TracksDeletedBy` — auto-fills `deleted_by` from the authenticated user right before a soft delete; used by `Company`, `Branch`, `CompanyUser`

Create shared enums under `app/Enums`
    - `RecordStatus` — backed int enum for any `status` column; used by `Profile`, `Company`, `Branch`
        - `INACTIVE = 0`
        - `ACTIVE = 1`
        - `SOFTDELETED = 99`

## Models

1. Update `User` model
    - Add `password_changed_at` to `$casts` (datetime)
    - Relationships
        - `profile()` — hasOne `Profile`
        - `settings()` — hasMany `Setting`
        - `companies()` — belongsToMany `Company` through `company_user` (use the `CompanyUser` pivot model)

2. Create `Profile` model
    - Relationships
        - `user()` — belongsTo `User`

3. Create `Setting` model
    - Relationships
        - `user()` — belongsTo `User`

4. Create `Company` model
    - Relationships
        - `users()` — belongsToMany `User` through `company_user` (use the `CompanyUser` pivot model)
        - `branches()` — hasMany `Branch`

5. Create `CompanyUser` pivot model (for the `company_user` relationship table)
    - Extends `Illuminate\Database\Eloquent\Relations\Pivot`
    - Uses `SoftDeletes`

6. Create `Branch` model
    - Relationships
        - `company()` — belongsTo `Company`


