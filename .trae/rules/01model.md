---
alwaysApply: false
description: 
---
# Laravel Search Scope Standard

## Rule
Untuk semua fitur pencarian (search) yang melibatkan beberapa kolom dengan OR, WAJIB dibungkus `where(function ($query) use (...) { ... })`
agar seluruh OR-terikat dalam satu grup dan tidak merusak kondisi lain (mis. tenant_id, is_active, date range).

## Why
Tanpa grouping closure, `orWhere` dapat “membocorkan” logika sehingga mengabaikan filter lain di query utama.

## Example (Correct)
```php
public function scopeSearch($query, string $search)
{
    return $query->where(function ($query) use ($search) {
        $query->where('cash_accounts.code', 'like', '%'.$search.'%')
            ->orWhere('cash_accounts.name', 'like', '%'.$search.'%')
            ->orWhere('cash_accounts.remarks', 'like', '%'.$search.'%');
    });
}
```

# BelongsTo Relationship Standard

## Rule
Setiap relasi `belongsTo` WAJIB menggunakan `withTrashed()` jika model yang direlasikan mendukung SoftDeletes.

## Why
Agar data tidak hilang saat parent (referensi) di-soft delete, sehingga integritas data historis tetap terjaga saat ditampilkan.

## Example (Correct)
```php
public function company()
{
    return $this->belongsTo(Company::class)->withTrashed();
}
```

# Scopeable Traits Standard

## Rule
1.  **Gunakan Trait Standar**:
    *   `App\Traits\ScopeableByCompany`: Untuk model yang memiliki `company_id`.
    *   `App\Traits\ScopeableByBranch`: Untuk model yang memiliki `branch_id`.
    *   `App\Traits\ScopeableByStatus`: Untuk model yang memiliki `status`.
2.  **Mandatory Trait**: Jika Action Class atau Controller menggunakan helper `whereCompanyId($id)` atau `whereBranchId($id)`, Model **WAJIB** menggunakan trait terkait secara eksplisit (`use Scopeable...`). Jangan berasumsi helper tersedia secara global.

## Example
```php
use App\Traits\ScopeableByCompany;
use App\Traits\ScopeableByBranch;

class Order extends Model
{
    // WAJIB use Trait agar method scope tersedia
    use ScopeableByCompany, ScopeableByBranch;
    // ...
}

// Usage in Action/Controller
$query->whereCompanyId($companyId)->whereBranchId($branchId);
```
