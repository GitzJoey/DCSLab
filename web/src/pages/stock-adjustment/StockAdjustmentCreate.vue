<script setup lang="ts">
// #region Imports
import { computed, ref, onMounted, nextTick, watch } from 'vue';
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
  FormInputDateTime,
  FormTextarea,
  FormSelectSearch,
  FormSwitch,
} from '@/components/Base/Form';
import WarehouseService from '@/services/WarehouseService';
import ProductService from '@/services/ProductService';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import { Dialog } from '@/components/Base/Headless';
import { formatDate, formatCurrency, convertErrorTypeToAlertListType } from '@/utils/helper';
import StockAdjustmentService from '@/services/StockAdjustmentService';
import StockAdjustmentCategoryService from '@/services/StockAdjustmentCategoryService';
import CacheService from '@/services/CacheService';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { type DropDownOption } from '@/types/models/DropDownOption';
import {
  type StockAdjustmentInProductNestedStoreRequest,
  type StockAdjustmentOutProductNestedStoreRequest,
} from '@/types/services/stock-adjustment/StockAdjustmentRequest';
import { debounce } from 'lodash';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
// #endregion

// #region Declarations
type StockAdjustmentInProductFormItem = {
  qty: StockAdjustmentInProductNestedStoreRequest['qty'];
  product_unit_id: StockAdjustmentInProductNestedStoreRequest['product_unit_id'];
  product_unit_product_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_unit_name?: string | null;
  product_unit_base_unit_name?: string | null;
  product_unit_conversion_value: StockAdjustmentInProductNestedStoreRequest['product_unit_conversion_value'];
  product_unit_cogs: StockAdjustmentInProductNestedStoreRequest['product_unit_cogs'];
  product_unit_total_cogs?: number | null;
  remarks?: StockAdjustmentInProductNestedStoreRequest['remarks'];
};

type StockAdjustmentOutProductFormItem = {
  qty: StockAdjustmentOutProductNestedStoreRequest['qty'];
  product_unit_id: StockAdjustmentOutProductNestedStoreRequest['product_unit_id'];
  product_unit_product_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_unit_name?: string | null;
  product_unit_base_unit_name?: string | null;
  product_unit_conversion_value: StockAdjustmentOutProductNestedStoreRequest['product_unit_conversion_value'];
  remarks?: StockAdjustmentOutProductNestedStoreRequest['remarks'];
};

type ProductUnitOption = {
  product_id: string;
  product_code: string;
  product_name: string;
  product_unit_id: string;
  product_unit_code: string;
  unit_name: string;
  base_unit_name: string;
  conversion_value: number;
  cogs: number;
  remaining_stock: number;
};

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const stockAdjustmentService = new StockAdjustmentService();
const stockAdjustmentForm = stockAdjustmentService.useStockAdjustmentCreateForm();
const stockAdjustmentCategoryService = new StockAdjustmentCategoryService();
const warehouseService = new WarehouseService();
const productService = new ProductService();
const cacheServices = new CacheService();

const categoryDDL = ref<Array<DropDownOption> | null>(null);
const categorySearch = ref<string>('');
const categoryOptions = computed(() =>
  (categoryDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const inWarehouseDDL = ref<Array<DropDownOption> | null>(null);
const inWarehouseSearch = ref<string>('');
const inWarehouseOptions = computed(() =>
  (inWarehouseDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);
const outWarehouseDDL = ref<Array<DropDownOption> | null>(null);
const outWarehouseSearch = ref<string>('');
const outWarehouseOptions = computed(() =>
  (outWarehouseDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const showInProductUnitModal = ref<boolean>(false);
const productSearchTextIn = ref<string>('');
const isSearchingInProductUnit = ref<boolean>(false);
const productUnitOptionsIn = ref<Array<ProductUnitOption>>([]);
const editingInProductIndex = ref<number | null>(null);
const inProductQtyToFocus = ref<number | null>(null);
const inProductsRemarksExpanded = ref<boolean[]>([]);
const inProductsForm = computed<StockAdjustmentInProductFormItem[]>(
  () => stockAdjustmentForm.in_products as StockAdjustmentInProductFormItem[],
);

const showOutProductUnitModal = ref<boolean>(false);
const productSearchTextOut = ref<string>('');
const isSearchingOutProductUnit = ref<boolean>(false);
const productUnitOptionsOut = ref<Array<ProductUnitOption>>([]);
const editingOutProductIndex = ref<number | null>(null);
const outProductQtyToFocus = ref<number | null>(null);
const outProductsRemarksExpanded = ref<boolean[]>([]);
const outProductsForm = computed<StockAdjustmentOutProductFormItem[]>(
  () => stockAdjustmentForm.out_products as StockAdjustmentOutProductFormItem[],
);

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.stock_adjustment.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.stock_adjustment.field_groups.stock_adjustment_data',
    state: CardState.Expanded,
  },
  {
    title: 'views.stock_adjustment.field_groups.in_products',
    state: CardState.Collapsed,
  },
  {
    title: 'views.stock_adjustment.field_groups.out_products',
    state: CardState.Collapsed,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);
// #endregion

// #region Vue Core
const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};

watch(
  () => stockAdjustmentForm.in_products as StockAdjustmentInProductFormItem[],
  (items: StockAdjustmentInProductFormItem[]) => {
    if (!items) return;
    items.forEach((item: StockAdjustmentInProductFormItem) => {
      const qty = Number(item.qty ?? 0);
      const cogs = Number(item.product_unit_cogs ?? 0);
      item.product_unit_total_cogs = qty * cogs;
    });
  },
  { deep: true },
);

watch(showInProductUnitModal, (open) => {
  if (!open) return;
  nextTick(() => {
    const el = document.getElementById('product-unit-search-input') as HTMLInputElement | null;
    el?.focus();
  });
});

watch(showOutProductUnitModal, (open) => {
  if (!open) return;
  nextTick(() => {
    const el = document.getElementById('product-unit-search-input') as HTMLInputElement | null;
    el?.focus();
  });
});

watch(
  stockAdjustmentForm,
  debounce((newValue): void => {
    cacheServices.setLastEntity('STOCK_ADJUSTMENT_CREATE', newValue.data());
  }, 500),
  { deep: true },
);

onMounted(() => {
  emits('mode-state', ViewMode.FORM_CREATE);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  loadFromCache();

  stockAdjustmentForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
  });

  loadCategoryDDL();
  loadInWarehouseDDL();
  loadOutWarehouseDDL();
});
// #endregion

// #region Methods - Stock Adjustment
const setCode = () => {
  stockAdjustmentForm.forgetError('code');
  if (stockAdjustmentForm.code === '_AUTO_') {
    stockAdjustmentForm.setData({ code: '' });
  } else {
    stockAdjustmentForm.setData({ code: '_AUTO_' });
  }
};

const loadInWarehouseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await warehouseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    status: undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    inWarehouseDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const clearInWarehouse = () => {
  stockAdjustmentForm.setData({ in_warehouse_id: '' });
  loadInWarehouseDDL('');
  stockAdjustmentForm.forgetError('in_warehouse_id');
  stockAdjustmentForm.validate('in_warehouse_id');
};

const loadOutWarehouseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await warehouseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    status: undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    outWarehouseDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const clearOutWarehouse = () => {
  stockAdjustmentForm.setData({ out_warehouse_id: '' });
  loadOutWarehouseDDL('');
  stockAdjustmentForm.forgetError('out_warehouse_id');
  stockAdjustmentForm.validate('out_warehouse_id');
};

const loadCategoryDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await stockAdjustmentCategoryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: undefined,
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

const clearCategory = () => {
  stockAdjustmentForm.setData({ category_id: '' });
  loadCategoryDDL('');
  stockAdjustmentForm.forgetError('category_id');
  stockAdjustmentForm.validate('category_id');
};

const loadFromCache = () => {
  const data = cacheServices.getLastEntity('STOCK_ADJUSTMENT_CREATE') as Record<string, unknown>;
  if (!data) return;
  stockAdjustmentForm.setData(data);
};
// #endregion

// #region Methods - In Products
const addInProduct = () => {
  productSearchTextIn.value = '';
  productUnitOptionsIn.value = [];
  isSearchingInProductUnit.value = false;
  editingInProductIndex.value = null;
  showInProductUnitModal.value = true;
};

const changeInProductProductUnit = (index: number) => {
  productSearchTextIn.value = '';
  productUnitOptionsIn.value = [];
  isSearchingInProductUnit.value = false;
  editingInProductIndex.value = index;
  showInProductUnitModal.value = true;
};

const searchInProductUnits = async () => {
  if (!selectedUserLocation.value) return;

  if (!stockAdjustmentForm.in_warehouse_id) {
    stockAdjustmentForm.validate('in_warehouse_id');
    return;
  }

  if (!stockAdjustmentForm.date) {
    stockAdjustmentForm.validate('date');
    return;
  }

  isSearchingInProductUnit.value = true;

  let endDate: string;
  if (stockAdjustmentForm.date === '_AUTO_') {
    endDate = formatDate(new Date().toString(), 'YYYY-MM-DD HH:mm:ss');
  } else {
    endDate = stockAdjustmentForm.date as string;
  }

  const result = await productService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search: productSearchTextIn.value,
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
      warehouse_id: stockAdjustmentForm.in_warehouse_id as string,
      include_service_products: false,
    },
    refresh: true,
    limit: 20,
  });

  isSearchingInProductUnit.value = false;

  if (result.success && result.data) {
    const products = result.data.data as any[];
    productUnitOptionsIn.value = products.flatMap((p: any) => {
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
          product_unit_id: u.id,
          product_unit_code: u.code,
          unit_name: u.unit ? u.unit.name : '',
          base_unit_name: baseUnitName,
          conversion_value: conversionValue,
          cogs: Number(u.price),
          remaining_stock: remainingStock,
        };
      });
    });
  } else {
    productUnitOptionsIn.value = [];
  }
};

const selectProductUnitForIn = (option: ProductUnitOption) => {
  const baseData: Partial<StockAdjustmentInProductFormItem> = {
    product_unit_id: option.product_unit_id,
    product_unit_product_code: option.product_unit_code,
    product_unit_product_name: option.product_name,
    product_unit_unit_name: option.unit_name,
    product_unit_base_unit_name: option.conversion_value != 1 ? option.base_unit_name : '',
    product_unit_conversion_value: option.conversion_value,
    product_unit_cogs: option.cogs,
  };

  let targetIndex: number;

  if (editingInProductIndex.value === null) {
    const item: StockAdjustmentInProductFormItem = {
      qty: 0,
      remarks: '',
      ...baseData,
    } as StockAdjustmentInProductFormItem;

    stockAdjustmentForm.in_products.push(item);
    inProductsRemarksExpanded.value.push(false);
    targetIndex = stockAdjustmentForm.in_products.length - 1;
  } else {
    const index = editingInProductIndex.value;
    const current = stockAdjustmentForm.in_products[index] as StockAdjustmentInProductFormItem;
    stockAdjustmentForm.in_products[index] = {
      ...current,
      ...baseData,
    };
    targetIndex = index;
  }

  showInProductUnitModal.value = false;
  editingInProductIndex.value = null;
  inProductQtyToFocus.value = targetIndex;
};

const toggleInProductRemarks = (index: number) => {
  const current = inProductsRemarksExpanded.value[index] ?? false;
  inProductsRemarksExpanded.value[index] = !current;
};

const handleInProductUnitModalAfterLeave = () => {
  const inIndex = inProductQtyToFocus.value;

  inProductQtyToFocus.value = null;

  if (inIndex === null) return;

  nextTick(() => {
    const el = document.getElementById(`in-product-qty-${inIndex}`) as HTMLInputElement | null;
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el?.focus();
    el?.select();
  });
};

const removeInProduct = (index: number) => {
  stockAdjustmentForm.in_products.splice(index, 1);
  inProductsRemarksExpanded.value.splice(index, 1);
};
// #endregion

// #region Methods - Out Products
const addOutProduct = () => {
  productSearchTextOut.value = '';
  productUnitOptionsOut.value = [];
  isSearchingOutProductUnit.value = false;
  editingOutProductIndex.value = null;
  showOutProductUnitModal.value = true;
};

const changeOutProductProductUnit = (index: number) => {
  productSearchTextOut.value = '';
  productUnitOptionsOut.value = [];
  isSearchingOutProductUnit.value = false;
  editingOutProductIndex.value = index;
  showOutProductUnitModal.value = true;
};

const searchOutProductUnits = async () => {
  if (!selectedUserLocation.value) return;

  if (!stockAdjustmentForm.out_warehouse_id) {
    stockAdjustmentForm.validate('out_warehouse_id');
    return;
  }

  if (!stockAdjustmentForm.date) {
    stockAdjustmentForm.validate('date');
    return;
  }

  isSearchingOutProductUnit.value = true;

  let endDate: string;
  if (stockAdjustmentForm.date === '_AUTO_') {
    endDate = formatDate(new Date().toString(), 'YYYY-MM-DD HH:mm:ss');
  } else {
    endDate = stockAdjustmentForm.date as string;
  }

  const result = await productService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search: productSearchTextOut.value,
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
      warehouse_id: stockAdjustmentForm.out_warehouse_id as string,
      stock_filter: 'has_stock',
      include_service_products: false,
    },
    refresh: true,
    limit: 20,
  });

  isSearchingOutProductUnit.value = false;

  if (result.success && result.data) {
    const products = result.data.data as any[];
    productUnitOptionsOut.value = products.flatMap((p: any) => {
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
          product_unit_id: u.id,
          product_unit_code: u.code,
          unit_name: u.unit ? u.unit.name : '',
          base_unit_name: baseUnitName,
          conversion_value: conversionValue,
          cogs: Number(u.price),
          remaining_stock: remainingStock,
        };
      });
    });
  } else {
    productUnitOptionsOut.value = [];
  }
};

const selectProductUnitForOut = (option: ProductUnitOption) => {
  const baseData: Partial<StockAdjustmentOutProductFormItem> = {
    product_unit_id: option.product_unit_id,
    product_unit_product_code: option.product_unit_code,
    product_unit_product_name: option.product_name,
    product_unit_unit_name: option.unit_name,
    product_unit_base_unit_name: option.conversion_value != 1 ? option.base_unit_name : '',
    product_unit_conversion_value: option.conversion_value,
  };

  let targetIndex: number;

  if (editingOutProductIndex.value === null) {
    const item: StockAdjustmentOutProductFormItem = {
      qty: 0,
      remarks: '',
      ...baseData,
    } as StockAdjustmentOutProductFormItem;

    stockAdjustmentForm.out_products.push(item);
    outProductsRemarksExpanded.value.push(false);
    targetIndex = stockAdjustmentForm.out_products.length - 1;
  } else {
    const index = editingOutProductIndex.value;
    const current = stockAdjustmentForm.out_products[index] as StockAdjustmentOutProductFormItem;
    stockAdjustmentForm.out_products[index] = {
      ...current,
      ...baseData,
    };
    targetIndex = index;
  }

  showOutProductUnitModal.value = false;
  editingOutProductIndex.value = null;
  outProductQtyToFocus.value = targetIndex;
};

const toggleOutProductRemarks = (index: number) => {
  const current = outProductsRemarksExpanded.value[index] ?? false;
  outProductsRemarksExpanded.value = [
    ...outProductsRemarksExpanded.value.slice(0, index),
    !current,
    ...outProductsRemarksExpanded.value.slice(index + 1),
  ];
};

const handleOutProductUnitModalAfterLeave = () => {
  const outIndex = outProductQtyToFocus.value;

  outProductQtyToFocus.value = null;

  if (outIndex === null) return;

  nextTick(() => {
    const el = document.getElementById(`out-product-qty-${outIndex}`) as HTMLInputElement | null;
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el?.focus();
    el?.select();
  });
};

const removeOutProduct = (index: number) => {
  stockAdjustmentForm.out_products.splice(index, 1);
  outProductsRemarksExpanded.value.splice(index, 1);
};
// #endregion

// #region Actions
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

const resetForm = () => {
  stockAdjustmentForm.reset();
  stockAdjustmentForm.setErrors({});
  inProductsRemarksExpanded.value = [];
  outProductsRemarksExpanded.value = [];
};

const onSubmit = async () => {
  if (stockAdjustmentForm.hasErrors) {
    const firstErrorKey = Object.keys(stockAdjustmentForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  const originalInProducts = stockAdjustmentForm.in_products as StockAdjustmentInProductFormItem[];
  const cleanedInProducts: StockAdjustmentInProductNestedStoreRequest[] = originalInProducts.map(
    ({
      product_unit_product_code,
      product_unit_product_name,
      product_unit_unit_name,
      product_unit_base_unit_name,
      product_unit_total_cogs,
      ...rest
    }: StockAdjustmentInProductFormItem) => rest,
  );

  const originalOutProducts = stockAdjustmentForm.out_products as StockAdjustmentOutProductFormItem[];
  const cleanedOutProducts: StockAdjustmentOutProductNestedStoreRequest[] = originalOutProducts.map(
    ({
      product_unit_product_code,
      product_unit_product_name,
      product_unit_unit_name,
      product_unit_base_unit_name,
      ...rest
    }: StockAdjustmentOutProductFormItem) => rest,
  );

  const backupInProducts = [...originalInProducts];
  const backupOutProducts = [...originalOutProducts];

  stockAdjustmentForm.in_products = cleanedInProducts as any;
  stockAdjustmentForm.out_products = cleanedOutProducts as any;

  emits('loading-state', true);

  try {
    await stockAdjustmentForm.submit();
    resetForm();
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    router.push({ name: 'side-menu-stock-adjustment-list' });
  } catch (error) {
    stockAdjustmentForm.in_products = backupInProducts as any;
    stockAdjustmentForm.out_products = backupOutProducts as any;
    const errorList: Record<string, Array<string>> = convertErrorTypeToAlertListType(error);
    showAlertPlaceholder('danger', '', errorList);
  } finally {
    emits('loading-state', false);
  }
};
// #endregion
</script>

<template>
  <form id="stockAdjustmentForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <!-- company & branch -->
      <template #card-items-0>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <!-- company -->
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.company.code }}
                <br />
                {{ selectedUserLocation.company.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="stockAdjustmentForm.company_id" />
            </div>

            <!-- branch -->
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="stockAdjustmentForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <!-- stock_adjustment -->
      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <!-- code -->
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel :class="{ 'text-danger': stockAdjustmentForm.invalid('code') }">
                {{ t('views.stock_adjustment.fields.code') }}
              </FormLabel>
              <FormInputCode v-model="stockAdjustmentForm.code" :class="{
                'border-danger': stockAdjustmentForm.invalid('code'),
              }" :placeholder="t('views.stock_adjustment.fields.code')" @set-auto="setCode"
                @change="stockAdjustmentForm.validate('code')" />
              <FormErrorMessages :messages="stockAdjustmentForm.errors.code" />
            </div>
            <!-- date -->
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel :class="{ 'text-danger': stockAdjustmentForm.invalid('date') }">
                {{ t('views.stock_adjustment.fields.date') }}
              </FormLabel>
              <FormInputDateTime
                v-model="stockAdjustmentForm.date"
                :class="{
                  'border-danger': stockAdjustmentForm.invalid('date'),
                }"
                :placeholder="t('views.stock_adjustment.fields.date')"
                @change="stockAdjustmentForm.validate('date')"
              />
              <FormErrorMessages :messages="stockAdjustmentForm.errors.date" />
            </div>
            <!-- category -->
            <div class="col-span-12 lg:col-span-4">
              <FormLabel :class="{
                'text-danger': stockAdjustmentForm.invalid('category_id'),
              }">
                {{ t('views.stock_adjustment.fields.category_id') }}
              </FormLabel>
              <div class="flex items-center gap-2">
                <div class="flex-1">
                  <FormSelectSearch v-model="stockAdjustmentForm.category_id" v-model:search="categorySearch"
                    :options="categoryOptions" :placeholder="t('components.dropdown.placeholder')" :class="{
                      'border-danger': stockAdjustmentForm.invalid('category_id'),
                    }" @change="stockAdjustmentForm.validate('category_id')" @search="loadCategoryDDL" />
                </div>
                <button v-if="stockAdjustmentForm.category_id" type="button" class="text-slate-500 hover:text-danger"
                  @click="clearCategory">
                  <Lucide icon="X" class="w-4 h-4" />
                </button>
              </div>
              <FormErrorMessages :messages="stockAdjustmentForm.errors.category_id" />
            </div>
            <!-- in_warehouse -->
            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{
                'text-danger': stockAdjustmentForm.invalid('in_warehouse_id'),
              }">
                {{ t('views.stock_adjustment.fields.in_warehouse_id') }}
              </FormLabel>
              <div class="flex items-center gap-2">
                <div class="flex-1">
                  <FormSelectSearch v-model="stockAdjustmentForm.in_warehouse_id" v-model:search="inWarehouseSearch"
                    :options="inWarehouseOptions" :placeholder="t('components.dropdown.placeholder')" :class="{
                      'border-danger': stockAdjustmentForm.invalid('in_warehouse_id'),
                    }" @change="stockAdjustmentForm.validate('in_warehouse_id')" @search="loadInWarehouseDDL" />
                </div>
                <button v-if="stockAdjustmentForm.in_warehouse_id" type="button"
                  class="text-slate-500 hover:text-danger" @click="clearInWarehouse">
                  <Lucide icon="X" class="w-4 h-4" />
                </button>
              </div>
              <FormErrorMessages :messages="stockAdjustmentForm.errors.in_warehouse_id" />
            </div>
            <!-- out_warehouse -->
            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{
                'text-danger': stockAdjustmentForm.invalid('out_warehouse_id'),
              }">
                {{ t('views.stock_adjustment.fields.out_warehouse_id') }}
              </FormLabel>
              <div class="flex items-center gap-2">
                <div class="flex-1">
                  <FormSelectSearch v-model="stockAdjustmentForm.out_warehouse_id" v-model:search="outWarehouseSearch"
                    :options="outWarehouseOptions" :placeholder="t('components.dropdown.placeholder')" :class="{
                      'border-danger': stockAdjustmentForm.invalid('out_warehouse_id'),
                    }" @change="stockAdjustmentForm.validate('out_warehouse_id')" @search="loadOutWarehouseDDL" />
                </div>
                <button v-if="stockAdjustmentForm.out_warehouse_id" type="button"
                  class="text-slate-500 hover:text-danger" @click="clearOutWarehouse">
                  <Lucide icon="X" class="w-4 h-4" />
                </button>
              </div>
              <FormErrorMessages :messages="stockAdjustmentForm.errors.out_warehouse_id" />
            </div>
            <!-- remarks -->
            <div class="col-span-12">
              <FormLabel :class="{
                'text-danger': stockAdjustmentForm.invalid('remarks'),
              }">
                {{ t('views.stock_adjustment.fields.remarks') }}
              </FormLabel>
              <FormTextarea v-model="stockAdjustmentForm.remarks" rows="3" :class="{
                'border-danger': stockAdjustmentForm.invalid('remarks'),
              }" :placeholder="t('views.stock_adjustment.fields.remarks')"
                @change="stockAdjustmentForm.validate('remarks')" />
              <FormErrorMessages :messages="stockAdjustmentForm.errors.remarks" />
            </div>
            <!-- is_posted -->
            <div class="col-span-12">
              <FormLabel class="pr-5">
                {{ t('views.stock_adjustment.fields.is_posted') }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input v-model="stockAdjustmentForm.is_posted" type="checkbox" />
              </FormSwitch>
            </div>
          </div>
        </div>
      </template>

      <!-- in_product -->
      <template #card-items-2>
        <div class="p-5">
          <div v-if="stockAdjustmentForm.in_products.length === 0" class="text-slate-500 text-sm">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <!-- in_product list -->
          <div v-else class="space-y-5">
            <div v-for="(item, index) in inProductsForm" :key="index"
              class="border border-slate-200/60 dark:border-darkmode-400 rounded-md p-4">
              <!-- in_product actions -->
              <div class="flex items-center justify-between mb-3">
                <div class="font-medium text-sm">
                  {{ t('views.stock_adjustment_in_product.page_title') }} #{{ index + 1 }}
                </div>
                <div class="flex items-center gap-2">
                  <Button type="button" class="text-xs text-slate-500 hover:text-primary"
                    @click="toggleInProductRemarks(index)">
                    {{ inProductsRemarksExpanded[index] ? '▲' : '▼' }}
                  </Button>
                  <Button type="button" variant="outline-secondary" @click="removeInProduct(index)">
                    <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                  </Button>
                </div>
              </div>

              <!-- in_product fields -->
              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <!-- qty -->
                <div class="col-span-12 lg:col-span-2">
                  <FormLabel :class="{
                    'text-danger': stockAdjustmentForm.invalid(`in_products.${index}.qty` as any),
                  }">
                    {{ t('views.stock_adjustment_in_product.fields.qty') }}
                  </FormLabel>
                  <FormInputCurrency :id="`in-product-qty-${index}`" v-model="item.qty"
                    @change="stockAdjustmentForm.validate(`in_products.${index}.qty` as any)" :class="[
                      'text-right',
                      {
                        'border-danger': stockAdjustmentForm.invalid(`in_products.${index}.qty` as any),
                      },
                    ]" />
                  <FormErrorMessages :messages="(stockAdjustmentForm.errors as any)[`in_products.${index}.qty`]" />
                  <div class="text-sm text-slate-500 text-right font-bold mt-1">
                    {{ item.product_unit_unit_name }}
                  </div>
                </div>

                <!-- product description (name + code) -->
                <div class="col-span-12 lg:col-span-5">
                  <FormLabel :class="{
                    'text-danger': stockAdjustmentForm.invalid(`in_products.${index}.product_unit_id` as any),
                  }">
                    {{ t('views.stock_adjustment_in_product.table.cols.product') }}
                  </FormLabel>
                  <div class="flex items-center gap-2">
                    <div class="flex-1">
                      <div
                        class="form-control border rounded-md px-3 py-2 bg-slate-50 dark:bg-darkmode-800 text-slate-700 dark:text-slate-300">
                        {{ item.product_unit_product_name || '-' }}
                      </div>
                    </div>
                    <Button type="button" variant="outline-secondary" tabindex="-1"
                      class="flex items-center justify-center border-slate-500 text-slate-500 hover:text-primary hover:border-primary"
                      @click="changeInProductProductUnit(index)">
                      <Lucide icon="Search" class="w-4 h-4" />
                    </Button>
                  </div>
                  <FormErrorMessages
                    :messages="(stockAdjustmentForm.errors as any)[`in_products.${index}.product_unit_id`]" />
                  <div class="text-sm text-slate-500 font-bold mt-1">
                    {{ item.product_unit_product_code }}
                  </div>
                </div>

                <!-- product_unit_conversion_value -->
                <div class="col-span-12 lg:col-span-1">
                  <FormLabel :class="{
                    'text-danger': stockAdjustmentForm.invalid(
                      `in_products.${index}.product_unit_conversion_value` as any,
                    ),
                  }">
                    {{ t('views.stock_adjustment_in_product.fields.product_unit_conversion_value') }}
                  </FormLabel>
                  <FormInputCurrency v-model="item.product_unit_conversion_value" tabindex="-1"
                    @change="stockAdjustmentForm.validate(`in_products.${index}.product_unit_conversion_value` as any)"
                    :class="[
                      'text-right',
                      {
                        'border-danger': stockAdjustmentForm.invalid(
                          `in_products.${index}.product_unit_conversion_value` as any,
                        ),
                      },
                    ]" />
                  <FormErrorMessages :messages="(stockAdjustmentForm.errors as any)[`in_products.${index}.product_unit_conversion_value`]
                    " />
                  <div class="text-sm text-slate-500 text-right font-bold mt-1">
                    {{ item.product_unit_base_unit_name }}
                  </div>
                </div>

                <!-- product_unit_cogs -->
                <div class="col-span-12 lg:col-span-2">
                  <FormLabel :class="{
                    'text-danger': stockAdjustmentForm.invalid(`in_products.${index}.product_unit_cogs` as any),
                  }">
                    {{ t('views.stock_adjustment_in_product.fields.product_unit_cogs') }}
                  </FormLabel>
                  <FormInputCurrency v-model="item.product_unit_cogs"
                    @change="stockAdjustmentForm.validate(`in_products.${index}.product_unit_cogs` as any)" :class="[
                      'text-right',
                      {
                        'border-danger': stockAdjustmentForm.invalid(`in_products.${index}.product_unit_cogs` as any),
                      },
                    ]" />
                  <FormErrorMessages
                    :messages="(stockAdjustmentForm.errors as any)[`in_products.${index}.product_unit_cogs`]" />
                  <div v-if="
                    item.product_unit_conversion_value > 1 &&
                    item.product_unit_cogs &&
                    item.product_unit_base_unit_name
                  " class="text-sm text-slate-500 text-right font-bold mt-1">
                    {{ formatCurrency((item.product_unit_cogs / item.product_unit_conversion_value).toFixed(2)) }}
                    / {{ item.product_unit_base_unit_name }}
                  </div>
                </div>

                <!-- product_unit_total_cogs -->
                <div class="col-span-12 lg:col-span-2">
                  <FormLabel>
                    {{ t('views.stock_adjustment_in_product.fields.product_unit_total_cogs') }}
                  </FormLabel>
                  <FormInputCurrency :model-value="item.product_unit_total_cogs ?? 0" readonly tabindex="-1"
                    class="text-right" />
                </div>

                <!-- product_unit_remarks -->
                <div v-if="inProductsRemarksExpanded[index]" class="col-span-12">
                  <FormLabel :class="{
                    'text-danger': stockAdjustmentForm.invalid(`in_products.${index}.remarks` as any),
                  }">
                    {{ t('views.stock_adjustment_in_product.fields.remarks') }}
                  </FormLabel>
                  <FormTextarea rows="2" v-model="stockAdjustmentForm.in_products[index].remarks" :class="{
                    'border-danger': stockAdjustmentForm.invalid(`in_products.${index}.remarks` as any),
                  }" @change="stockAdjustmentForm.validate(`in_products.${index}.remarks` as any)" />
                  <FormErrorMessages :messages="(stockAdjustmentForm.errors as any)[`in_products.${index}.remarks`]" />
                </div>
              </div>
            </div>
          </div>

          <!-- in_product actions -->
          <div class="flex items-center justify-between mt-4">
            <FormLabel>
              <!-- {{ t("views.stock_adjustment.field_groups.in_products") }} -->
            </FormLabel>
            <Button type="button" variant="primary" class="shadow-md" @click="addInProduct">
              {{ t('views.stock_adjustment_in_product.actions.create') }}
            </Button>
          </div>
        </div>
      </template>

      <!-- out_product -->
      <template #card-items-3>
        <div class="p-5">
          <div v-if="stockAdjustmentForm.out_products.length === 0" class="text-slate-500 text-sm">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <!-- out_product list -->
          <div v-else class="space-y-5">
            <div v-for="(item, index) in outProductsForm" :key="index"
              class="border border-slate-200/60 dark:border-darkmode-400 rounded-md p-4">
              <!-- out_product actions -->
              <div class="flex items-center justify-between mb-3">
                <div class="font-medium text-sm">
                  {{ t('views.stock_adjustment_out_product.page_title') }} #{{ index + 1 }}
                </div>
                <div class="flex items-center gap-2">
                  <Button type="button" class="text-xs text-slate-500 hover:text-primary"
                    @click="toggleOutProductRemarks(index)">
                    {{ outProductsRemarksExpanded[index] ? '▲' : '▼' }}
                  </Button>
                  <Button type="button" variant="outline-secondary" @click="removeOutProduct(index)">
                    <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                  </Button>
                </div>
              </div>

              <!-- out_product fields -->
              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <!-- qty -->
                <div class="col-span-12 lg:col-span-2">
                  <FormLabel :class="{
                    'text-danger': stockAdjustmentForm.invalid(`out_products.${index}.qty` as any),
                  }">
                    {{ t('views.stock_adjustment_out_product.fields.qty') }}
                  </FormLabel>
                  <FormInputCurrency :id="`out-product-qty-${index}`" v-model="item.qty"
                    @change="stockAdjustmentForm.validate(`out_products.${index}.qty` as any)" :class="[
                      'text-right',
                      {
                        'border-danger': stockAdjustmentForm.invalid(`out_products.${index}.qty` as any),
                      },
                    ]" />
                  <FormErrorMessages :messages="(stockAdjustmentForm.errors as any)[`out_products.${index}.qty`]" />
                  <div class="text-sm text-slate-500 text-right font-bold mt-1">
                    {{ item.product_unit_unit_name }}
                  </div>
                </div>

                <!-- product description (name + code) -->
                <div class="col-span-12 lg:col-span-8">
                  <FormLabel :class="{
                    'text-danger': stockAdjustmentForm.invalid(`out_products.${index}.product_unit_id` as any),
                  }">
                    {{ t('views.stock_adjustment_out_product.table.cols.product') }}
                  </FormLabel>
                  <div class="flex items-center gap-2">
                    <div class="flex-1">
                      <div
                        class="form-control border rounded-md px-3 py-2 bg-slate-50 dark:bg-darkmode-800 text-slate-700 dark:text-slate-300">
                        {{ item.product_unit_product_name || '-' }}
                      </div>
                    </div>
                    <Button type="button" variant="outline-secondary" tabindex="-1"
                      class="flex items-center justify-center border-slate-500 text-slate-500 hover:text-primary hover:border-primary"
                      @click="changeOutProductProductUnit(index)">
                      <Lucide icon="Search" class="w-4 h-4" />
                    </Button>
                  </div>
                  <FormErrorMessages
                    :messages="(stockAdjustmentForm.errors as any)[`out_products.${index}.product_unit_id`]" />
                  <div class="text-sm text-slate-500 font-bold mt-1">
                    {{ item.product_unit_product_code }}
                  </div>
                </div>

                <!-- product_unit_conversion_value -->
                <div class="col-span-12 lg:col-span-2">
                  <FormLabel :class="{
                    'text-danger': stockAdjustmentForm.invalid(
                      `out_products.${index}.product_unit_conversion_value` as any,
                    ),
                  }">
                    {{ t('views.stock_adjustment_out_product.fields.product_unit_conversion_value') }}
                  </FormLabel>
                  <FormInputCurrency v-model="item.product_unit_conversion_value" tabindex="-1"
                    @change="stockAdjustmentForm.validate(`out_products.${index}.product_unit_conversion_value` as any)"
                    :class="[
                      'text-right',
                      {
                        'border-danger': stockAdjustmentForm.invalid(
                          `out_products.${index}.product_unit_conversion_value` as any,
                        ),
                      },
                    ]" />
                  <FormErrorMessages :messages="(stockAdjustmentForm.errors as any)[`out_products.${index}.product_unit_conversion_value`]
                    " />
                  <div class="text-sm text-slate-500 text-right font-bold mt-1">
                    {{ item.product_unit_base_unit_name }}
                  </div>
                </div>

                <!-- remarks -->
                <div v-if="outProductsRemarksExpanded[index]" class="col-span-12">
                  <FormLabel :class="{
                    'text-danger': stockAdjustmentForm.invalid(`out_products.${index}.remarks` as any),
                  }">
                    {{ t('views.stock_adjustment_out_product.fields.remarks') }}
                  </FormLabel>
                  <FormTextarea rows="2" v-model="stockAdjustmentForm.out_products[index].remarks" :class="{
                    'border-danger': stockAdjustmentForm.invalid(`out_products.${index}.remarks` as any),
                  }" @change="stockAdjustmentForm.validate(`out_products.${index}.remarks` as any)" />
                  <FormErrorMessages :messages="(stockAdjustmentForm.errors as any)[`out_products.${index}.remarks`]" />
                </div>
              </div>
            </div>
          </div>

          <!-- out_product actions -->
          <div class="flex items-center justify-between mt-4">
            <FormLabel>
              <!-- {{ t("views.stock_adjustment.field_groups.out_products") }} -->
            </FormLabel>
            <Button type="button" variant="primary" class="shadow-md" @click="addOutProduct">
              {{ t('views.stock_adjustment_out_product.actions.create') }}
            </Button>
          </div>
        </div>
      </template>

      <!-- stock_adjustment actions -->
      <template #card-items-button>
        <div class="flex gap-4 p-5">
          <Button type="submit" href="#" variant="primary" class="w-28 shadow-md"
            :disabled="stockAdjustmentForm.validating || stockAdjustmentForm.hasErrors">
            <Lucide v-if="stockAdjustmentForm.validating" icon="Loader" class="animate-spin" />
            <template v-else>
              {{ t('components.buttons.submit') }}
            </template>
          </Button>
          <Button type="button" href="#" variant="soft-secondary" class="w-28 shadow-md" @click="resetForm">
            {{ t('components.buttons.reset') }}
          </Button>
        </div>
      </template>
    </TwoColumnsLayout>

    <!-- product_unit modal - IN -->
    <Dialog size="xl" :open="showInProductUnitModal" @close="
      () => {
        showInProductUnitModal = false;
      }
    " @after-leave="handleInProductUnitModalAfterLeave">
      <Dialog.Panel>
        <div class="p-5">
          <!-- modal header -->
          <div class="flex items-center justify-between mb-4">
            <FormLabel>
              {{ t('views.stock_adjustment_in_product.table.title') }}
            </FormLabel>
            <button type="button" class="text-slate-500 hover:text-danger" @click="showInProductUnitModal = false">
              <Lucide icon="X" class="w-4 h-4" />
            </button>
          </div>

          <!-- search box -->
          <div class="flex items-center gap-2 mb-4">
            <FormInput id="product-unit-search-input" v-model="productSearchTextIn" type="text"
              :placeholder="t('components.search-box.placeholder.search')" @keyup.enter="searchInProductUnits" />
            <Button type="button" variant="primary" class="shadow-md" @click="searchInProductUnits" tabindex="-1"
              :disabled="isSearchingInProductUnit || !stockAdjustmentForm.in_warehouse_id">
              <template v-if="isSearchingInProductUnit">
                <Lucide icon="Loader" class="w-4 h-4 animate-spin" />
              </template>
              <template v-else>
                {{ t('components.buttons.search') }}
              </template>
            </Button>
          </div>

          <!-- product unit table -->
          <div class="max-h-80 overflow-auto border border-slate-200/60 dark:border-darkmode-400 rounded-md">
            <table class="min-w-full text-sm">
              <thead class="bg-slate-100 dark:bg-darkmode-600">
                <tr>
                  <!-- product -->
                  <th class="px-3 py-2 text-left">
                    {{ t('views.stock_adjustment_in_product.table.cols.product') }}
                  </th>
                  <!-- remaining_stock -->
                  <th class="px-3 py-2 text-right">
                    {{ t('views.stock_adjustment_in_product.table.cols.remaining_stock') }}
                  </th>
                  <!-- unit -->
                  <th class="px-3 py-2 text-left">
                    {{ t('views.product.table.cols.unit') }}
                  </th>
                  <!-- conversion_value -->
                  <th class="px-3 py-2 text-right">
                    {{ t('views.product.fields.conversion_value') }}
                  </th>
                  <!-- product_unit_cogs -->
                  <th class="px-3 py-2 text-right">
                    {{ t('views.stock_adjustment_in_product.table.cols.product_unit_cogs') }}
                  </th>
                  <!-- actions -->
                  <th class="px-3 py-2"></th>
                </tr>
              </thead>
              <tbody>
                <!-- empty state -->
                <tr v-if="productUnitOptionsIn.length === 0">
                  <td colspan="6" class="px-3 py-4 text-center text-slate-500">
                    {{ t('components.data-list.data_not_found') }}
                  </td>
                </tr>

                <!-- product unit rows -->
                <tr v-for="(opt, idx) in productUnitOptionsIn" :key="idx"
                  class="border-t border-slate-200/60 dark:border-darkmode-400">
                  <!-- product -->
                  <td class="px-3 py-2">[{{ opt.product_unit_code }}] {{ opt.product_name }}</td>
                  <!-- remaining_stock -->
                  <td class="px-3 py-2 text-right">
                    {{ formatCurrency(opt.remaining_stock) }}
                  </td>
                  <!-- unit -->
                  <td class="px-3 py-2">
                    {{ opt.unit_name }}
                  </td>
                  <!-- conversion_value -->
                  <td class="px-3 py-2 text-right">
                    {{ formatCurrency(opt.conversion_value) }}
                  </td>
                  <!-- product_unit_cogs -->
                  <td class="px-3 py-2 text-right">
                    {{ formatCurrency(opt.cogs) }}
                  </td>
                  <!-- select button -->
                  <td class="px-3 py-2 text-right">
                    <Button type="button" variant="primary" size="sm" @click="selectProductUnitForIn(opt)">
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

    <!-- product_unit modal - OUT -->
    <Dialog size="xl" :open="showOutProductUnitModal" @close="
      () => {
        showOutProductUnitModal = false;
      }
    " @after-leave="handleOutProductUnitModalAfterLeave">
      <Dialog.Panel>
        <div class="p-5">
          <!-- modal header -->
          <div class="flex items-center justify-between mb-4">
            <FormLabel>
              {{ t('views.stock_adjustment_out_product.table.title') }}
            </FormLabel>
            <button type="button" class="text-slate-500 hover:text-danger" @click="showOutProductUnitModal = false">
              <Lucide icon="X" class="w-4 h-4" />
            </button>
          </div>

          <!-- search box -->
          <div class="flex items-center gap-2 mb-4">
            <FormInput id="product-unit-search-input" v-model="productSearchTextOut" type="text"
              :placeholder="t('components.search-box.placeholder.search')" @keyup.enter="searchOutProductUnits" />
            <Button type="button" variant="primary" class="shadow-md" @click="searchOutProductUnits" tabindex="-1"
              :disabled="isSearchingOutProductUnit || !stockAdjustmentForm.out_warehouse_id">
              <template v-if="isSearchingOutProductUnit">
                <Lucide icon="Loader" class="w-4 h-4 animate-spin" />
              </template>
              <template v-else>
                {{ t('components.buttons.search') }}
              </template>
            </Button>
          </div>

          <!-- product unit table -->
          <div class="max-h-80 overflow-auto border border-slate-200/60 dark:border-darkmode-400 rounded-md">
            <table class="min-w-full text-sm">
              <thead class="bg-slate-100 dark:bg-darkmode-600">
                <tr>
                  <!-- product -->
                  <th class="px-3 py-2 text-left">
                    {{ t('views.stock_adjustment_out_product.table.cols.product') }}
                  </th>
                  <!-- remaining_stock -->
                  <th class="px-3 py-2 text-right">
                    {{ t('views.stock_adjustment_out_product.table.cols.remaining_stock') }}
                  </th>
                  <!-- unit -->
                  <th class="px-3 py-2 text-left">
                    {{ t('views.product.table.cols.unit') }}
                  </th>
                  <!-- conversion_value -->
                  <th class="px-3 py-2 text-right">
                    {{ t('views.product.fields.conversion_value') }}
                  </th>
                  <!-- product_unit_cogs -->
                  <th class="px-3 py-2 text-right">
                    {{ t('views.stock_adjustment_out_product.table.cols.product_unit_cogs') }}
                  </th>
                  <!-- actions -->
                  <th class="px-3 py-2"></th>
                </tr>
              </thead>
              <tbody>
                <!-- empty state -->
                <tr v-if="productUnitOptionsOut.length === 0">
                  <td colspan="6" class="px-3 py-4 text-center text-slate-500">
                    {{ t('components.data-list.data_not_found') }}
                  </td>
                </tr>

                <!-- product unit rows -->
                <tr v-for="(opt, idx) in productUnitOptionsOut" :key="idx"
                  class="border-t border-slate-200/60 dark:border-darkmode-400">
                  <!-- product -->
                  <td class="px-3 py-2">[{{ opt.product_unit_code }}] {{ opt.product_name }}</td>
                  <!-- remaining_stock -->
                  <td class="px-3 py-2 text-right">
                    {{ formatCurrency(opt.remaining_stock) }}
                  </td>
                  <!-- unit -->
                  <td class="px-3 py-2">
                    {{ opt.unit_name }}
                  </td>
                  <!-- conversion_value -->
                  <td class="px-3 py-2 text-right">
                    {{ formatCurrency(opt.conversion_value) }}
                  </td>
                  <!-- product_unit_cogs -->
                  <td class="px-3 py-2 text-right">
                    {{ formatCurrency(opt.cogs) }}
                  </td>
                  <!-- select button -->
                  <td class="px-3 py-2 text-right">
                    <Button type="button" variant="primary" size="sm" @click="selectProductUnitForOut(opt)">
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
  </form>
</template>
