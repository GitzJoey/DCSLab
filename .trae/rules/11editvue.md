---
alwaysApply: false
description: Standarisasi penulisan halaman Edit (EntityEdit.vue) di Frontend
---
# Standarisasi Halaman Edit (EntityEdit.vue)

Dokumen ini menjelaskan standar penulisan halaman `[Entity]Edit.vue` di frontend (`web/src/pages/[entity]/[Entity]Edit.vue`). Standar ini mirip dengan `EntityCreate.vue` namun dengan perbedaan kunci pada **Data Loading** dan **Reset Strategy**.

Contoh konkret yang dijadikan referensi adalah `web/src/pages/stock-adjustment/StockAdjustmentEdit.vue`.

## 1. Imports

- Gunakan Composition API (`ref`, `computed`, `onMounted`, `watch`, `nextTick`).
- Gunakan `useRoute`, `useRouter` dari `vue-router`.
- Gunakan form dan layout dari base components (`TwoColumnsLayout`, `FormInput`, `FormLabel`, `FormErrorMessages`, `FormInputCode`, `FormInputCurrency`, `FormTextarea`, `FormSelectSearch`, `FormTomSelect`, `FormSwitch`).
- Gunakan service khusus entity (misalnya `StockAdjustmentService`, `StockAdjustmentCategoryService`, `WarehouseService`, `ProductService`).
- Gunakan helper utilitas (`formatDate`, `formatCurrency`).
- Gunakan store lokasi user (`useSelectedUserLocationStore`) dan enum `ErrorCode` untuk redirect jika lokasi belum dipilih.

Contoh (disederhanakan):

```typescript
import { computed, ref, onMounted, nextTick, watch } from "vue";
import { useRoute, useRouter } from "vue-router";
import { TwoColumnsLayout } from "@/components/Base/Form/FormLayout";
import { TwoColumnsLayoutCards } from "@/components/Base/Form/FormLayout/TwoColumnsLayout.vue";
import { CardState } from "@/types/enums/CardState";
import { useSelectedUserLocationStore } from "@/stores/selected-user-location";
import {
    FormInput,
    FormLabel,
    FormErrorMessages,
    FormInputCode,
    FormInputCurrency,
    FormTextarea,
    FormTomSelect,
    FormSwitch,
} from "@/components/Base/Form";
import { ErrorCode } from "@/types/enums/ErrorCode";
```

## 2. Struktur & Layout

- Gunakan `TwoColumnsLayout` dengan `cards` yang eksplisit berisi:
  - Group company/branch (`views.[entity].field_groups.company_info`).
  - Group data utama entity (`views.[entity].field_groups.[entity]_data`).
  - Group untuk array detail (misalnya `in_products`, `out_products`).
  - Card khusus tombol aksi dengan `id: "button"`.

Contoh pola:

```typescript
const cards = ref<Array<TwoColumnsLayoutCards>>([
    {
        title: "views.stock_adjustment.field_groups.company_info",
        state: CardState.Expanded,
    },
    {
        title: "views.stock_adjustment.field_groups.stock_adjustment_data",
        state: CardState.Expanded,
    },
    {
        title: "views.stock_adjustment.field_groups.in_products",
        state: CardState.Collapsed,
    },
    {
        title: "views.stock_adjustment.field_groups.out_products",
        state: CardState.Collapsed,
    },
    { title: "", state: CardState.Hidden, id: "button" },
]);
```

Form **WAJIB** diberi `id` dengan format `[entity]Form`.

```html
<form id="stockAdjustmentForm" @submit.prevent="onSubmit">
    <!-- ... -->
</form>
```

## 3. Lifecycle Hooks (onMounted)

- Halaman Edit **tidak** memuat data dari cache; data harus selalu diambil dari server.
- **WAJIB** validasi lokasi user (`isUserLocationSelected`); jika belum pilih, redirect ke halaman error.
- Setelah valid, load DDL paralel, kemudian load data entity berdasarkan `route.params.ulid`.

Contoh pola:

```typescript
onMounted(async () => {
    if (!isUserLocationSelected.value) {
        router.push({
            name: "side-menu-error-code",
            params: { code: ErrorCode.USERLOCATION_REQUIRED },
        });
        return;
    }

    await Promise.all([
        loadCategoryDDL(),
        loadInWarehouseDDL(),
        loadOutWarehouseDDL(),
    ]);

    await loadData();
});
```

## 4. Data Loading Strategy

- `loadData` bertanggung jawab mengambil data dari API dan mengisi form.
- Gunakan `route.params.ulid` sebagai key data.
- Mapping dari model API ke struktur form dilakukan secara eksplisit, termasuk array detail.
- Untuk kasus master–detail, di form digunakan struktur khusus (FormItem) yang menambahkan field tampilan seperti `product_unit_product_name`, `product_unit_unit_name`, dsb. Field tampilan ini **tidak** dikirim ke backend.

Contoh mapping master–detail (disederhanakan):

```typescript
const loadData = async () => {
    const result = await stockAdjustmentService.read(route.params.ulid as string);

    if (result.success && result.data) {
        const data = result.data;

        const inProducts = (data.in_products || []).map((item: any) => {
            const productUnit = item.product_unit;
            const unit = productUnit?.unit;
            const product = (productUnit as any)?.product;

            return {
                id: item.id,
                qty: item.qty,
                product_unit_id: productUnit?.id ?? "",
                product_unit_product_code: productUnit?.code ?? "",
                product_unit_product_name: product?.name ?? "",
                product_unit_unit_name: unit?.name ?? "",
                product_unit_base_unit_name: "",
                product_unit_conversion_value: item.product_unit_conversion_value,
                product_unit_cogs: item.product_unit_cogs,
                product_unit_total_cogs: item.product_unit_total_cogs,
                remarks: item.remarks ?? "",
            };
        });

        // mapping outProducts serupa

        stockAdjustmentForm.setData({
            company_id: data.company.id,
            branch_id: data.branch.id,
            code: data.code,
            date: data.date,
            category_id: data.category?.id ?? "",
            in_warehouse_id: data.in_warehouse?.id ?? "",
            out_warehouse_id: data.out_warehouse?.id ?? "",
            remarks: data.remarks ?? "",
            is_posted: data.is_posted,
            delete_in_product_ids: [],
            in_products: inProducts as any,
            delete_out_product_ids: [],
            out_products: outProducts as any,
        } as any);
    }
};
```

## 5. DDL Loading

- DDL (dropdown) di-load dengan function async per jenis data (kategori, gudang, dst).
- Hasil API di-map ke `DropDownOption` (`{ code, name }`).
- Untuk halaman Edit, **WAJIB** simpan hasil `read` awal ke state terpisah (misalnya `const stockAdjustmentData = ref<StockAdjustment | null>(null);`) di dalam `loadData`, lalu gunakan state ini sebagai sumber kebenaran nilai awal.
- Saat memanggil API `readAny*` untuk DDL yang punya parameter `include_id`, **WAJIB** gunakan ID dari data awal (misalnya `stockAdjustmentData.value?.category?.id`) dan **JANGAN** menggunakan nilai reactive di form (`stockAdjustmentForm.category_id`). Tujuannya agar opsi awal tetap ikut di hasil DDL walaupun user mengubah nilai form.
- Di template, untuk select yang bisa di-search, gunakan `FormSelectSearch` dengan:
  - `v-model` ke field form.
  - `v-model:search` ke ref string lokal untuk query.
  - `:options` berupa array `{ value, label }` hasil map dari `DropDownOption`.
  - Event `@change` untuk memicu `form.validate(field)`.
  - Event `@search` memanggil loader DDL (API akan dipanggil dengan query search).
- `FormTomSelect` boleh tetap digunakan untuk kasus khusus yang belum dimigrasi, namun standar baru untuk select searchable adalah `FormSelectSearch`.

Contoh standar DDL dengan `FormSelectSearch`:

```typescript
const stockAdjustmentData = ref<StockAdjustment | null>(null);
const categoryDDL = ref<Array<DropDownOption> | null>(null);
const categorySearch = ref<string>("");
const categoryOptions = computed(() =>
    (categoryDDL.value ?? []).map((item) => ({
        value: item.code,
        label: item.name,
    }))
);

const loadCategoryDDL = async (search = "") => {
    if (!selectedUserLocation.value) return;

    const result = await stockAdjustmentCategoryService.readAnyGet({
        with_trashed: false,
        company_id: selectedUserLocation.value.company.id,
        search,
        include_id: stockAdjustmentData.value?.category?.id as string | undefined,
        refresh: false,
        limit: 20,
    });

    if (result.success && result.data) {
        categoryDDL.value = result.data.data.map((item: any) => ({
            code: item.id,
            name: item.name,
        }));
    }
};
```

```html
<FormSelectSearch
    v-model="stockAdjustmentForm.category_id"
    v-model:search="categorySearch"
    :options="categoryOptions"
    :placeholder="t('components.dropdown.placeholder')"
    :class="{ 'border-danger': stockAdjustmentForm.invalid('category_id') }"
    @change="stockAdjustmentForm.validate('category_id')"
    @search="loadCategoryDDL"
/>
```

## 6. Real-time Validation (Precognition)

- Untuk field biasa gunakan:

```html
<FormInput
    v-model="stockAdjustmentForm.code"
    :class="{ 'border-danger': stockAdjustmentForm.invalid('code') }"
    @change="stockAdjustmentForm.validate('code')"
/>
<FormErrorMessages :messages="stockAdjustmentForm.errors.code" />
```

- Untuk field array pakai path template literal:

```html
<FormInputCurrency
    v-model="item.qty"
    :class="{
        'border-danger': stockAdjustmentForm.invalid(`in_products.${index}.qty` as any),
    }"
    @change="stockAdjustmentForm.validate(`in_products.${index}.qty` as any)"
/>
<FormErrorMessages
    :messages="(stockAdjustmentForm.errors as any)[`in_products.${index}.qty`]"
/>
```

## 7. Form Submission (Update)

- Sebelum submit, bersihkan field tampilan dari array detail sehingga hanya field yang diharapkan backend yang terkirim.
- Simpan backup array detail, set form ke versi "bersih", jalankan `submit()`, dan kembalikan backup jika terjadi error.

Contoh:

```typescript
const onSubmit = async () => {
    if (stockAdjustmentForm.hasErrors) {
        const firstErrorKey = Object.keys(stockAdjustmentForm.errors)[0];
        if (firstErrorKey) {
            scrollToError(firstErrorKey);
        }
        return;
    }

    const originalInProducts = stockAdjustmentForm.in_products as StockAdjustmentInProductFormItem[];
    const cleanedInProducts: StockAdjustmentInProductNestedUpdateRequest[] =
        originalInProducts.map(({ product_unit_product_code, product_unit_product_name, product_unit_unit_name, product_unit_base_unit_name, product_unit_total_cogs, ...rest }) => rest);

    const originalOutProducts = stockAdjustmentForm.out_products as StockAdjustmentOutProductFormItem[];
    const cleanedOutProducts: StockAdjustmentOutProductNestedUpdateRequest[] =
        originalOutProducts.map(({ product_unit_product_code, product_unit_product_name, product_unit_unit_name, product_unit_base_unit_name, ...rest }) => rest);

    const backupInProducts = [...originalInProducts];
    const backupOutProducts = [...originalOutProducts];

    stockAdjustmentForm.in_products = cleanedInProducts as any;
    stockAdjustmentForm.out_products = cleanedOutProducts as any;

    try {
        await stockAdjustmentForm.submit();
    } catch (error) {
        stockAdjustmentForm.in_products = backupInProducts as any;
        stockAdjustmentForm.out_products = backupOutProducts as any;
        console.error(error);
    }
};
```

## 8. Reset Form Strategy

- Pada halaman Edit, `reset` berarti **reload data original dari server**, bukan mengosongkan field.
- Selain reset data, juga reset error dan state tambahan (misalnya ekspansi remarks).

Contoh:

```typescript
const resetForm = async () => {
    stockAdjustmentForm.reset();
    stockAdjustmentForm.setErrors({});
    dateTimeDisplay.value = "";
    inProductsRemarksExpanded.value = [];
    outProductsRemarksExpanded.value = [];
    await loadData();
};
```

## 9. Dynamic Form Arrays (Master-Detail + Soft Delete)

- Tambah item detail:
  - Dorong item baru ke array.
  - Bersihkan error yang berkaitan dengan prefix path array (misalnya `in_products.`).
- Hapus item detail:
  - Jika item punya `id`, dorong ke `delete_*_ids` di form untuk diproses backend.
  - Splice array dan bersihkan error array terkait.

Contoh tambah dan hapus in_product:

```typescript
const removeInProduct = (index: number) => {
    const items = stockAdjustmentForm.in_products as StockAdjustmentInProductFormItem[];
    const item = items[index];

    if (item && item.id) {
        stockAdjustmentForm.delete_in_product_ids.push(item.id);
    }

    items.splice(index, 1);
    inProductsRemarksExpanded.value.splice(index, 1);

    Object.keys(stockAdjustmentForm.errors).forEach((key) => {
        if (key.startsWith("in_products.")) {
            stockAdjustmentForm.forgetError(key as any);
        }
    });
};
```

Pola serupa diterapkan untuk `out_products` dengan `delete_out_product_ids`.

## 10. Helper UI Functions

- `scrollToError` untuk scroll ke field yang pertama error.
- `setCode` untuk toggle `_AUTO_` pada field `code`.
- Menggunakan watcher untuk menghitung field turunan (misalnya total COGS).
- Menggunakan `nextTick` untuk mengatur fokus setelah modal pemilihan item/produk ditutup.

Contoh:

```typescript
const scrollToError = (id: string): void => {
    const el = document.getElementById(id);
    if (!el) return;
    el.scrollIntoView({ behavior: "smooth", block: "center" });
};
```

Dengan mengikuti pola di atas, halaman `[Entity]Edit.vue` (contoh: `StockAdjustmentEdit.vue`) akan konsisten dengan kaidah yang sudah digunakan pada halaman Create, terutama untuk:

- Struktur layout dan grouping card.
- Penggunaan Precognition untuk validasi real-time.
- Penanganan master–detail dengan soft delete.
- Reset yang selalu mengembalikan data ke state server terbaru.
