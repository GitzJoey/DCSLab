# Global rules on Application Architecture (Layering)

This describes the required request flow and responsibilities for each layer.
These rules apply globally across the whole API, regardless of the specific
feature/module being built.

## Request Flow

```
Route -> Controller -> Form Request (validation/authorization)
                    -> Action (business logic + Eloquent calls)
                    -> API Resource (response formatting)
```

## Layers & Responsibilities

1. **Routes**
    - Only map an HTTP verb + URI to a single Controller method.
    - No closures containing logic; no inline validation.

2. **Controllers**
    - Live under `app/Http/Controllers`.
    - Only allowed to: receive a Form Request, call **one** Action, and return
      an API Resource (or a Resource Collection).
    - Must NOT contain business logic, validation rules, or Eloquent/model calls
      of any kind (no `Model::query()`, `Model::where()`, `$model->save()`, etc.).
    - Must NOT call other Actions directly except through the single Action for
      that endpoint (an Action may internally call other Actions, see below).

3. **Form Requests**
    - Live under `app/Http/Requests`.
    - Only responsible for validation rules (`rules()`) and authorization
      (`authorize()`) of the incoming request.
    - Must NOT contain business logic or Eloquent/model calls, except read-only
      lookups strictly required for validation (e.g. `exists`/`unique` rules).

4. **Actions**
    - Live under `app/Actions`, namespaced to match their feature/module
      (e.g. `app/Actions/Company/CreateCompanyAction.php`).
    - Are the **only** place allowed to call Eloquent/models directly
      (queries, creates, updates, deletes, relationships, transactions, etc).
    - Contain all business logic for a single use case (single responsibility,
      one Action = one use case, e.g. `CreateCompanyAction`, `ListBranchesAction`).
    - May call other Actions when a use case is composed of smaller use cases.
    - Must NOT return HTTP responses, Resources, or depend on the `Request`
      facade/HTTP layer — accept plain arguments (typed DTOs/arrays/models) and
      return plain data (models/collections/primitives).
    - Must be invokable via `__invoke()` or a clearly named public method, and
      must always be resolved from the container (`app(Action::class)` or
      constructor injection in the Controller), never `new`'d directly in
      Controllers.
    - For company-owned models (models with `company_id`), Actions must accept
      `company_id` as an explicit argument (sourced by the Controller from the
      request, e.g. the `company_id` query string) and filter Eloquent calls
      with it directly (e.g. `Branch::where('company_id', $companyId)->...`).
      Models must NOT use a global scope for this — see RULES_MODELS.md.

5. **API Resources**
    - Live under `app/Http/Resources`.
    - Only responsible for shaping the outgoing JSON response from a
      model/collection returned by an Action.
    - Must NOT contain business logic or Eloquent/model calls beyond accessing
      already-loaded relationships/attributes on the resource.

## Guardrails

6. Eloquent/model calls (`Model::`, `$model->`, query builder, relationships)
   are **only** allowed inside classes under `app/Actions`. Any Eloquent usage
   found in `app/Http/Controllers`, `app/Http/Requests`, or
   `app/Http/Resources` is a violation of these rules and must be refactored
   into an Action.
7. Actions are **only** allowed to be called from Controllers (or from other
   Actions). Actions must never be called from Requests, Resources, or Models.
8. Every Controller endpoint must call exactly one top-level Action.
9. Keep Actions single-purpose; if an Action grows to handle multiple unrelated
   use cases, split it into separate Actions.
