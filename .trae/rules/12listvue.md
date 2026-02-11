---
alwaysApply: false
description: Standarisasi penulisan halaman List (EntityList.vue) di Frontend
---
# Standarisasi Halaman List (EntityList.vue)

Dokumen ini menjelaskan standar penulisan halaman `[Entity]List.vue` di frontend (`web/src/pages/[entity]/[Entity]List.vue`). Halaman List berfungsi untuk menampilkan data tabel dengan fitur pagination, pencarian, dan aksi CRUD dasar.

## 1. Imports

Hindari import library berat yang tidak perlu. Gunakan komponen standar `DataList` untuk wrapper tabel.

```typescript
import { computed, onMounted, ref } from "vue";
import { useI18n } from "vue-i18n";
import { useRouter } from "vue-router";
import { storeToRefs } from "pinia";
import DataList from "@/components/DataList";
import Button from "@/components/Base/Button";
import Lucide from "@/components/Base/Lucide";
import Table from "@/components/Base/Table";
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

## 9. Template Structure (DataList & Table)

Gunakan komponen `DataList` sebagai wrapper utama.

**Aturan Penting untuk Expandable Rows**:
- Baris detail (`<Table.Tr>` untuk detail) **HARUS** berada di dalam loop `v-for`, tepat di bawah baris utama data.
- Jangan meletakkan baris detail di luar loop atau di dalam blok `v-if` empty state.

```html
<DataList
    :title="t('views.entity.table.title')"
    :data="lists"
    :enable-search="true"
    :pagination="lists ? lists.meta : null"
    @dataListChanged="handleDataListChange"
>
    <template #content>
        <Table class="mt-5" :hover="true">
            <Table.Thead variant="light">
                <!-- Header Columns -->
            </Table.Thead>
            <Table.Tbody v-if="lists !== null">
                <!-- Empty State -->
                <template v-if="lists.data.length === 0">
                    <Table.Tr>
                        <Table.Td colspan="5" class="text-center italic">
                            {{ t("components.data-list.data_not_found") }}
                        </Table.Td>
                    </Table.Tr>
                </template>
                
                <!-- Data Rows Loop -->
                <template v-for="(item, itemIdx) in lists.data" :key="item.ulid">
                    <!-- Main Row -->
                    <Table.Tr class="intro-x">
                        <!-- Columns -->
                        <Table.Td>
                            <!-- Actions -->
                            <div class="flex justify-end gap-1">
                                <Button variant="outline-secondary" @click="viewSelected(itemIdx)">
                                    <Lucide icon="Info" />
                                </Button>
                                <Button variant="outline-secondary" @click="editSelected(itemIdx)">
                                    <Lucide icon="Pen" />
                                </Button>
                            </div>
                        </Table.Td>
                    </Table.Tr>

                    <!-- Expandable Detail Row (Must be INSIDE v-for) -->
                    <Table.Tr :class="{ 'intro-x': true, 'hidden transition-all': expandDetail !== itemIdx }">
                        <Table.Td colspan="5">
                             <!-- Detail Content Here -->
                             <div class="flex flex-row">
                                <div class="ml-5 w-48 text-right pr-5">{{ t('views.entity.fields.name') }}</div>
                                <div class="flex-1">{{ item.name }}</div>
                             </div>
                        </Table.Td>
                    </Table.Tr>
                </template>
            </Table.Tbody>
        </Table>
    </template>
</DataList>
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
