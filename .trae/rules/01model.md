---
alwaysApply: false
description: 
---
# Laravel Search Scope Standard

## Rule
Untuk semua fitur pencarian (search) yang melibatkan beberapa kolom dengan OR, WAJIB dibungkus `where(function ($query) use (...) { ... })`
agar seluruh OR-terikat dalam satu grup dan tidak merusak kondisi lain (mis. tenant_id, is_active, date range).

Kolom `date`, `datetime`, `timestamp`, dan field waktu sejenis pada Model TIDAK BOLEH dimasukkan ke `scopeSearch()` secara default,
kecuali memang ada kebutuhan bisnis yang jelas dan eksplisit bahwa user harus bisa mencari berdasarkan tanggal dari input search bebas.
Untuk search umum, prioritaskan hanya field yang benar-benar relevan secara tekstual seperti `code`, `name`, `reference`, `notes`, atau `remarks`.

## Why
Tanpa grouping closure, `orWhere` dapat “membocorkan” logika sehingga mengabaikan filter lain di query utama.

Kolom tanggal biasanya tidak relevan untuk keyword search umum, memperbesar noise hasil pencarian, dan lebih tepat ditangani oleh filter terpisah
seperti `start_date`, `end_date`, atau parameter range tanggal.

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

## Example (Incorrect)
```php
public function scopeSearch($query, string $search)
{
    return $query->where(function ($query) use ($search) {
        $query->where('capital_openings.code', 'like', '%'.$search.'%')
            ->orWhere('capital_openings.date', 'like', '%'.$search.'%')
            ->orWhere('capital_openings.remarks', 'like', '%'.$search.'%');
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

# Field Order Consistency Standard

## Rule
Urutan field pada Model dan layer terkait WAJIB mengikuti urutan definisi kolom di migration utama tabel tersebut.

Aturan ini berlaku untuk:
- `$fillable`
- assignment field di Action `create()` dan `update()`
- urutan output field di Resource
- urutan field di FormRequest `rules()`, `attributes()`, dan `prepareForValidation()`
- array atau mapping lain yang merepresentasikan field yang sama

Jika tidak semua field dipakai pada sebuah method, pertahankan urutan relatif berdasarkan migration dan cukup lewati field yang memang tidak relevan.

## Why
Urutan yang konsisten membuat review lebih cepat, meminimalkan salah mapping antar layer, dan memudahkan pengecekan apakah implementasi sudah sesuai struktur database.

## Example
Jika migration mendefinisikan urutan:
```php
$table->foreignId('company_id');
$table->foreignId('branch_id');
$table->string('code');
$table->dateTime('date');
$table->foreignId('investor_id');
$table->foreignId('cash_account_id');
$table->decimal('amount', 30, 8)->default(0);
$table->string('remarks')->nullable();
```

Maka urutan field di Model, Action, Resource, dan Request harus mengikuti susunan yang sama:
```php
'company_id',
'branch_id',
'code',
'date',
'investor_id',
'cash_account_id',
'amount',
'remarks',
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
