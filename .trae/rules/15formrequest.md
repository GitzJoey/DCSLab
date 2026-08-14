---
alwaysApply: false
description: Standarisasi Laravel FormRequest backend
---
# Laravel FormRequest Standard

## Authorize Method

### Rule
Method `authorize()` pada Laravel FormRequest backend WAJIB ditulis eksplisit, tidak dalam bentuk one-liner.

Gunakan pola berikut:
- cek autentikasi lebih dulu dengan `Auth::check()`
- ambil user dari `Auth::user()`
- untuk `StoreRequest`, gunakan policy check ke `Model::class`
- untuk `UpdateRequest`, ambil model dari route binding lalu cek policy terhadap instance tersebut

Hindari menulis `authorize()` dalam bentuk singkat seperti `return auth()->check() && ...` karena kurang konsisten dengan pola project dan lebih sulit direview saat butuh menambah logika.

### Why
- Konsisten dengan mayoritas FormRequest backend di project
- Lebih mudah dibaca dan diperluas saat ada kebutuhan tambahan
- Membuat pemisahan jelas antara pengecekan login, pengambilan resource route, dan policy check

### Example (StoreRequest)
```php
public function authorize()
{
    if (! Auth::check()) {
        return false;
    }

    /** @var \App\User */
    $user = Auth::user();

    return $user->can('create', CapitalOpening::class);
}
```

### Example (UpdateRequest)
```php
public function authorize()
{
    if (! Auth::check()) {
        return false;
    }

    /** @var \App\User */
    $user = Auth::user();
    $capitalOpening = $this->route('capital_opening');

    return $user->can('update', $capitalOpening);
}
```

## Prepare For Validation

### Rule
Method `prepareForValidation()` pada Laravel FormRequest backend hanya boleh dipakai untuk normalisasi input yang memang perlu dilakukan sebelum validasi, terutama:
- decode Hashids ke integer ID
- trimming atau normalisasi format sederhana yang memang dibutuhkan validator

`prepareForValidation()` TIDAK BOLEH dipakai untuk mengurus field nullable biasa yang bukan bagian dari decoding atau normalisasi inti, misalnya memaksa `remarks`, `notes`, `description`, dan field teks sejenis menjadi `null`.

Jika sebuah field memang opsional namun tetap wajib hadir di payload, gunakan rule `present` + `nullable` di `rules()`, dan biarkan validator menangani nilainya apa adanya.

### Why
- Tanggung jawab `prepareForValidation()` tetap sempit dan jelas: normalisasi sebelum validasi
- Mencegah side effect tersembunyi yang mengubah payload di luar kebutuhan validasi inti
- Membuat kontrak request lebih konsisten: field nullable dikontrol lewat `rules()`, bukan lewat mutasi tambahan

### Example (Correct)
```php
public function rules(): array
{
    return [
        'company_id' => ['required', 'integer', 'bail', new IsValidCompany],
        'branch_id' => ['required', 'integer', 'bail', new IsValidBranch($this->company_id, true)],
        'remarks' => ['present', 'nullable', 'string', 'max:255'],
    ];
}

public function prepareForValidation()
{
    $this->merge([
        'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
        'branch_id' => $this->filled('branch_id') ? HashidsHelper::decodeId($this->branch_id) : null,
    ]);
}
```

### Example (Incorrect)
```php
public function prepareForValidation()
{
    $this->merge([
        'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
        'remarks' => $this->filled('remarks') ? $this->remarks : null,
    ]);
}
```
