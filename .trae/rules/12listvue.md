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
const deleteUlid = ref<string>("");
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null); // Jika ada fitur expand row

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

**PENTING: Error Handling Strategy**
Saat data berhasil dimuat (`result.success`), kita **WAJIB** membersihkan alert placeholder (`hidden`) untuk menghapus pesan error yang mungkin muncul dari kegagalan request sebelumnya (misal: koneksi putus lalu nyambung lagi).

```typescript
const getData = async (search: string, refresh: boolean, page: number, per_page: number) => {
    emits("loading-state", true);

    const requestParams: EntityReadAnyPaginateRequest = {
        company_id: selectedUserLocation.value.company.id,
        search: search,
        refresh: refresh,
        page: page,
        per_page: per_page,
        // ... parameter lain
    };

    let result = await entityService.readAnyPaginate(requestParams);

    if (result.success && result.data) {
        lists.value = result.data;
        // CLEANUP: Sembunyikan alert error lama jika request sukses
        showAlertPlaceholder("hidden", "", null);
    } else {
        // Tampilkan error jika request gagal
        showAlertPlaceholder("danger", "", result.errors as Record<string, Array<string>>);
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

Operasi delete harus menggunakan konfirmasi modal dan refresh data setelah sukses.

```typescript
const confirmDelete = async () => {
    deleteModalShow.value = false;
    emits("loading-state", true);

    let result = await entityService.delete(deleteUlid.value);

    emits("loading-state", false);

    if (result.success) {
        // Refresh data setelah delete
        await getData("", true, 1, 10);
        
        // CLEANUP: Sembunyikan alert error lama
        showAlertPlaceholder("hidden", "", null);
        
        showNotification(t("views.entity.alert.delete.title"), t("views.entity.alert.delete.message"));
    } else {
        showAlertPlaceholder("danger", "", result.errors as Record<string, Array<string>>);
    }
};
```

## 7. Template Structure (DataList & Table)

Gunakan komponen `DataList` sebagai wrapper utama untuk menangani search bar, pagination, dan export buttons.

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
                
                <!-- Data Rows -->
                <template v-for="(item, idx) in lists.data" :key="item.ulid">
                    <Table.Tr>
                        <!-- Columns -->
                        <Table.Td>
                            <!-- Actions -->
                            <Button @click="editSelected(idx)">Edit</Button>
                        </Table.Td>
                    </Table.Tr>
                </template>
            </Table.Tbody>
        </Table>
    </template>
</DataList>
```
