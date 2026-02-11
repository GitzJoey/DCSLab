---
alwaysApply: false
description: Standarisasi penulisan halaman Edit (EntityEdit.vue) di Frontend
---
# Standarisasi Halaman Edit (EntityEdit.vue)

Dokumen ini menjelaskan standar penulisan halaman `[Entity]Edit.vue` di frontend (`web/src/pages/[entity]/[Entity]Edit.vue`). Standar ini mirip dengan `EntityCreate.vue` namun dengan perbedaan kunci pada **Data Loading** dan **Caching Strategy**.

## 1. Imports

Sama seperti halaman Create, jangan mengimpor `axios` secara default. Gunakan `isAxiosError` dan `AxiosError` untuk error handling.

**JANGAN:** `import axios from "axios";`
**LAKUKAN:**
```typescript
import { isAxiosError, AxiosError } from "axios";
import { useRoute, useRouter } from "vue-router";
// ... imports lainnya
```

## 2. Struktur & Layout

Gunakan `TwoColumnsLayout` dengan definisi `cards` state yang sama.

```typescript
const cards = ref<Array<TwoColumnsLayoutCards>>([
    // ... definisi cards
    { title: "views.entity.field_groups.group_1", state: CardState.Expanded, id: "group1" },
    { title: "", state: CardState.Hidden, id: "button" },
]);
```

**Note:** Pastikan mengimpor `CardState` dari path yang benar.
```typescript
import { CardState } from "@/types/enums/CardState";
```

### Form Identifier

**WAJIB** memberikan `id` pada tag `<form>` dengan format `[entity]Form`.
Ini berguna untuk keperluan testing atau styling spesifik jika dibutuhkan.

```html
<form id="supplierForm" @submit.prevent="onSubmit">
    <!-- ... -->
</form>
```

## 3. Lifecycle Hooks (onMounted)

Berbeda dengan Create, halaman Edit **TIDAK** memuat data dari cache saat `onMounted`. Sebaliknya, ia harus memuat data terbaru dari server.

Selain itu, **WAJIB** melakukan validasi apakah user sudah memilih lokasi (`isUserLocationSelected`). Jika belum, redirect ke halaman error.

**Imports yang Diperlukan:**
```typescript
import { storeToRefs } from "pinia";
import { useSelectedUserLocationStore } from "@/stores/useSelectedUserLocationStore";
import { ErrorCode } from "@/types/enums/ErrorCode";
```

**Setup Store:**
```typescript
const selectedUserLocationStore = useSelectedUserLocationStore();
const { isUserLocationSelected } = storeToRefs(selectedUserLocationStore);
```

**Urutan yang Direkomendasikan:**
1. Validasi lokasi user (`isUserLocationSelected`).
2. Emit mode view (`ViewMode.FORM_EDIT`).
3. Load DDL secara paralel.
4. Load Data Entitas dari server (`loadData()`).

```typescript
onMounted(async () => {
    // 1. Validasi lokasi user
    if (!isUserLocationSelected.value) {
        router.push({ name: 'side-menu-error-code', params: { code: ErrorCode.USERLOCATION_REQUIRED } });
        return;
    }

    emits("mode-state", ViewMode.FORM_EDIT);
    
    // 2. Load DDL Paralel
    await Promise.all([
        getCategoryDDL(),
        getUnitDDL(),
        getStatusDDL()
    ]);

    // 3. Load Data Server
    await loadData(route.params.ulid as string);
});
```

## 4. Data Loading Strategy

Method `loadData` bertanggung jawab mengambil data dari API dan mengisi form.

**Penting:**
- Gunakan `route.params.ulid` atau parameter ID yang sesuai.
- Handle kasus 404/Not Found dengan redirect.
- Pastikan array dinamis terisi minimal satu item default jika kosong (opsional, tergantung bisnis logic).

```typescript
const loadData = async () => {
    emits("loading-state", true);
    const result = await entityService.read(route.params.ulid.toString());
    emits("loading-state", false);

    if (result.success && result.data) {
        form.setData({
            // Mapping data server ke form structure
            code: result.data.code,
            name: result.data.name,
            details: result.data.details.map((d: any) => ({
                id: d.id,
                // ... map detail fields
            })),
        });

        // Optional: Ensure at least one detail exists
        if (form.details.length === 0) {
            form.details.push({ ...defaultItem });
        }
    } else {
        // Redirect jika data tidak ditemukan
        router.push({ name: "entity-list-route" });
    }
};
```

## 5. DDL Loading

Sama seperti halaman Create, gunakan `async` function.

```typescript
const getCategoryDDL = async (search = ""): Promise<void> => {
    // ... logic load DDL
};
```

## 6. Real-time Validation (Precognition)

Gunakan event `@change` untuk memicu validasi Precognition secara real-time pada setiap input. Ini memberikan feedback instan kepada user.

**Single Field:**
```html
<FormInput 
    v-model="form.name" 
    :class="{ 'border-danger': form.invalid('name') }"
    @change="form.validate('name')" 
/>
<FormErrorMessages :messages="form.errors.name" />
```

**Array Field:**
Gunakan template literal untuk path array.
```html
<FormInput 
    v-model="form.items[index].price" 
    :class="{ 'border-danger': form.invalid(`items.${index}.price` as any) }"
    @change="form.validate(`items.${index}.price` as any)" 
/>
<FormErrorMessages :messages="(form.errors as any)[`items.${index}.price`]" />
```

## 7. Form Submission (Update)

Method `onSubmit` mirip dengan Create, namun biasanya memanggil method update pada service.

```typescript
const onSubmit = async () => {
    if (form.hasErrors) {
        scrollToError(Object.keys(form.errors)[0]);
    }
    
    emits("loading-state", true);
    
    await form.submit()
        .then(() => {
            // Sembunyikan alert error sebelumnya jika ada (PENTING)
            showAlertPlaceholder("hidden", "", null);
            
            emits("update-profile");
            router.push({ name: "entity-list-route" });
        })
        .catch((error) => {
            const errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error);
            showAlertPlaceholder("danger", "", errorList);
        })
        .finally(() => {
            emits("loading-state", false);
        });
};
```

## 8. Reset Form Strategy

Pada halaman Edit, Reset berarti **Reload Data** dari server, bukan sekedar mengosongkan form.

```typescript
const resetForm = async () => {
    form.reset();
    form.setErrors({});
    await loadData(); // Reload original data from server
};
```

## 9. Caching (Auto-Save Draft)

Halaman Edit **BOLEH** menyimpan draft edit ke cache untuk mencegah kehilangan data saat tidak sengaja refresh/close tab, namun **JANGAN** me-load cache tersebut secara otomatis di `onMounted` (kecuali ada logic restore khusus).

```typescript
watch(
    form,
    debounce((newValue): void => {
        // Gunakan key unik untuk EDIT, misal: ENTITY_EDIT
        cacheServices.setLastEntity("ENTITY_EDIT", newValue.data());
    }, 500),
    { deep: true }
);
```

## 10. Dynamic Form Arrays (Master-Detail)

Gunakan logika `removeItem` dan `updateItemName` yang sama dengan halaman Create, dengan tambahan penanganan ID untuk soft delete.

### Tambah Item (Add Item)
Saat menambah item baru, bersihkan error terkait array tersebut untuk mencegah error lama (stale errors).

```typescript
const addItem = () => {
    form.items.push({
        // ... default properties
    });

    // Clear errors related to items to prevent stale errors
    Object.keys(form.errors).forEach((key) => {
        if (key.startsWith("items.")) {
            form.forgetError(key as any);
        }
    });
};
```

### Hapus Item (Soft Delete)
Untuk halaman Edit, item yang dihapus mungkin perlu ditandai untuk dihapus di database (bukan sekedar `splice` array). Cek apakah item memiliki `id` sebelum menandai.

```typescript
const removeItem = (index: number) => {
    const item = form.details[index];
    
    // Jika item sudah ada di database (punya ID), masukkan ke list delete
    if (item.id) {
        if (!form.delete_detail_ids) {
            form.delete_detail_ids = [];
        }
        form.delete_detail_ids.push(item.id);
    }

    // Lanjutkan logic hapus array standar
    const isPrimary = form.details[index].is_primary;
    form.details.splice(index, 1);
    
    // Re-assign primary status jika yang dihapus adalah primary
    if (isPrimary && form.details.length > 0) {
        form.details[0].is_primary = true;
    }

    // Bersihkan error stale yang mungkin tertinggal untuk index tersebut
    Object.keys(form.errors).forEach((key) => {
        if (key.startsWith("details.")) {
            form.forgetError(key as any);
        }
    });
};
```

## 11. Helper UI Functions

Gunakan helper yang sama: `scrollToError`, `setCode` (untuk `_AUTO_`), dan `updateItemName` dengan UX improvement (`blur` focus).

```typescript
const scrollToError = (id: string): void => {
    let el = document.getElementById(id);
    if (!el) return;
    el.scrollIntoView({ behavior: "smooth", block: "center" });
};

const updateItemName = (index: number, newVal: string) => {
    // ... logic update
    if (found) {
        // ...
        (document.activeElement as HTMLElement)?.blur();
        form.forgetError(`details.${index}.item_id` as any);
    }
};
```
