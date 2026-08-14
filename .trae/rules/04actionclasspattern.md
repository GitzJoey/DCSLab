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
*   **Database Transaction**: **TIDAK PERLU** menggunakan `DB::beginTransaction()`, `DB::commit()`, atau `DB::rollBack()`.
    *   Transaction akan ditangani di layer yang lebih tinggi (Controller/Service) atau tidak diperlukan untuk operasi single-model sederhana.
    *   Cukup gunakan blok `try-catch` untuk menangkap exception dan melakukan logging.

## 2. Pola Method CRUD
Setiap Action class umumnya mengimplementasikan 5 method utama dengan *signature* dan alur logika yang konsisten.

### 2.1 Urutan Method Public
Untuk konsistensi navigasi dan kemudahan membaca, urutan method public di setiap Action class harus mengikuti pola berikut:

1. `readAny`
2. `read`
3. `create`
4. `update`
5. `delete`

### A. Method `create`
*   **Signature**: `public function create(array $data): Model`
    *   *Catatan*: Untuk entitas kompleks (seperti Product), parameter dapat berupa **DTO**.
*   **Alur Logika**:
    1.  Start timer: `$timer_start = microtime(true);`
    2.  Block `try-catch-finally`.
    3.  Instansiasi Model baru.
    4.  **Auto-generate Code**: `$model->code = $this->generateUniqueCode(...)`.
    5.  Assign attributes dari `$data`.
        *   **Null Safety**: Jangan gunakan operator `?? null` atau `?? default` di sini. Null safety dan validasi data harus sudah ditangani di Controller/Request. Action class berasumsi data yang diterima sudah valid dan lengkap (sesuai struktur tabel).
    6.  Simpan Model: `$model->save()`.
    7.  **Flush Cache**: `$this->flushCache()`.
    8.  Return Model.
*   **Error Handling**: Log error di `catch` menggunakan `$this->loggerDebug(__METHOD__, $e)`.
*   **Performance**: Log waktu eksekusi di `finally` menggunakan `$this->loggerPerformance(__METHOD__, $execution_time)`.

### B. Method `readAny`
*   **Signature**: `public function readAny(bool $withTrashed, int $companyId, ?int $branchId, ..., ?ExecuteDTO $execute)`
*   **Grouping Parameter (Wajib 3 Blok Visual untuk Non-Outlier)**:
*   Untuk semua Action class non-legacy yang memakai `?ExecuteDTO $execute`, signature `readAny` wajib dibagi menjadi 3 blok dengan linebreak:
*       1. Blok basis/konteks: parameter scope utama seperti `withTrashed`, `companyId`, `branchId`, `search`, atau parameter konteks lain seperti `referableType` dan `referableId` bila modul tidak memakai company scope
*       2. Blok filter: seluruh filter spesifik modul seperti `includeId`, `categoryId`, `brandId`, `warehouseId`, `cashAccountId`, `productId`, `productUnitId`, `serial`, `withRemainingStock`, dan filter bisnis lain
*       3. Blok eksekusi: `execute`
*   Urutan umum yang harus diutamakan adalah scope/konteks dulu, lalu filter, lalu `execute` terakhir.
*   Untuk action yang memakai `companyId`, letakkan `companyId` sebelum `search`.
*   `includeId` dianggap bagian dari blok filter, bukan blok eksekusi.
*   Urutan dan grouping ini wajib konsisten pada:
*       *   signature method `readAny`
*       *   daftar variable di closure `use (...)`
*       *   urutan filter `if (...)` di query
*       *   array `$cacheParams`
*   Action legacy/outlier berikut tidak boleh dirapikan ke pola ini kecuali diminta eksplisit:
*       *   `UserActions`
*       *   `RoleActions`
*       *   `PurchaseOrderProductUnitActions`
*       *   `PurchaseOrderDownPaymentApplyActions`
*       *   `PurchaseOrderDownPaymentActions`
*   **Alur Logika**:
    1.  **Build Query (Mandatory Filters)**:
        *   Inisialisasi query dengan filter wajib di level utama (bukan di dalam closure).
        *   Gunakan Scope/Trait helper jika tersedia (`whereCompanyId`, `whereBranchId`).
        *   **WithTrashed Pattern**: Gunakan pola deklaratif (withoutTrashed by default, override jika perlu). Gunakan *one-liner* `if` tanpa kurung kurawal agar ringkas.
            ```php
            // Correct (One-liner preferred)
            $query->withoutTrashed();
            if ($withTrashed) $query->withTrashed();
            ```
        *   **Joins**: Lakukan `join` eksplisit jika perlu melakukan filtering atau sorting berdasarkan kolom di tabel relasi (misal: `stock_adjustments.date`).
    2.  **Apply Conditional/Complex Filters**:
        *   Gunakan `where(function($q) { ... })` untuk search, filter spesifik, atau kondisi OR yang kompleks.
    3.  **Apply Sorting**:
        *   Gunakan `orderBy` yang relevan.
        *   **Deterministic Sorting**: SELALU tambahkan `orderBy('id', 'asc')` (atau desc) sebagai sorting terakhir untuk memastikan urutan data konsisten saat pagination, terutama jika sorting utama memiliki nilai yang sama.
        *   Contoh:
            ```php
            $query->orderBy('companies.name', 'asc')
                  ->orderBy('stock_adjustments.date', 'desc')
                  ->orderBy('stock_adjustment_in_products.id', 'asc'); // Final tie-breaker
            ```
    4.  **Execute Check**:
        *   Jika `$execute` adalah `null`, return `$query` (Query Builder) segera.
    5.  **Caching Logic** (Inside `if ($execute)`):
        *   Generate `$cacheKey` menggunakan `implode` array parameter eksplisit (HINDARI `json_encode` object/DTO).
            ```php
            $cacheParams = [
                $withTrashed ? 'true' : 'false',
                $companyId,
                $branchId ?? '[null]',
                ...
            ];
            $cacheKey = 'read_any_...'.implode('_', $cacheParams);
            ```
        *   **Check Cache**: Gunakan method `readFromCache` (dari `CacheHelper`).
        *   **Cache Hit Validation**: Bandingkan hasil dengan `Config::get('dcslab.ERROR_RETURN_VALUE')` untuk memastikan validitas cache (bukan sekadar `!is_null`).
            ```php
            if ($execute->useCache) {
                $cacheResult = $this->readFromCache($cacheKey);
                if ($cacheResult !== Config::get('dcslab.ERROR_RETURN_VALUE')) {
                    return $cacheResult;
                }
            }
            ```
    6.  **Pagination/Limit**:
        *   Handle `pagination` atau `limit` berdasarkan property di `ExecuteDTO`.
        *   **PENTING**: Properti `limit` berada di dalam objek `$execute->get`, BUKAN langsung di `$execute`.
            ```php
            if ($execute->pagination) {
                // ... paginate logic
            } else {
                if ($execute->get?->limit) {
                    $query->limit($execute->get->limit);
                }
                $result = $query->get();
            }
            ```
    7.  **Performance Logging**:
        *   Hitung `$recordsCount` dari hasil.
        *   Log performance dengan jumlah record: `$this->loggerPerformance(__METHOD__, $execution_time, $recordsCount);`.

### C. Method `read`
*   **Signature**: `public function read(Model $model): Model`
*   **Alur Logika**:
    *   Load relasi yang dibutuhkan: `return $model->load('relation1', 'relation2');`.
    *   Untuk modul transaksi stok yang menampilkan detail produk di frontend, relasi produk dan gambar wajib ikut di-load (contoh jalur: `productUnit.product.images`).

### F. Aturan Relasi `with` untuk Transaksi Stok
*   Pada method `readAny`:
    *   Relasi utama (`belongsTo`) yang sering dipakai UI harus selalu di-`with` (contoh: `stockAdjustment`, `productUnit`, `productUnit.unit`, `productUnit.product`, `productUnit.product.images`).
    *   Relasi `hasMany` yang berat hanya di-`with` saat pagination (`$execute?->pagination`) untuk efisiensi query.
    *   Pola ini wajib dipakai pada action transaksi stok seperti `StockAdjustmentActions`, `StockAdjustmentInProductActions`, `StockAdjustmentOutProductActions`, `StockAdjustmentInProductSerialActions`, dan `StockAdjustmentOutProductSerialActions`.
*   Pada method `read`:
    *   Gunakan `load([...])` yang lengkap untuk seluruh relasi yang dibutuhkan halaman detail, termasuk nested relation ke produk dan gambar.

### D. Method `update`
*   **Signature**: `public function update(Model $model, array $data): Model`
    *   *Catatan*: Gunakan **DTO** jika entitas kompleks.
*   **Alur Logika**:
    1.  Start timer & Try-Catch-Finally.
    2.  **Regenerate Code**: `$this->generateUniqueCode(..., $model->id)` (pastikan kirim ID untuk pengecualian unik).
    3.  **Assign Attributes**: Assign properti satu per satu (Style Property Assignment), BUKAN `update([...])`. Ini lebih eksplisit dan konsisten dengan method `create`.
        ```php
        $model->field1 = $data['field1'];
        $model->field2 = $data['field2'];
        // ...
        $model->save();
        ```
    4.  **Jangan Ubah Foreign Key Konteks yang Immutable**:
        *   Jika `company_id`, `branch_id`, atau foreign key konteks lain ditetapkan saat create dan secara bisnis tidak boleh berpindah konteks, maka field tersebut **tidak boleh** di-assign ulang pada method `update`.
        *   Untuk kebutuhan seperti generate unique code saat update, gunakan nilai yang sudah ada di model (contoh: `$model->company_id`), bukan dari payload update.
        *   Contoh yang benar:
            ```php
            $model->code = $this->generateUniqueCode($model->company_id, $data['code'], $model->id);
            $model->date = $data['date'];
            $model->remarks = $data['remarks'];
            $model->save();
            ```
    5.  **Style Kondisional Singkat**:
        *   Jika conditional hanya berisi satu statement pendek, utamakan *one-line if* tanpa kurung kurawal agar konsisten dengan style project pada action class.
        *   Cocok dipakai untuk sinkronisasi atau delete relasi internal yang sederhana.
        *   Contoh:
            ```php
            $cashTransaction = $model->cashTransaction;
            if ($cashTransaction) $this->cashTransactionActions->delete($cashTransaction);
            ```
    6.  `$this->flushCache()`.
    7.  Return `$model->refresh()`.

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
