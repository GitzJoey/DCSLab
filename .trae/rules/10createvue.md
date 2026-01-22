---
alwaysApply: false
description: Standarisasi penulisan halaman Create (EntityCreate.vue) di Frontend
---
# Standarisasi Halaman Create (EntityCreate.vue)

Dokumen ini menjelaskan standar penulisan halaman `[Entity]Create.vue` di frontend (`web/src/pages/[entity]/[Entity]Create.vue`) untuk menjaga konsistensi UI/UX, performa, dan error handling.

## 1. Imports

### Axios
Jangan mengimpor `axios` secara default. Impor hanya `isAxiosError` dan `AxiosError` untuk keperluan type checking dan error handling.

**JANGAN:** `import axios from "axios";`
**LAKUKAN:**
```typescript
import { isAxiosError, AxiosError } from "axios";
```

### Components & Services
Pastikan mengimpor komponen UI standar dan Service yang diperlukan.

```typescript
import { TwoColumnsLayout } from "@/components/Base/Form/FormLayout";
import { TwoColumnsLayoutCards } from "@/components/Base/Form/FormLayout/TwoColumnsLayout.vue";
import Button from "@/components/Base/Button";
import {
    FormInput,
    FormLabel,
    FormSelect,
    FormErrorMessages,
    // ... komponen form lainnya
} from "@/components/Base/Form";
import CacheService from "@/services/CacheService";
import DashboardService from "@/services/DashboardService";
// ... Import Service entitas terkait
```

## 2. Struktur & Layout

Gunakan `TwoColumnsLayout` dengan definisi `cards` state.

```typescript
// Script Setup
const cards = ref<Array<TwoColumnsLayoutCards>>([
    {
        title: "views.entity.field_groups.group_1",
        state: CardState.Expanded,
    },
    // ... group lainnya
    { title: "", state: CardState.Hidden, id: "button" },
]);

// Template
<template>
    <form id="entityForm" @submit.prevent="onSubmit">
        <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
            <!-- Templates untuk setiap card items -->
             <template #card-items-button>
                <!-- Tombol Submit & Reset -->
             </template>
        </TwoColumnsLayout>
    </form>
</template>
```

## 3. Lifecycle Hooks (onMounted)

Pada `onMounted`, lakukan inisialisasi berikut secara berurutan:
1. Emit mode view.
2. Cek validasi lokasi user (jika perlu).
3. **Load DDL secara paralel** menggunakan `Promise.all` untuk performa.
4. Load draft dari cache.
5. Set data default (misal: `company_id`).

```typescript
onMounted(async () => {
    emits("mode-state", ViewMode.FORM_CREATE);
    
    // Validasi lokasi user...

    // Load DDL Paralel
    await Promise.all([
        getCategoryDDL(),
        getUnitDDL(),
        getStatusDDL()
    ]);

    loadFromCache();
    setCompanyIdData();
});
```

## 4. DDL Loading (Drop Down List)

Method `getDDL` sebaiknya bersifat `async` dan langsung mengisi ref variable.

```typescript
const getStatusDDL = async (): Promise<void> => {
    const result = await dashboardServices.getStatusDDL();
    if (result) {
        statusDDL.value = result;
    }
};
```

## 5. Form Submission & Button State

### Tombol Submit
Tombol submit **HARUS** di-disable jika form sedang memvalidasi (`validating`) ATAU memiliki error (`hasErrors`).

```html
<Button type="submit" href="#" variant="primary" class="w-28 shadow-md"
    :disabled="form.validating || form.hasErrors">
    <Lucide v-if="form.validating" icon="Loader" class="animate-spin" />
    <template v-else>
        {{ t("components.buttons.submit") }}
    </template>
</Button>
```

### Method onSubmit
Method `onSubmit` harus menangani flow berikut:
1. Cek `form.hasErrors` -> scroll ke error pertama.
2. Emit `loading-state` true.
3. Panggil `form.submit()`.
4. Handle sukses: reset form, emit update, redirect.
5. Handle error: parse error menggunakan helper standar.
6. Finally: emit `loading-state` false.

```typescript
const onSubmit = async () => {
    if (form.hasErrors) {
        scrollToError(Object.keys(form.errors)[0]);
    }
    
    emits("loading-state", true);
    
    await form.submit()
        .then(() => {
            resetForm();
            emits("update-profile");
            router.push({ name: "route-name-list" });
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

## 6. Standard Error Handling Helper

Gunakan fungsi standar ini untuk memparsing error dari Axios response ke format alert list. Fungsi ini menangani `AxiosError` dan `Error` biasa.

```typescript
const convertErrorTypeToAlertListType = (error: unknown) => {
    const record: Record<string, Array<string>> = {};

    const anyError = error as any;
    const response = isAxiosError(error)
        ? (error as AxiosError).response
        : anyError?.response;

    if (response && response.data) {
        const data = response.data as any;

        if (data.errors && typeof data.errors === "object") {
            for (const key of Object.keys(data.errors)) {
                const value = data.errors[key];

                if (Array.isArray(value)) {
                    record[key] = value;
                } else if (value !== undefined && value !== null) {
                    record[key] = [String(value)];
                }
            }
            return record;
        }

        if (data.message) {
            record.error = [String(data.message)];
            return record;
        }
    }

    if (error instanceof Error && error.message) {
        record.error = [error.message];
    } else {
        record.error = ["Unknown error"];
    }

    return record;
};
```

## 7. Caching

Gunakan watcher dengan `debounce` untuk menyimpan draft form ke cache secara otomatis.

```typescript
watch(
    form,
    debounce((newValue): void => {
        cacheServices.setLastEntity("ENTITY_CREATE", newValue.data());
    }, 500),
    { deep: true }
);
```
