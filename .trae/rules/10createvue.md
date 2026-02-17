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
import { CardState } from "@/types/enums/CardState";
import Button from "@/components/Base/Button";
import Lucide from "@/components/Base/Lucide";
import { type AlertPlaceholderProps } from "@/components/AlertPlaceholder/AlertPlaceholder.vue";
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
        id: "group1"
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

### Form Identifier

**WAJIB** memberikan `id` pada tag `<form>` dengan format `[entity]Form`.
Ini berguna untuk keperluan testing atau styling spesifik jika dibutuhkan.

```html
<form id="supplierForm" @submit.prevent="onSubmit">
    <!-- ... -->
</form>
```

## 3. Lifecycle Hooks (onMounted)

Pada `onMounted`, urutan inisialisasi sangat PENTING untuk mencegah *race condition* antara loading cache dan inisialisasi default form.

**Urutan yang Direkomendasikan:**
1. Emit mode view.
2. Validasi lokasi user.
3. **Load Cache TERLEBIH DAHULU** sebelum inisialisasi default array/object.
4. Inisialisasi default value (hanya jika cache kosong/tidak ada).
5. Load DDL secara paralel.
6. Repopulasi data label/nama dari DDL ke Form (jika diperlukan untuk UI).
7. Set data kontekstual (misal: `company_id`).

```typescript
onMounted(async () => {
    emits("mode-state", ViewMode.FORM_CREATE);
    
    // 1. Validasi lokasi user...

    // 2. Load Cache DULUAN untuk mencegah tertimpa inisialisasi default
    loadFromCache();

    // 3. Inisialisasi Default (hanya jika data kosong)
    if (form.details.length === 0) {
        form.details.push({ ...defaultItem });
    }

    // 4. Load DDL Paralel
    await Promise.all([
        getCategoryDDL(),
        getUnitDDL(),
        getStatusDDL()
    ]);

    // 5. Repopulasi Label/Nama (setelah DDL siap)
    // Penting untuk field yang menyimpan ID tapi menampilkan Nama (Read-only mode)
    repopulateDetailNames();
    
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
            // Sembunyikan alert error sebelumnya jika ada
            showAlertPlaceholder("hidden", "", null);
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

### Nested Array untuk Detail (Contoh: in_products / out_products)

Untuk entity yang memiliki detail nested seperti `in_products` / `out_products` (misalnya Stock Adjustment), gunakan pola berikut agar konsisten dengan Form Request Laravel yang memakai notasi `in_products.*.field_name`:

- Pisahkan detail ke dalam card sendiri di `TwoColumnsLayout` (misal: `views.stock_adjustment.field_groups.in_products`).
- Gunakan struktur array di form TypeScript yang mengikuti nested request (`StockAdjustmentInProductNestedStoreRequest`, dsb).
- Selalu gunakan path lengkap saat validasi Precognition:

```html
<div v-for="(item, index) in form.in_products" :key="index">
    <FormInput
        type="number"
        v-model.number="form.in_products[index].qty"
        :class="{ 'border-danger': form.invalid(`in_products.${index}.qty` as any) }"
        @change="form.validate(`in_products.${index}.qty` as any)"
    />
    <FormErrorMessages
        :messages="(form.errors as any)[`in_products.${index}.qty`]"
    />
</div>
```

**Catatan:**

- Nama field di frontend harus selaras dengan rules di Form Request dan `*Rules::mapToFieldNames` agar error message muncul di field yang benar.
- Untuk field detail lain (`product_unit_id`, `product_unit_conversion_value`, `product_unit_cogs`, `remarks`, dll), gunakan pola path yang sama (`in_products.${index}.field_name`).

## 7. Standard Error Handling Helper

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

## 8. Caching (Auto-Save)

Gunakan watcher dengan `debounce` untuk menyimpan draft form ke cache secara otomatis agar data tidak hilang saat refresh.

**Import:**
```typescript
import { debounce } from "lodash";
```

**Implementation:**
```typescript
watch(
    form,
    debounce(() => {
        // Simpan form state ke cache
        // Pastikan key cache unik per halaman (misal: ENTITY_CREATE)
        cacheServices.setLastEntity("ENTITY_CREATE", form.data());
    }, 500),
    { deep: true }
);
```

**Load from Cache:**
Pastikan membuat method `loadFromCache` dan memanggilnya di awal `onMounted`.

```typescript
const loadFromCache = () => {
    let data = cacheServices.getLastEntity("ENTITY_CREATE") as Record<string, unknown>;
    if (!data) return;
    
    // Handle specific default values if needed
    if (!data.code) data.code = '_AUTO_';
    
    form.setData(data);
};
```

## 9. Dynamic Form Arrays (Master-Detail)

Untuk form yang memiliki list item dinamis (seperti Product Units, Invoice Items), perhatikan hal berikut:

### Tambah Item (Add Item)
Saat menambah item baru, bersihkan error terkait array tersebut untuk mencegah error lama (stale errors) yang mungkin tidak relevan lagi.

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

### Hapus Item (Remove Item)
Saat menghapus item, pastikan menangani state global yang mungkin terpengaruh (misal: Primary Item).

```typescript
const removeItem = (index: number) => {
    const isPrimary = form.items[index].is_primary;
    form.items.splice(index, 1);

    // Re-assign primary status jika yang dihapus adalah primary
    if (isPrimary && form.items.length > 0) {
        form.items[0].is_primary = true;
    }

    // Bersihkan error stale yang mungkin tertinggal untuk index tersebut
    Object.keys(form.errors).forEach((key) => {
        if (key.startsWith("items.")) {
            form.forgetError(key as any);
        }
    });
};
```

### UX: Bersihkan DOM setelah Pilih Dropdown
Setelah user memilih item dari dropdown (terutama jika UI berubah menjadi mode read-only), lepaskan fokus dari elemen.

```typescript
const updateItemName = (index: number, newVal: string) => {
    // ... logic update ...
    if (found) {
        form.items[index].name = found.name;
        // Lepas fokus agar dropdown tertutup rapi
        (document.activeElement as HTMLElement)?.blur();
        
        // Hapus error spesifik field ini
        form.forgetError(`items.${index}.item_id` as any);
    }
};
```

### Repopulasi Data Label
Karena cache biasanya hanya menyimpan ID (value), sedangkan UI mungkin butuh Nama (label), buat fungsi untuk mengisi ulang nama setelah DDL siap.

```typescript
const repopulateDetailNames = () => {
    form.items.forEach((item) => {
        if (item.id && !item.name) {
            const match = ddl.value?.find(opt => opt.code === item.id);
            if (match) item.name = match.name;
        }
    });
};
```

## 10. Helper UI Functions

### Handle Expand Card
Helper ini digunakan untuk mengatur state expand/collapse card pada `TwoColumnsLayout`.

```typescript
const handleExpandCard = (index: number) => {
    if (cards.value[index].state === CardState.Collapsed) {
        cards.value[index].state = CardState.Expanded
    } else if (cards.value[index].state === CardState.Expanded) {
        cards.value[index].state = CardState.Collapsed
    }
};
```

### Show Alert Placeholder
Helper ini digunakan untuk menampilkan alert notifikasi (sukses/gagal) menggunakan emit.

```typescript
const showAlertPlaceholder = (pAlertType: 'hidden'|'danger'|'success'|'warning'|'pending'|'dark', pTitle: string, pAlertList: Record<string, Array<string>>|null) => {
  let ap: AlertPlaceholderProps = {
    alertType: pAlertType,
    title: pTitle,
    alertList: pAlertList,
  };

  emits('show-alert-placeholder', ap);
};
```

### Scroll To Error
Helper ini digunakan di `onSubmit` untuk scroll otomatis ke field yang error.

```typescript
const scrollToError = (id: string): void => {
    let el = document.getElementById(id);
    if (!el) return;
    el.scrollIntoView({ behavior: "smooth", block: "center" });
};
```

## 11. Auto-Generated Codes (_AUTO_)
Untuk field kode yang bisa digenerate otomatis, gunakan pattern `_AUTO_`.

**Template:**
```html
<FormInputCode 
    v-model="form.code"
    :placeholder="t('views.entity.fields.code')" 
    @set-auto="setCode"
    @change="form.validate('code')" 
/>
```

**Script:**
```typescript
const setCode = () => {
    form.forgetError("code");
    if (form.code == "_AUTO_") {
        form.setData({ code: "" });
    } else {
        form.setData({ code: "_AUTO_" });
    }
};
```

## 12. Reset Form Strategy
Saat mereset form, pastikan untuk mengembalikan state default yang mungkin tidak dicover oleh `form.reset()` standar, terutama untuk array dinamis.

```typescript
const resetForm = () => {
    form.reset();
    form.setErrors({});
    
    // Re-initialize default values for arrays or complex objects
    form.setData({
        details: [{
            ...defaultItem,
            // Pastikan properti default diset ulang dengan benar
            code: '_AUTO_',
            is_primary: true
        }]
    });
};
```

## 13. Debugging Tips

Untuk men-debug masalah validasi form secara realtime, gunakan watcher ini:

```typescript
watch(
    () => form.errors,
    (newErrors) => {
        console.log("Realtime Errors Update:", JSON.parse(JSON.stringify(newErrors)));
        console.log("Has Errors:", form.hasErrors);
    },
    { deep: true }
);
```
