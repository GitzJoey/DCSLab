---
alwaysApply: false
description: 
---
# Standarisasi Request.ts (Frontend Type Definitions)

Aturan ini mengatur standar penulisan file definisi tipe TypeScript (`Request.ts`) untuk layanan frontend di direktori `web/src/types/services`.

## 1. Penamaan File dan Lokasi
- **Lokasi**: `web/src/types/services/{module}/{Module}Request.ts`
- **Penamaan File**: `{Module}Request.ts` (PascalCase).
- **Contoh**: `web/src/types/services/branch/BranchRequest.ts`

## 2. Struktur Interface
Setiap file `Request.ts` umumnya harus memiliki interface berikut:

### 2.1. ReadAnyPaginateRequest
Digunakan untuk request daftar data dengan paginasi.

**Format Penamaan**: `export interface {Module}ReadAnyPaginateRequest`

**Urutan Property (Wajib Dipatuhi):**
1.  `with_trashed: boolean;` (Wajib, paling atas)
2.  `company_id: string;` (Jika modul scope company)
3.  `branch_id?: string | null;` (Jika modul scope branch)
4.  `search?: string | null;` (Pencarian umum)
5.  `...filters` (Filter spesifik lain, misal `branch_id`, `status`, `type`, `include_id`, dll)
    - **Catatan**: Urutan filter harus sesuai dengan urutan `$fillable` pada Model terkait di backend.
6.  `refresh: boolean;` (Wajib)
8.  `page: number;` (Wajib)
9.  `per_page: number;` (Wajib)

**Contoh:**
```typescript
export interface BranchReadAnyPaginateRequest {
    with_trashed: boolean;
    company_id: string;
    search?: string | null;
    is_main?: boolean;
    status?: string | number;
    include_id?: string;
    refresh: boolean;
    page: number;
    per_page: number;
}
```

### 2.2. ReadAnyGetRequest
Digunakan untuk request daftar data tanpa paginasi penuh (biasanya dengan limit).

**Format Penamaan**: `export interface {Module}ReadAnyGetRequest`

**Urutan Property:**
Mirip dengan `PaginateRequest`, namun mengganti `page` dan `per_page` dengan `limit`.

1.  `with_trashed: boolean;`
2.  `company_id: string;`
3.  `search?: string | null;`

4.  `...` (Property scope & filter sama seperti Paginate)
    - **Catatan**: Urutan filter wajib mengikuti urutan `$fillable` pada Model terkait di backend.
5.  `refresh: boolean;`
6.  `limit: number;`

**Contoh:**
```typescript
export interface BranchReadAnyGetRequest {
    with_trashed: boolean;
    company_id: string;
    search?: string | null;
    is_main?: boolean;
    status?: string | number;
    include_id?: string;
    refresh: boolean;
    limit: number;
}
```

### 2.3. StoreRequest & UpdateRequest
Digunakan untuk payload Create dan Edit.

**Format Penamaan**:
- `export interface {Module}StoreRequest`
- `export interface {Module}UpdateRequest`

**Aturan:**
- Sesuaikan dengan field yang dibutuhkan API.
- Gunakan tipe yang tepat (`string`, `number`, `boolean`).
- Property opsional ditandai dengan `?`.

## 3. Konvensi Tipe Data
- **Search**: `search?: string | null;`
- **Status**: `status?: string | number;` (Mengakomodasi status berupa kode string atau angka enum)
- **ID Fields**: `string` (Karena menggunakan Hashids)
- **Boolean Flags**: `boolean` (bukan number 0/1)

## 4. Urutan Filter (Wajib)
Semua filter tambahan (selain `company_id`, `branch_id`, dan `search`) **WAJIB** diurutkan berdasarkan urutan properti `$fillable` pada Model Eloquent terkait di backend. Hal ini untuk memastikan konsistensi antara Frontend dan Backend validation logic.

## 5. Referensi
Selalu pastikan interface ini sinkron dengan parameter yang diharapkan oleh Controller API di sisi backend (lihat `06controller.md` untuk urutan parameter di backend, meskipun di frontend kita mengirim object JSON/Query param, menjaga konsistensi penamaan sangat penting).
