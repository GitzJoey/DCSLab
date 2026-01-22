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
