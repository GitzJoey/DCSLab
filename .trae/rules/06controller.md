---
alwaysApply: false
description: Standarisasi penulisan Controller untuk modul Master Data, mencakup struktur method, handling request, cache strategy, dan exception handling.
---
# Controller Standardization Rules (Master Data)

Aturan ini berlaku untuk semua Controller di bawah menu Master Data (e.g., Company, Branch, Warehouse, Investor, dll).

## 1. General Structure & Dependencies
- **Inheritance**: Semua controller wajib mewarisi `App\Http\Controllers\BaseController`.
- **Dependency Injection**: Gunakan Constructor Injection untuk memanggil Action Class.
- **Penamaan Properti DI**: Nama properti mengikuti nama class Action dalam camelCase, contoh:
  - `StockAdjustmentActions` → `$stockAdjustmentActions`
  - `StockAdjustmentInProductActions` → `$stockAdjustmentInProductActions`
  - `StockAdjustmentOutProductActions` → `$stockAdjustmentOutProductActions`
- **Common Imports**:
  - `App\DTOs\ExecuteDTO`, `ExecuteGetDTO`, `ExecutePaginationDTO`
  - `App\Helpers\HashidsHelper`
  - `App\Http\Resources\<Entity>Resource`
  - `Illuminate\Support\Facades\Auth`
  - `Illuminate\Support\Facades\DB`
  - `Exception`

## 2. CRUD Methods Standard

### 2.1 Urutan Method Public
Untuk semua controller CRUD di bawah menu Master Data, urutan method public utama harus menggunakan pola berikut:

1. `readAny`
2. `read`
3. `store`
4. `update`
5. `delete`

### A. Method `store(StoreRequest $request)`
1.  **Validation**: Gunakan dedicated FormRequest class (e.g., `StoreSupplierRequest` atau `SupplierStoreRequest`).
    - **Pemisahan Request**: Wajib memisahkan Request untuk Store dan Update untuk menjaga *Single Responsibility*.
    - Ambil data dengan `$request->validated()`.
2.  **Transaction**: Bungkus logika dalam `try-catch` block dengan `DB::beginTransaction()`, `DB::commit()`, dan `DB::rollBack()`.
3.  **Unique Validation**: Lakukan validasi unik manual (Code/Name) memanggil method Action (`isUniqueCode`/`isUniqueName`).
    - **Guard Clause**: Gunakan *One-line Guard Clause* untuk pengecekan validasi.
    - *Syntax*: `if (! $isUnique) return response()->error(['field' => [trans('rules.unique_...')]], 422);`
4.  **Action Execution**: Panggil method `create` pada Action Class.
    - *Input*: Kirimkan `array` data (Default).
5.  **Response**:
    - Success: `response()->success()`
    - Failure: `response()->error($errorMsg)`
    - **Formatting**: Gunakan ternary operator untuk return response.
      `return is_null($result) ? response()->error($errorMsg) : response()->success();`

### B. Method `readAny(Request $request)`
1.  **Auth & Authorization**:
    - **Guard Clause**: Wajib cek `Auth::check()` dengan *One-line Guard Clause*.
      `if (! Auth::check()) return response()->error(trans('auth.unauthenticated'), 401);`
    - Lanjutkan dengan `$this->authorize('viewAny', Model::class);`.
2.  **Input Handling**:
    - Gunakan `Illuminate\Http\Request` biasa, bukan FormRequest khusus.
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
5.  **Grouping Named Argument `readAny` (Wajib 3 Blok untuk Stock Adjustment Product)**:
    - Pada pemanggilan Action `readAny`, susun named argument menjadi 3 blok dengan linebreak:
      1. Blok basis: `withTrashed`, `companyId`, `branchId`, `search`
      2. Blok filter: `stockAdjustmentId`, `stockAdjustmentStartDate`, `stockAdjustmentEndDate`, `stockAdjustmentCategoryId`, `stockAdjustmentInWarehouseId`, `stockAdjustmentOutWarehouseId`, `product_unit_code`, `product_name`, `product_category_id`, `product_brand_id`
      3. Blok eksekusi: `execute`
    - Terapkan konsisten pada controller:
      - `StockAdjustmentInProductController`
      - `StockAdjustmentOutProductController`
      - `StockAdjustmentInProductSerialController`
      - `StockAdjustmentOutProductSerialController`
6.  **Response**: `Resource::collection($result)`.
    - Gunakan *Early Return* jika hasil null.
      ```php
      if (is_null($result)) {
          return response()->error($errorMsg);
      }
      return Resource::collection($result);
      ```

### C. Method `read(Model $model)`
1.  **Auth & Authorization**:
    - **Guard Clause**: Wajib cek `Auth::check()` dengan *One-line Guard Clause* (sama seperti `readAny`).
    - Lanjutkan dengan `$this->authorize('view', $model);`.
2.  **Execution**: Panggil method `read` pada Action Class.
3.  **Response**: `new Resource($result)`.
    - Gunakan *Early Return* jika hasil null (sama seperti `readAny`).

### D. Method `update(Model $model, UpdateRequest $request)`
1.  **Validation**: Gunakan dedicated FormRequest class (e.g., `UpdateSupplierRequest` atau `SupplierUpdateRequest`).
2.  **Flow**: Mirip dengan `store`.
3.  **Unique Validation**: Sertakan ID model saat ini untuk pengecualian (`ignore current id`).
    - Gunakan *One-line Guard Clause* untuk pengecekan validasi.
4.  **Action Execution**: Panggil method `update` pada Action Class.
5.  **Response**:
    - Gunakan ternary operator untuk return response (sama seperti `store`).

### E. Method `delete(Model $model)`
1.  **Auth & Authorization**:
    - **Guard Clause**: Wajib cek `Auth::check()` dengan *One-line Guard Clause*.
    - Lanjutkan dengan `$this->authorize('delete', $model);`.
2.  **Transaction**: Wajib menggunakan DB Transaction.
3.  **Business Rules**: Cek validasi bisnis sebelum delete (e.g., `isDefault`).
4.  **Response**:
    - Gunakan ternary operator untuk return response.
      `return ! $result ? response()->error($errorMsg) : response()->success();`

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

## 4. Form Request Standard

### A. Prepare For Validation
1.  **Use `filled()` over `has()`**:
    - Saat melakukan merge input di `prepareForValidation`, gunakan method `$this->filled('key')` daripada `$this->has('key')`.
    - **Alasan**: `filled()` memastikan nilai tidak hanya *exist* tapi juga tidak kosong/null string. Ini mencegah error decoding pada Hashids dan memastikan sanitasi data (string kosong menjadi null).
    - **Contoh**:
      ```php
      protected function prepareForValidation()
      {
          $this->merge([
              // Decode Hashids hanya jika terisi
              'company_id' => $this->filled('company_id') ? HashidsHelper::decodeId($this->company_id) : null,
              // Sanitasi string kosong menjadi null
              'address' => $this->filled('address') ? $this['address'] : null,
          ]);
      }
      ```

### B. Transaksional Dengan Child (Parent + Detail)

- Untuk modul transaksional yang saat simpan/update juga menyimpan child/detail (misal: StockAdjustment dengan in_products/out_products):
  - Wajib menggunakan FormRequest terpisah untuk `store` dan `update` khusus transaksi tersebut.
  - Struktur rules untuk child harus dipusatkan di class khusus di namespace `App\Validation\<Domain>\`, lalu dipetakan ke nested field di FormRequest.
    - Contoh format di FormRequest:
      ```php
      $rules['in_products'] = ['nullable', 'array'];
      $rules += StockAdjustmentInProductRules::mapToFieldNames($this->company_id ?? 0,
          'in_products.*.qty',
          'in_products.*.product_unit_id',
          'in_products.*.product_unit_conversion_value',
          'in_products.*.product_unit_cogs',
          'in_products.*.remarks',
      );

      $rules['out_products'] = ['nullable', 'array'];
      $rules += StockAdjustmentOutProductRules::mapToFieldNames($this->company_id ?? 0,
          'out_products.*.qty',
          'out_products.*.product_unit_id',
          'out_products.*.product_unit_conversion_value',
          'out_products.*.remarks',
      );
      ```
  - Pola di atas memastikan:
    - Satu sumber kebenaran untuk rules child.
    - Jika aturan field child berubah, FormRequest akan ikut terdampak (mencegah duplikasi rules tersebar di banyak tempat).

### B. Authorization
1.  **Check Auth First**: Selalu cek `Auth::check()` terlebih dahulu.
2.  **Gate Policy**: Gunakan `$user->can()` untuk memanggil Policy yang sesuai.
    - **Contoh**:
      ```php
      public function authorize()
      {
          if (! Auth::check()) {
              return false;
          }
          /** @var \App\User */
          $user = Auth::user();
          return $user->can('create', Supplier::class);
      }
      ```
