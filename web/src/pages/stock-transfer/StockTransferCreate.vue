<script setup lang="ts">
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import { CardState } from '@/types/enums/CardState';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import {
  FormInput,
  FormLabel,
  FormErrorMessages,
  FormInputCode,
  FormInputCurrency,
  FormInputDateTimeAuto,
  FormTextarea,
  FormSelectSearch,
  FormSwitch,
} from '@/components/Base/Form';
import WarehouseService from '@/services/WarehouseService';
import ProductService from '@/services/ProductService';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { Dialog } from '@/components/Base/Headless';
import ProductImagePreview from '@/components/Product/ProductImagePreview.vue';
import { convertErrorTypeToAlertListType, formatCurrency, formatDate } from '@/utils/helper';
import StockTransferService from '@/services/StockTransferService';
import CacheService from '@/services/CacheService';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { type DropDownOption } from '@/types/models/DropDownOption';
import {
  type StockTransferItemNestedStoreRequest,
} from '@/types/services/stock-transfer/StockTransferRequest';
import { debounce } from 'lodash';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';

type StockTransferItemFormItem = {
  qty: StockTransferItemNestedStoreRequest['qty'];
  product_unit_id: StockTransferItemNestedStoreRequest['product_unit_id'];
  product_unit_product_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_unit_name?: string | null;
  product_unit_base_unit_name?: string | null;
  product_unit_conversion_value: StockTransferItemNestedStoreRequest['product_unit_conversion_value'];
  remarks: StockTransferItemNestedStoreRequest['remarks'];
  is_use_serial_number?: boolean;
  serials: { serial: string }[];
};

type ProductUnitOption = {
  product_id: string;
  product_code: string;
  product_name: string;
  product_image_url: string | null;
  product_unit_id: string;
  product_unit_code: string;
  unit_name: string;
  base_unit_name: string;
  conversion_value: number;
  cogs: number;
  remaining_stock: number;
  is_use_serial_number: boolean;
};

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const stockTransferService = new StockTransferService();
const stockTransferForm = stockTransferService.useStockTransferCreateForm();
const warehouseService = new WarehouseService();
const productService = new ProductService();
const cacheServices = new CacheService();

const sourceWarehouseDDL = ref<Array<DropDownOption> | null>(null);
const sourceWarehouseSearch = ref<string>('');
const sourceWarehouseOptions = computed(() =>
  (sourceWarehouseDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const destinationWarehouseDDL = ref<Array<DropDownOption> | null>(null);
const destinationWarehouseSearch = ref<string>('');
const destinationWarehouseOptions = computed(() =>
  (destinationWarehouseDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const showProductUnitModal = ref<boolean>(false);
const productSearchText = ref<string>('');
const isSearchingProductUnit = ref<boolean>(false);
const productUnitOptions = ref<Array<ProductUnitOption>>([]);
const editingProductUnitIndex = ref<number | null>(null);
const productUnitQtyToFocus = ref<number | null>(null);
const productUnitsRemarksExpanded = ref<boolean[]>([]);

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.stock_transfer.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.stock_transfer.field_groups.stock_transfer_data',
    state: CardState.Expanded,
  },
  {
    title: 'views.stock_transfer.field_groups.items',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const productUnitsForm = computed<StockTransferItemFormItem[]>(
  () => stockTransferForm.items as StockTransferItemFormItem[],
);

const invalidStockTransferField = (field: string) => stockTransferForm.invalid(field as any);

const validateStockTransferField = (field: string) => stockTransferForm.validate(field as any);

const getStockTransferFieldErrors = (field: string) =>
  (stockTransferForm.errors as Record<string, string | undefined>)[field];

const getProductMainImageUrl = (product: any): string | null => {
  const images: any[] = product?.product_images ?? [];
  if (images.length === 0) return null;

  const mainImage = images.find((img: any) => img?.is_main);
  if (mainImage?.url) return mainImage.url;

  return images[0]?.url ?? null;
};

const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};

watch(
  stockTransferForm,
  debounce((newValue): void => {
    cacheServices.setLastEntity('STOCK_TRANSFER_CREATE', newValue.data());
  }, 500),
  { deep: true },
);

onMounted(async () => {
  emits('mode-state', ViewMode.FORM_CREATE);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  loadFromCache();

  stockTransferForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
  });

  await Promise.all([loadSourceWarehouseDDL(), loadDestinationWarehouseDDL()]);
});

const setCode = () => {
  stockTransferForm.forgetError('code');
  if (stockTransferForm.code === '_AUTO_') {
    stockTransferForm.setData({ code: '' });
  } else {
    stockTransferForm.setData({ code: '_AUTO_' });
  }
};

const loadSourceWarehouseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await warehouseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    include_id: undefined,
    status: undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    sourceWarehouseDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadDestinationWarehouseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await warehouseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    include_id: undefined,
    status: undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    destinationWarehouseDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const clearSourceWarehouse = () => {
  stockTransferForm.setData({ source_warehouse_id: '' });
  stockTransferForm.forgetError('source_warehouse_id');
  stockTransferForm.validate('source_warehouse_id');
};

const clearDestinationWarehouse = () => {
  stockTransferForm.setData({ destination_warehouse_id: '' });
  stockTransferForm.forgetError('destination_warehouse_id');
  stockTransferForm.validate('destination_warehouse_id');
};

const loadFromCache = () => {
  const data = cacheServices.getLastEntity('STOCK_TRANSFER_CREATE') as Record<string, unknown>;
  if (!data) return;
  stockTransferForm.setData(data);
  productUnitsRemarksExpanded.value = ((data.items as unknown[]) ?? []).map(() => false);
};

const searchProductUnits = async () => {
  if (!selectedUserLocation.value) return;

  if (!stockTransferForm.source_warehouse_id) {
    stockTransferForm.validate('source_warehouse_id');
    return;
  }

  if (!stockTransferForm.date) {
    stockTransferForm.validate('date');
    return;
  }

  isSearchingProductUnit.value = true;

  let endDate: string;
  if (stockTransferForm.date === '_AUTO_') {
    endDate = formatDate(new Date().toString(), 'YYYY-MM-DD HH:mm:ss');
  } else {
    endDate = stockTransferForm.date as string;
  }

  const result = await productService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search: productSearchText.value,
    category_id: undefined,
    brand_id: undefined,
    is_taxable: undefined,
    vat_rate: undefined,
    is_price_include_vat: undefined,
    is_use_serial_number: undefined,
    is_expirable: undefined,
    type: undefined,
    status: undefined,
    include_id: undefined,
    with_remaining_stock: {
      end_date: endDate,
      warehouse_id: stockTransferForm.source_warehouse_id as string,
      stock_filter: 'has_stock',
      include_service_products: false,
    },
    refresh: true,
    limit: 20,
  });

  isSearchingProductUnit.value = false;

  if (result.success && result.data) {
    const products = result.data.data as any[];
    productUnitOptions.value = products.flatMap((p: any) => {
      const units: any[] = p.product_units || [];
      const remainingStockBaseUnit = Number(p.remaining_stock_base_unit ?? 0);

      let baseUnit = units.find((u: any) => u.is_base);
      if (!baseUnit) baseUnit = units.find((u: any) => Number(u.conversion_value) === 1);
      if (!baseUnit) baseUnit = units.find((u: any) => u.is_primary_unit);
      if (!baseUnit && units.length > 0) baseUnit = units[0];
      const baseUnitName = baseUnit && baseUnit.unit ? baseUnit.unit.name : '';

      return units.map((u: any) => {
        const conversionValue = Number(u.conversion_value) || 1;
        const remainingStock = remainingStockBaseUnit / conversionValue;

        return {
          product_id: p.id,
          product_code: p.code,
          product_name: p.name,
          product_image_url: getProductMainImageUrl(p),
          product_unit_id: u.id,
          product_unit_code: u.code,
          unit_name: u.unit ? u.unit.name : '',
          base_unit_name: baseUnitName,
          conversion_value: conversionValue,
          cogs: Number(u.price),
          remaining_stock: remainingStock,
          is_use_serial_number: p.is_use_serial_number,
        };
      });
    });
  } else {
    productUnitOptions.value = [];
  }
};

const addProductUnit = () => {
  productSearchText.value = '';
  productUnitOptions.value = [];
  isSearchingProductUnit.value = false;
  editingProductUnitIndex.value = null;
  showProductUnitModal.value = true;
};

const changeProductUnit = (index: number) => {
  productSearchText.value = '';
  productUnitOptions.value = [];
  isSearchingProductUnit.value = false;
  editingProductUnitIndex.value = index;
  showProductUnitModal.value = true;
};

const selectProductUnit = (option: ProductUnitOption) => {
  const baseData: Partial<StockTransferItemFormItem> = {
    product_unit_id: option.product_unit_id,
    product_unit_product_code: option.product_unit_code,
    product_unit_product_name: option.product_name,
    product_unit_unit_name: option.unit_name,
    product_unit_base_unit_name: option.conversion_value != 1 ? option.base_unit_name : '',
    product_unit_conversion_value: option.conversion_value,
    is_use_serial_number: option.is_use_serial_number,
    serials: [],
  };

  let targetIndex: number;

  if (editingProductUnitIndex.value === null) {
    const item: StockTransferItemFormItem = {
      qty: 0,
      remarks: '',
      ...baseData,
    } as StockTransferItemFormItem;

    stockTransferForm.items.push(item as any);
    productUnitsRemarksExpanded.value.push(false);
    targetIndex = stockTransferForm.items.length - 1;
  } else {
    const index = editingProductUnitIndex.value;
    const current = stockTransferForm.items[index] as StockTransferItemFormItem;
    stockTransferForm.items[index] = {
      ...current,
      ...baseData,
    } as any;
    targetIndex = index;
  }

  showProductUnitModal.value = false;
  editingProductUnitIndex.value = null;
  productUnitQtyToFocus.value = targetIndex;

  Object.keys(stockTransferForm.errors).forEach((key) => {
    if (key.startsWith('items.')) {
      stockTransferForm.forgetError(key as any);
    }
  });
};

const toggleProductUnitRemarks = (index: number) => {
  const current = productUnitsRemarksExpanded.value[index] ?? false;
  productUnitsRemarksExpanded.value[index] = !current;
};

const handleProductUnitModalAfterLeave = () => {
  const index = productUnitQtyToFocus.value;

  productUnitQtyToFocus.value = null;

  if (index === null) return;

  nextTick(() => {
    const el = document.getElementById(`product-units-qty-${index}`) as HTMLInputElement | null;
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el?.focus();
    el?.select();
  });
};

const addProductUnitSerial = (index: number) => {
  const item = productUnitsForm.value[index];
  if (!item) return;
  item.serials.push({ serial: '' });
  stockTransferForm.validate(`items.${index}.serials` as any);
};

const removeProductUnitSerial = (index: number, serialIndex: number) => {
  const item = productUnitsForm.value[index];
  if (!item) return;
  item.serials.splice(serialIndex, 1);
  stockTransferForm.validate(`items.${index}.serials` as any);
};

const removeProductUnit = (index: number) => {
  productUnitsForm.value.splice(index, 1);
  productUnitsRemarksExpanded.value.splice(index, 1);

  Object.keys(stockTransferForm.errors).forEach((key) => {
    if (key.startsWith('items.')) {
      stockTransferForm.forgetError(key as any);
    }
  });
};

const scrollToError = (id: string): void => {
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const showAlertPlaceholder = (
  pAlertType: 'hidden' | 'danger' | 'success' | 'warning' | 'pending' | 'dark',
  pTitle: string,
  pAlertList: Record<string, Array<string>> | null,
) => {
  const ap: AlertPlaceholderProps = {
    alertType: pAlertType,
    title: pTitle,
    alertList: pAlertList,
  };
  emits('show-alertplaceholder', ap);
};

const onSubmit = async () => {
  if (stockTransferForm.hasErrors) {
    const firstErrorKey = Object.keys(stockTransferForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  const originalProductUnits = stockTransferForm.items as StockTransferItemFormItem[];
  const cleanedProductUnits: StockTransferItemNestedStoreRequest[] = originalProductUnits.map(
    (item: StockTransferItemFormItem) => ({
      qty: item.qty,
      product_unit_id: item.product_unit_id,
      product_unit_conversion_value: item.product_unit_conversion_value,
      remarks: item.remarks,
      serials: item.serials.map((serialItem) => ({
        serial: serialItem.serial,
      })),
    }),
  );

  const backupProductUnits = [...originalProductUnits];
  stockTransferForm.items = cleanedProductUnits as any;

  emits('loading-state', true);

  try {
    await stockTransferForm.submit();
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    router.push({ name: 'side-menu-stock-transfer-list' });
  } catch (error) {
    stockTransferForm.items = backupProductUnits as any;
    const errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error);
    showAlertPlaceholder('danger', '', errorList);
  } finally {
    emits('loading-state', false);
  }
};
</script>

<template>
  <form id="stockTransferForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <template #card-items-0>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.company.code }}
                <br />
                {{ selectedUserLocation.company.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="stockTransferForm.company_id" />
            </div>

            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="stockTransferForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel :class="{ 'text-danger': stockTransferForm.invalid('code') }">
                {{ t('views.stock_transfer.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="stockTransferForm.code"
                :class="{ 'border-danger': stockTransferForm.invalid('code') }"
                :placeholder="t('views.stock_transfer.fields.code')"
                @set-auto="setCode"
                @change="stockTransferForm.validate('code')"
              />
              <FormErrorMessages :messages="stockTransferForm.errors.code" />
            </div>
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel :class="{ 'text-danger': stockTransferForm.invalid('date') }">
                {{ t('views.stock_transfer.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="stockTransferForm.date"
                :class="{ 'border-danger': stockTransferForm.invalid('date') }"
                :placeholder="t('views.stock_transfer.fields.date')"
                @change="stockTransferForm.validate('date')"
              />
              <FormErrorMessages :messages="stockTransferForm.errors.date" />
            </div>
            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': stockTransferForm.invalid('source_warehouse_id') }">
                {{ t('views.stock_transfer.fields.source_warehouse_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="stockTransferForm.source_warehouse_id"
                v-model:search="sourceWarehouseSearch"
                :options="sourceWarehouseOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': stockTransferForm.invalid('source_warehouse_id') }"
                @change="stockTransferForm.validate('source_warehouse_id')"
                @search="loadSourceWarehouseDDL"
                @clear="clearSourceWarehouse"
              />
              <FormErrorMessages :messages="stockTransferForm.errors.source_warehouse_id" />
            </div>
            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': stockTransferForm.invalid('destination_warehouse_id') }">
                {{ t('views.stock_transfer.fields.destination_warehouse_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="stockTransferForm.destination_warehouse_id"
                v-model:search="destinationWarehouseSearch"
                :options="destinationWarehouseOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': stockTransferForm.invalid('destination_warehouse_id') }"
                @change="stockTransferForm.validate('destination_warehouse_id')"
                @search="loadDestinationWarehouseDDL"
                @clear="clearDestinationWarehouse"
              />
              <FormErrorMessages :messages="stockTransferForm.errors.destination_warehouse_id" />
            </div>
            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': stockTransferForm.invalid('remarks') }">
                {{ t('views.stock_transfer.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                v-model="stockTransferForm.remarks"
                :class="{ 'border-danger': stockTransferForm.invalid('remarks') }"
                :placeholder="t('views.stock_transfer.fields.remarks')"
                @change="stockTransferForm.validate('remarks')"
              />
              <FormErrorMessages :messages="stockTransferForm.errors.remarks" />
            </div>
            <div class="col-span-12">
              <FormLabel class="pr-5">
                {{ t('views.stock_transfer.fields.is_posted') }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input v-model="stockTransferForm.is_posted" type="checkbox" />
              </FormSwitch>
            </div>
          </div>
        </div>
      </template>

      <template #card-items-2>
        <div class="p-5">
          <FormErrorMessages :messages="stockTransferForm.errors.items" />

          <div v-if="productUnitsForm.length === 0" class="text-slate-500 text-sm">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <div v-else class="space-y-5">
            <div
              v-for="(item, index) in productUnitsForm"
              :key="`${item.product_unit_id}-${index}`"
              class="border border-slate-200/60 dark:border-darkmode-400 rounded-md p-4"
            >
              <div class="flex items-center justify-between mb-3">
                <div class="font-medium text-sm">
                  {{ t('views.stock_transfer_item.page_title') }} #{{ index + 1 }}
                </div>
                <div class="flex items-center gap-2">
                  <Button
                    type="button"
                    class="text-xs text-slate-500 hover:text-primary"
                    @click="toggleProductUnitRemarks(index)"
                  >
                    {{ productUnitsRemarksExpanded[index] ? '▲' : '▼' }}
                  </Button>
                  <Button type="button" variant="outline-secondary" @click="removeProductUnit(index)">
                    <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                  </Button>
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-2">
                  <FormLabel :class="{ 'text-danger': invalidStockTransferField(`items.${index}.qty`) }">
                    {{ t('views.stock_transfer_item.fields.qty') }}
                  </FormLabel>
                  <FormInputCurrency
                    :id="`product-units-qty-${index}`"
                    v-model="item.qty"
                    :allow-negative="false"
                    :class="[
                      'text-right',
                      {
                        'border-danger': invalidStockTransferField(`items.${index}.qty`),
                      },
                    ]"
                    @change="
                      validateStockTransferField(`items.${index}.qty`);
                      validateStockTransferField(`items.${index}.serials`);
                    "
                  />
                  <FormErrorMessages :messages="getStockTransferFieldErrors(`items.${index}.qty`)" />
                  <div class="text-sm text-slate-500 text-right font-bold mt-1">
                    {{ item.product_unit_unit_name }}
                  </div>
                </div>
                <div class="col-span-12 lg:col-span-8">
                  <FormLabel
                    :class="{ 'text-danger': invalidStockTransferField(`items.${index}.product_unit_id`) }"
                  >
                    {{ t('views.stock_transfer_item.table.cols.product') }}
                  </FormLabel>
                  <div class="flex items-center gap-2">
                    <div class="flex-1">
                      <div
                        class="form-control border rounded-md px-3 py-2 bg-slate-50 dark:bg-darkmode-800 text-slate-700 dark:text-slate-300"
                      >
                        {{ item.product_unit_product_name || '-' }}
                      </div>
                    </div>
                    <Button
                      type="button"
                      variant="outline-secondary"
                      tabindex="-1"
                      class="flex items-center justify-center border-slate-500 text-slate-500 hover:text-primary hover:border-primary"
                      @click="changeProductUnit(index)"
                    >
                      <Lucide icon="Search" class="w-4 h-4" />
                    </Button>
                  </div>
                  <FormErrorMessages :messages="getStockTransferFieldErrors(`items.${index}.product_unit_id`)" />
                  <div class="text-sm text-slate-500 font-bold mt-1">
                    {{ item.product_unit_product_code }}
                  </div>
                </div>
                <div class="col-span-12 lg:col-span-2">
                  <FormLabel
                    :class="{
                      'text-danger': invalidStockTransferField(`items.${index}.product_unit_conversion_value`),
                    }"
                  >
                    {{ t('views.stock_transfer_item.fields.product_unit_conversion_value') }}
                  </FormLabel>
                  <FormInputCurrency
                    v-model="item.product_unit_conversion_value"
                    tabindex="-1"
                    :class="[
                      'text-right',
                      {
                        'border-danger': invalidStockTransferField(`items.${index}.product_unit_conversion_value`),
                      },
                    ]"
                    @change="
                      validateStockTransferField(`items.${index}.product_unit_conversion_value`);
                      validateStockTransferField(`items.${index}.serials`);
                    "
                  />
                  <FormErrorMessages
                    :messages="getStockTransferFieldErrors(`items.${index}.product_unit_conversion_value`)"
                  />
                  <div class="text-sm text-slate-500 text-right font-bold mt-1">
                    {{ item.product_unit_base_unit_name }}
                  </div>
                </div>

                <div v-if="item.is_use_serial_number" class="col-span-12">
                  <div class="flex items-center justify-between mb-2">
                    <FormLabel :class="{ 'text-danger': invalidStockTransferField(`items.${index}.serials`) }">
                      {{ t('views.product.fields.serial_number') }}
                    </FormLabel>
                    <Button type="button" size="sm" variant="outline-primary" @click="addProductUnitSerial(index)">
                      <Lucide icon="Plus" class="w-3 h-3 mr-1" />
                      {{ t('components.buttons.create') }}
                    </Button>
                  </div>
                  <div v-if="item.serials.length === 0" class="text-slate-500 text-xs italic">
                    {{ t('components.data-list.data_not_found') }}
                  </div>
                  <div v-else class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    <div
                      v-for="(serialItem, serialIndex) in item.serials"
                      :key="`${index}-${serialIndex}`"
                      class="flex gap-2"
                    >
                      <FormInput
                        :id="`items.${index}.serials.${serialIndex}.serial`"
                        v-model="serialItem.serial"
                        :placeholder="t('views.product.fields.serial_number')"
                        :class="{
                          'border-danger': invalidStockTransferField(`items.${index}.serials.${serialIndex}.serial`),
                        }"
                        @change="
                          validateStockTransferField(`items.${index}.serials.${serialIndex}.serial`);
                          validateStockTransferField(`items.${index}.serials`);
                        "
                      />
                      <Button type="button" variant="outline-secondary" @click="removeProductUnitSerial(index, serialIndex)">
                        <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                      </Button>
                    </div>
                  </div>
                  <FormErrorMessages :messages="getStockTransferFieldErrors(`items.${index}.serials`)" />
                  <FormErrorMessages
                    v-for="(_, serialIndex) in item.serials"
                    :key="`product-units-serial-error-${index}-${serialIndex}`"
                    :messages="getStockTransferFieldErrors(`items.${index}.serials.${serialIndex}.serial`)"
                  />
                </div>

                <div v-if="productUnitsRemarksExpanded[index]" class="col-span-12 space-y-3">
                  <div>
                    <FormLabel :class="{ 'text-danger': invalidStockTransferField(`items.${index}.remarks`) }">
                      {{ t('views.stock_transfer_item.fields.remarks') }}
                    </FormLabel>
                    <FormTextarea
                      v-model="item.remarks"
                      rows="2"
                      :class="{ 'border-danger': invalidStockTransferField(`items.${index}.remarks`) }"
                      @change="validateStockTransferField(`items.${index}.remarks`)"
                    />
                    <FormErrorMessages :messages="getStockTransferFieldErrors(`items.${index}.remarks`)" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center justify-between mt-4">
            <FormLabel />
            <Button type="button" variant="primary" class="shadow-md" @click="addProductUnit">
              {{ t('views.stock_transfer_item.actions.create') }}
            </Button>
          </div>
        </div>
      </template>

      <template #card-items-button>
        <div class="flex justify-end gap-2 p-5">
          <Button type="submit" variant="primary">
            <Lucide icon="Save" class="w-4 h-4 mr-2" />
            {{ t('components.buttons.save') }}
          </Button>
        </div>
      </template>
    </TwoColumnsLayout>
  </form>

  <Dialog size="xl" :open="showProductUnitModal" @close="showProductUnitModal = false" @after-leave="handleProductUnitModalAfterLeave">
    <Dialog.Panel>
      <div class="p-5">
        <div class="flex items-center justify-between mb-4">
          <FormLabel>
            {{ t('views.stock_transfer.field_groups.items') }}
          </FormLabel>
          <button type="button" class="text-slate-500 hover:text-danger" @click="showProductUnitModal = false">
            <Lucide icon="X" class="w-4 h-4" />
          </button>
        </div>

        <div class="flex items-center gap-2 mb-4">
          <FormInput
            id="product-unit-search-input"
            v-model="productSearchText"
            type="text"
            :placeholder="t('components.search-box.placeholder.search')"
            @keyup.enter="searchProductUnits"
          />
          <Button
            type="button"
            variant="primary"
            class="shadow-md"
            tabindex="-1"
            :disabled="isSearchingProductUnit || !stockTransferForm.source_warehouse_id"
            @click="searchProductUnits"
          >
            <template v-if="isSearchingProductUnit">
              <Lucide icon="Loader" class="w-4 h-4 animate-spin" />
            </template>
            <template v-else>
              {{ t('components.buttons.search') }}
            </template>
          </Button>
        </div>

        <div class="max-h-80 overflow-auto border border-slate-200/60 dark:border-darkmode-400 rounded-md">
          <table class="min-w-full text-sm">
            <thead class="bg-slate-100 dark:bg-darkmode-600">
              <tr>
                <th class="px-3 py-2 text-left">
                  {{ t('views.product.table.cols.image') }}
                </th>
                <th class="px-3 py-2 text-left">
                  {{ t('views.stock_transfer_item.table.cols.product') }}
                </th>
                <th class="px-3 py-2 text-right">
                  {{ t('views.stock_adjustment_out_item.table.cols.remaining_stock') }}
                </th>
                <th class="px-3 py-2 text-left">
                  {{ t('views.product.table.cols.unit') }}
                </th>
                <th class="px-3 py-2 text-right">
                  {{ t('views.product.fields.conversion_value') }}
                </th>
                <th class="px-3 py-2 text-right">
                  {{ t('views.stock_adjustment_in_item.table.cols.product_unit_cogs') }}
                </th>
                <th class="px-3 py-2"></th>
              </tr>
            </thead>
            <tbody>
              <tr v-if="productUnitOptions.length === 0">
                <td colspan="7" class="px-3 py-4 text-center text-slate-500">
                  {{ t('components.data-list.data_not_found') }}
                </td>
              </tr>
              <tr
                v-for="(option, index) in productUnitOptions"
                :key="`${option.product_unit_id}-${index}`"
                class="border-t border-slate-200/60 dark:border-darkmode-400"
              >
                <td class="px-3 py-2">
                  <ProductImagePreview
                    :image-url="option.product_image_url"
                    wrapper-class="w-10 h-10 rounded-md overflow-hidden bg-slate-100 dark:bg-darkmode-600 flex items-center justify-center cursor-zoom-in"
                    icon-class="w-4 h-4 text-slate-400"
                  />
                </td>
                <td class="px-3 py-2">[{{ option.product_unit_code }}] {{ option.product_name }}</td>
                <td class="px-3 py-2 text-right">
                  {{ formatCurrency(option.remaining_stock) }}
                </td>
                <td class="px-3 py-2">
                  {{ option.unit_name }}
                </td>
                <td class="px-3 py-2 text-right">
                  {{ formatCurrency(option.conversion_value) }}
                </td>
                <td class="px-3 py-2 text-right">
                  {{ formatCurrency(option.cogs) }}
                </td>
                <td class="px-3 py-2 text-right">
                  <Button type="button" variant="primary" size="sm" @click="selectProductUnit(option)">
                    {{ t('components.buttons.select') }}
                  </Button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
