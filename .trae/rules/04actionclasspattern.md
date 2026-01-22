---
alwaysApply: false
description: 
---
Berikut adalah analisa pola standar yang ditemukan pada semua *Class Actions* di bawah menu **Master Data**. Analisa ini disusun dalam format yang siap Anda salin ke dalam file rules untuk standarisasi proyek.

Analisa ini mencakup struktur dasar, pola method CRUD, penanganan *caching*, *logging*, dan *unique code generation*.

***

# Standardisasi Action Class (Master Data)

Pola ini berlaku untuk *Action Classes* yang mengelola entitas Master Data (contoh: `CompanyActions`, `BrandActions`, `CustomerActions`, dll).

## 1. Struktur Dasar Class
Setiap Action class harus memiliki struktur berikut:
*   **Namespace**: `App\Actions\{EntityName}`
*   **Class Name**: `{EntityName}Actions`
*   **Traits Wajib**:
    *   `use App\Traits\CacheHelper;` (Untuk manajemen cache otomatis)
    *   `use App\Traits\LoggerHelper;` (Untuk logging error dan performa)
*   **Constructor**: Umumnya kosong `public function __construct() {}`.

## 2. Pola Method CRUD
Setiap Action class umumnya mengimplementasikan 5 method utama dengan *signature* dan alur logika yang konsisten.

### A. Method `create`
*   **Signature**: `public function create(array $data): Model`
    *   *Catatan*: Untuk entitas kompleks (seperti Product), parameter dapat berupa **DTO**.
*   **Alur Logika**:
    1.  Start timer: `$timer_start = microtime(true);`
    2.  Block `try-catch-finally`.
    3.  Instansiasi Model baru.
    4.  **Auto-generate Code**: `$model->code = $this->generateUniqueCode(...)`.
    5.  Assign attributes dari `$data`.
    6.  Simpan Model: `$model->save()`.
    7.  **Flush Cache**: `$this->flushCache()`.
    8.  Return Model.
*   **Error Handling**: Log error di `catch` menggunakan `$this->loggerDebug(__METHOD__, $e)`.
*   **Performance**: Log waktu eksekusi di `finally` menggunakan `$this->loggerPerformance(__METHOD__, $execution_time)`.

### B. Method `readAny`
*   **Signature**: `public function readAny(bool $withTrashed, int $companyId, ..., ?ExecuteDTO $execute)`
*   **Alur Logika**:
    1.  Build Query: `Model::with(...)->select(...)->whereCompanyId(...)`.
    2.  Apply Filters: Menggunakan *nested closure* untuk logika `withTrashed`, `search`, dan filter spesifik lainnya.
    3.  Apply Sorting: Biasanya `orderBy('name', 'asc')` atau `FIELD(id, ...)` jika ada `includeId`.
    4.  **Caching Logic**:
        *   Generate `$cacheKey` berdasarkan parameter.
        *   Cek cache jika `$execute->useCache` true.
        *   Simpan hasil ke cache setelah query.
    5.  **Execution**: Handle `pagination` atau `get` limit berdasarkan `ExecuteDTO`.

### C. Method `read`
*   **Signature**: `public function read(Model $model): Model`
*   **Alur Logika**:
    *   Load relasi yang dibutuhkan: `return $model->load('relation1', 'relation2');`.

### D. Method `update`
*   **Signature**: `public function update(Model $model, array $data): Model`
    *   *Catatan*: Gunakan **DTO** jika entitas kompleks.
*   **Alur Logika**:
    1.  Start timer & Try-Catch-Finally.
    2.  **Regenerate Code**: `$this->generateUniqueCode(..., $model->id)` (pastikan kirim ID untuk pengecualian unik).
    3.  Update attributes.
    4.  `$model->save()`.
    5.  `$this->flushCache()`.
    6.  Return `$model->refresh()`.

### E. Method `delete`
*   **Signature**: `public function delete(Model $model): bool`
*   **Alur Logika**:
    1.  Start timer & Try-Catch-Finally.
    2.  `$model->delete()`.
    3.  `$this->flushCache()`.
    4.  Return `true` (atau hasil delete).

## 3. Helper Methods (Wajib Ada)
Untuk menjaga konsistensi data unik (Code & Name), method berikut wajib ada:

### A. `generateUniqueCode`
*   **Signature**: `public function generateUniqueCode(int $companyId, string $code, ?int $exceptId): string`
*   **Logika**:
    *   Cek jika input adalah keyword AUTO (misal: `config('dcslab.KEYWORDS.AUTO')`).
    *   Looping `do-while` untuk generate kode (Prefix + Counter + Pad).
    *   Validasi keunikan menggunakan `isUniqueCode`.

### B. `isUniqueCode`
*   **Signature**: `public function isUniqueCode(int $companyId, string $code, ?int $exceptId): bool`
*   **Logika**: Cek database apakah kode sudah ada (exclude `$exceptId` jika update).

### C. `isUniqueName`
*   **Signature**: `public function isUniqueName(int $companyId, string $name, ?int $exceptId): bool`
*   **Logika**: Validasi keunikan nama (sering digunakan untuk validasi input).

***

Anda dapat menyalin teks di atas ke dalam file Markdown (misalnya: `docs/standards/ACTION_CLASS_PATTERN.md` atau ke dalam `.trae/rules/`) untuk referensi pengembangan selanjutnya.