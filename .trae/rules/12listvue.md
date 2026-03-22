---
alwaysApply: false
description: Standarisasi penulisan halaman List (EntityList.vue) di Frontend
---
# Standarisasi Halaman List (EntityList.vue)

Dokumen ini menjelaskan standar penulisan halaman `[Entity]List.vue` di frontend (`web/src/pages/[entity]/[Entity]List.vue`). Halaman List berfungsi untuk menampilkan data dengan fitur pagination, pencarian, dan aksi CRUD dasar.

## 1. Imports

Hindari import library berat yang tidak perlu. Untuk list bergaya kartu/responsif gunakan `DataListFlex`. Untuk list tabular klasik tetap boleh memakai `DataList`.

```typescript
import { computed, onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import { DataListFlex } from "@/components/DataList";
import Button from "@/components/Base/Button";
import Lucide from "@/components/Base/Lucide";
import { Dialog } from "@/components/Base/Headless";
// ... Import Service & Types terkait
```

## 2. State Management (Refs)

Gunakan struktur standar untuk menyimpan data list (Collection) dengan inisialisasi default yang aman (mencegah null reference di template).

```typescript
const selectedUserLocationStore = useSelectedUserLocationStore();
const { isUserLocationSelected } = storeToRefs(selectedUserLocationStore);

const deleteUlid = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null); // State untuk fitur expand row

// Inisialisasi struktur kosong untuk mencegah error 'undefined' saat loading awal
const lists = ref<Collection<Array<Entity>> | null>({
    data: [],
    meta: {
        current_page: 0,
        from: null,
        last_page: 0,
        path: "",
        per_page: 0,
        to: null,
        total: 0,
    },
    links: {
        first: "",
        last: "",
        prev: null,
        next: null,
    },
});
```

## 3. Lifecycle Hooks (onMounted)

Pada `onMounted`, lakukan validasi lokasi (jika perlu) dan load data awal.

```typescript
onMounted(async () => {
    emits("mode-state", ViewMode.LIST);

    // Validasi User Location (Context)
    if (!isUserLocationSelected.value) {
        router.push({
            name: "side-menu-error-code",
            params: { code: ErrorCode.USERLOCATION_REQUIRED },
        });
        return;
    }

    await getData("", true, 1, 10);
});
```

## 4. Data Loading Strategy

Method `getData` (atau `getProducts`, `getUsers`, dsb) adalah inti dari halaman List.

**PENTING: Error Handling Strategy & Parameter Ordering**
1. Saat data berhasil dimuat (`result.success`), kita **WAJIB** membersihkan alert placeholder (`hidden`) untuk menghapus pesan error yang mungkin muncul dari kegagalan request sebelumnya.
2. Parameter request harus diurutkan sesuai dengan definisi interface di TypeScript (misalnya `with_trashed` biasanya paling atas).
3. Definisikan tipe return `result` secara eksplisit untuk type safety: `ServiceResponse<Collection<Array<Entity>> | null>`.

```typescript
const getData = async (search: string, refresh: boolean, page: number, per_page: number) => {
    emits("loading-state", true);

    const requestParams: EntityReadAnyPaginateRequest = {
        with_trashed: false, // Pastikan urutan sesuai definisi Interface!
        company_id: selectedUserLocation.value.company.id,
        search: search,
        refresh: refresh,
        page: page,
        per_page: per_page,
        // ... parameter lain
    };

    let result: ServiceResponse<Collection<Array<Entity>> | null> = 
        await entityService.readAnyPaginate(requestParams);

    if (result.success && result.data) {
        lists.value = result.data;
        // CLEANUP: Sembunyikan alert error lama jika request sukses
        emits("show-alert-placeholder", {
            alertType: "hidden",
            title: "",
            alertList: null,
        });
    } else {
        // Tampilkan error jika request gagal
        emits("show-alert-placeholder", {
            alertType: "danger",
            title: "",
            alertList: result.errors,
        });
    }

    emits("loading-state", false);
};
```

## 5. Pagination & Search Handler

Gunakan handler standar dari komponen `DataList`.

```typescript
const handleDataListChange = async (data: DataListEmittedData) => {
    await getData(data.search.text, false, data.pagination.page, data.pagination.per_page);
};
```

## 6. Delete Strategy

Operasi delete harus menggunakan konfirmasi modal dan refresh data setelah sukses. Perhatikan detail berikut:
1. Panggil `emits("update-profile")` jika delete berhasil (untuk update state global jika perlu).
2. Refresh data list (`await getData(...)`).
3. Tampilkan notifikasi sukses.
4. Jangan panggil `showAlertPlaceholder` tipe 'hidden' saat sukses (tidak perlu).
5. Casting `result.errors` ke `Record<string, Array<string>>` saat menampilkan error.
6. Definisikan tipe return `result` secara eksplisit: `ServiceResponse<boolean | null>`.

```typescript
const confirmDelete = async () => {
    deleteModalShow.value = false;
    emits("loading-state", true);

    const result: ServiceResponse<boolean | null> = await entityService.delete(
        deleteUlid.value
    );

    if (result.success) {
        emits("update-profile"); // PENTING: Update global state
        await getData("", true, 1, 10);
        
        emits("show-notification", {
            title: t("views.entity.alert.delete.title"),
            content: t("views.entity.alert.delete.message"),
        });
    } else {
        emits("show-alert-placeholder", {
            alertType: "danger",
            title: "",
            alertList: result.errors as Record<string, Array<string>>, // Type Assertion
        });
    }

    emits("loading-state", false);
};
```

## 7. View Detail Strategy (Expandable Row)

Untuk menampilkan detail data dalam baris yang bisa di-expand (toggle):
1. Gunakan state `expandDetail` (number | null) untuk menyimpan index baris yang sedang terbuka.
2. Buat method `viewSelected` untuk handle logic toggle.

```typescript
const viewSelected = (idx: number) => {
  if (expandDetail.value === idx) {
    expandDetail.value = null;
  } else {
    expandDetail.value = idx;
  }
};
```

## 8. Edit Navigation

Saat user mengklik tombol Edit, gunakan `router.push` untuk navigasi ke halaman form edit, bukan menggunakan event emit. 

**PENTING**: 
- Pastikan nama route (`name`) sama persis dengan yang terdaftar di `web/src/router/routes.ts`.
- Jangan menebak nama module (misal: jangan pakai `purchase` jika route sebenarnya ada di bawah group `supplier` -> `side-menu-supplier-supplier-edit`).

```typescript
const editSelected = (idx: number) => {
    if (!lists.value) return;
    const ulid = lists.value.data[idx].ulid;
    router.push({ 
        name: "side-menu-[module]-[entity]-edit", 
        params: { ulid: ulid } 
    });
};
```

## 9. Template Structure (DataListFlex)

Gunakan komponen `DataListFlex` sebagai wrapper utama untuk list produk dan list stock adjustment karena lebih konsisten di desktop/mobile.

**Aturan Penting untuk pola Stock Adjustment Product List**:
- Layout row wajib memakai grid `12 columns` dengan pemisahan blok: image, stock adjustment, product, product unit/serial, action.
- Setiap blok informasi memakai judul section uppercase (`text-primary text-xs font-semibold uppercase tracking-wide`).
- Detail value memakai `text-slate-500 text-xs`, dan field yang panjang (misal serial) wajib `break-all`.
- Aksi delete berada di kolom paling kanan (`lg:col-span-1`) dengan tombol outline-secondary + icon trash.

```html
<DataListFlex
    :data="lists"
    :enable-search="true"
    :can-print="true"
    :can-export="true"
    :rows="lists?.data ?? []"
    row-class="bg-white dark:bg-darkmode-600"
    :pagination="lists ? lists.meta : null"
    @dataListChanged="handleDataListChange"
>
    <template #row="{ item, index }">
        <div class="col-span-12 lg:col-span-1 md:col-span-12 flex items-center justify-center md:justify-start">
            <!-- image -->
        </div>
        <div class="col-span-12 lg:col-span-4 md:col-span-5 self-start">
            <!-- stock adjustment info -->
        </div>
        <div class="col-span-12 lg:col-span-3 md:col-span-4 self-start">
            <!-- product info -->
        </div>
        <div class="col-span-12 lg:col-span-3 md:col-span-3 self-start">
            <!-- product unit / serial info -->
        </div>
        <div class="col-span-12 lg:col-span-1 md:col-span-12 flex justify-end items-center gap-2">
            <Button size="sm" variant="outline-secondary" @click="deleteSelected(index)">
                <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
            </Button>
        </div>
    </template>
</DataListFlex>
```

## 10. Standard Translation Keys

Pastikan file translation (`web/src/lang/[lang]/views/[entity].json`) memiliki key standar berikut agar konsisten dengan komponen:

```json
{
    "page_title": "Entity Name",
    "table": {
        "title": "Entity List",
        "cols": {
            "code": "Code",
            "name": "Name",
            "status": "Status"
            // ... column lain
        }
    },
    "alert": {
        "delete": {
            "title": "Delete Entity",
            "message": "Entity Successfully Deleted"
        }
    }
}
```

Juga gunakan translation global untuk komponen umum:
- `t("components.data-list.data_not_found")`
- `t("components.delete-modal.title")`
- `t("components.buttons.delete")`
- `t("components.dropdown.values.statusDDL.active")`
