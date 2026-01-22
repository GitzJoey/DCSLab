---
alwaysApply: false
description: 
---
# Standarisasi Service Frontend (Service.ts)

Dokumen ini menjelaskan standar penulisan file Service di frontend (`web/src/services/`) untuk menjaga konsistensi, type safety, dan kemudahan maintenance.

## 1. Struktur Dasar Class
Setiap Service class harus:
- Di-export sebagai `default`.
- Menginjeksi `ZiggyRouteStore` untuk manajemen route.
- Menginjeksi `ErrorHandlerService` untuk penanganan error yang seragam.
- Memiliki properti `ziggyRoute` dan `errorHandlerService`.

```typescript
import axios from "../axios";
import { useZiggyRouteStore } from "../stores/ziggy-route";
import { route, Config } from "ziggy-js";
import { ServiceResponse } from "../types/services/ServiceResponse";
import ErrorHandlerService from "./ErrorHandlerService";
// ... imports lainnya

export default class ExampleService {
    private ziggyRoute: Config;
    private ziggyRouteStore = useZiggyRouteStore();
    private errorHandlerService;

    constructor() {
        this.ziggyRoute = this.ziggyRouteStore.getZiggy;
        this.errorHandlerService = new ErrorHandlerService();
    }
    // ... methods
}
```

## 2. Penamaan Method (Naming Convention)
Gunakan nama method berikut untuk operasi standar CRUD:

| Operasi | Nama Method | Signature |
|---------|-------------|-----------|
| Read Paginated | `readAnyPaginate` | `(args: RequestType): Promise<ServiceResponse<Collection<Array<Model>> \| null>>` |
| Read List (No Pagination) | `readAnyGet` | `(args: RequestType): Promise<ServiceResponse<Resource<Array<Model>> \| null>>` |
| Read Single | `read` | `(ulid: string): Promise<ServiceResponse<Model \| null>>` |
| Delete | `delete` | `(ulid: string): Promise<ServiceResponse<boolean \| null>>` |
| Form Create | `use[Entity]CreateForm` | `(): Form<...>` |
| Form Edit | `use[Entity]EditForm` | `(ulid: string): Form<...>` |

## 3. Penanganan Parameter Request (Query Params)
Pastikan parameter diproses dengan benar sebelum dikirim ke API:

### a. Boolean
Jangan mengonversi boolean ke `1` atau `0` secara manual. Kirimkan nilai boolean asli jika API mendukungnya, atau biarkan handling di backend.
**JANGAN:** `queryParams["active"] = args.active ? 1 : 0;`
**LAKUKAN:**
```typescript
if (args.active !== undefined) queryParams['active'] = args.active;
```

### b. Search & Optional Strings
Jangan mengirim string kosong (`""`) untuk parameter opsional seperti `search`. Cek keberadaan nilai terlebih dahulu.
**JANGAN:** `queryParams["search"] = args.search ? args.search : "";`
**LAKUKAN:**
```typescript
if (args.search) queryParams['search'] = args.search;
```

### c. Conditional Parameters
Hanya masukkan parameter ke `queryParams` jika nilainya ada (tidak `undefined` atau `null`).
```typescript
if (args.status) queryParams['status'] = args.status;
if (args.company_id) queryParams['company_id'] = args.company_id;
```

## 4. Return Types & Response Handling
Selalu gunakan tipe data yang eksplisit.

### Delete Method
Method `delete` harus mengembalikan `ServiceResponse<boolean | null>`, bukan `any` atau `void`.

```typescript
public async delete(ulid: string): Promise<ServiceResponse<boolean | null>> {
    const result: ServiceResponse<boolean | null> = { success: false };
    try {
        // ... request
        if (response.status == StatusCode.OK) {
            result.success = true;
        }
        return result;
    } catch (e: unknown) {
        // error handling
    }
}
```

## 5. Error Handling
Gunakan pola `try-catch` standar dengan `ErrorHandlerService`.

```typescript
try {
    // ... axios call
} catch (e: unknown) {
    if (e instanceof Error && e.message.includes('Ziggy error')) {
        return this.errorHandlerService.generateZiggyUrlErrorServiceResponse(e.message);
    } else if (isAxiosError(e)) {
        return this.errorHandlerService.generateAxiosErrorServiceResponse(e as AxiosError);
    } else {
        return result;
    }
}
```

## 6. Form Handling (Laravel Precognition)
Untuk form Create dan Edit, gunakan helper `client` dan `useForm` dari `laravel-precognition-vue`. Pastikan credentials dan CSRF token di-set.

```typescript
public useExampleCreateForm() {
    const url = route('api.post.example.save', undefined, true, this.ziggyRoute);

    client.axios().defaults.withCredentials = true;
    client.axios().defaults.withXSRFToken = true;
    
    const form = useForm('post', url, {
        code: '_AUTO_',
        name: '',
        status: 'ACTIVE',
        // ... fields lainnya
    });

    return form;
}
```

## 7. Import Order
Urutkan import untuk keterbacaan:
1. Library eksternal (`axios`, `ziggy-js`, `laravel-precognition-vue`)
2. Stores (`pinia` stores)
3. Types (`models`, `resources`, `services`, `enums`)
4. Internal Services (`ErrorHandlerService`, `CacheService`)
