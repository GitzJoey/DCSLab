---
alwaysApply: false
description: Standarisasi penulisan Controller untuk modul Master Data, mencakup struktur method, handling request, cache strategy, dan exception handling.
---
# Controller Standardization Rules (Master Data)

Aturan ini berlaku untuk semua Controller di bawah menu Master Data (e.g., Company, Branch, Warehouse, Investor, dll).

## 1. General Structure & Dependencies
- **Inheritance**: Semua controller wajib mewarisi `App\Http\Controllers\BaseController`.
- **Dependency Injection**: Gunakan Constructor Injection untuk memanggil Action Class.
- **Common Imports**:
  - `App\DTOs\ExecuteDTO`, `ExecuteGetDTO`, `ExecutePaginationDTO`
  - `App\Helpers\HashidsHelper`
  - `App\Http\Resources\<Entity>Resource`
  - `Illuminate\Support\Facades\Auth`
  - `Illuminate\Support\Facades\DB`
  - `Exception`

## 2. CRUD Methods Standard

### A. Method `store(StoreRequest $request)`
1.  **Validation**: Gunakan dedicated FormRequest class. Ambil data dengan `$request->validated()`.
2.  **Transaction**: Bungkus logika dalam `try-catch` block dengan `DB::beginTransaction()`, `DB::commit()`, dan `DB::rollBack()`.
3.  **Unique Validation**: Lakukan validasi unik manual (Code/Name) memanggil method Action (`isUniqueCode`/`isUniqueName`).
    - *Return*: `response()->error(['field' => [trans('rules.unique_...')]], 422)` jika gagal.
4.  **Action Execution**: Panggil method `create` pada Action Class.
    - *Input*: Kirimkan `array` data (Default).
5.  **Response**:
    - Success: `response()->success()`
    - Failure: `response()->error($errorMsg)`

### B. Method `readAny(Request $request)`
1.  **Auth & Authorization**: Wajib cek `Auth::check()` dan `$this->authorize('viewAny', Model::class)`.
2.  **Input Handling**:
    - Decode Hashids (e.g., `company_id`, `include_id`) sebelum validasi.
    - Validasi inline menggunakan `$request->validate([...])`.
    - **Urutan Parameter Validasi Wajib**:
      1. `with_trashed` (boolean)
      2. `company_id` (integer)
      3. `search` (string)
      4. ... (Filter spesifik lainnya)
      5. `refresh` (boolean)
      6. `paginate` (array)
      7. `get` (array)
3.  **Cache Strategy**:
    - Logic: `useCache` harus bernilai kebalikan dari request `refresh`.
    - Syntax: `useCache: ! $validatedRequest['refresh']`.
4.  **Pagination/Get Logic**:
    - Gunakan *Immediately Invoked Function Expression (IIFE)* atau closure untuk memisahkan logika `ExecutePaginationDTO` dan `ExecuteGetDTO`.
5.  **Response**: `Resource::collection($result)`.

### C. Method `read(Model $model)`
1.  **Authorization**: `$this->authorize('view', $model)`.
2.  **Execution**: Panggil method `read` pada Action Class.
3.  **Response**: `new Resource($result)`.

### D. Method `update(Model $model, UpdateRequest $request)`
1.  **Flow**: Mirip dengan `store`.
2.  **Unique Validation**: Sertakan ID model saat ini untuk pengecualian (`ignore current id`).
3.  **Action Execution**: Panggil method `update` pada Action Class.

### E. Method `delete(Model $model)`
1.  **Authorization**: `$this->authorize('delete', $model)`.
2.  **Transaction**: Wajib menggunakan DB Transaction.
3.  **Business Rules**: Cek validasi bisnis sebelum delete (e.g., `isDefault`).

## 3. Exceptions & Special Cases

### A. Data Transfer Object (DTO) vs Array
- **Default**: Gunakan **Array** (`$validatedRequest`) untuk mengirim data ke Action Class.
- **DTO Usage Criteria**: Gunakan **DTO** hanya jika:
  1.  Struktur data/kolom sangat rumit (complex columns).
  2.  Data object tersebut digunakan kembali di banyak tempat (reusability).
- **Contoh**: `ProductController` menggunakan `ProductPhysicalCreateDTO` karena kompleksitas atribut produk fisik.

### B. Method Naming
- Jika Controller menangani multiple tipe entitas (seperti **Product** yang memisahkan Physical dan Service), penamaan method boleh spesifik:
  - `storePhysical`, `storeService`
  - `updatePhysical`

### C. Helper Methods
- Method tambahan seperti `getTypes()` diperbolehkan untuk mengembalikan Enum/Konstanta ke frontend.
