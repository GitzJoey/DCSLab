<script setup lang="ts">
// #region Imports
import { computed, ref, onMounted, nextTick, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
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
import { formatDate, formatCurrency, convertErrorTypeToAlertListType } from '@/utils/helper';
import StockAdjustmentService from '@/services/StockAdjustmentService';
import StockAdjustmentCategoryService from '@/services/StockAdjustmentCategoryService';
import CacheService from '@/services/CacheService';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { type DropDownOption } from '@/types/models/DropDownOption';
import {
  type StockAdjustmentInItemNestedUpdateRequest,
  type StockAdjustmentOutItemNestedUpdateRequest,
} from '@/types/services/stock-adjustment/StockAdjustmentRequest';
import type { StockAdjustment } from '@/types/models/StockAdjustment';
import { debounce } from 'lodash';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
// #endregion

// #region Declarations
const stockAdjustmentData = ref<StockAdjustment | null>(null);

type StockAdjustmentInItemFormItem = {
  id: StockAdjustmentInItemNestedUpdateRequest['id'];
  qty: StockAdjustmentInItemNestedUpdateRequest['qty'];
  product_unit_id: StockAdjustmentInItemNestedUpdateRequest['product_unit_id'];
  product_unit_product_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_unit_name?: string | null;
  product_unit_base_unit_name?: string | null;
  product_unit_conversion_value: StockAdjustmentInItemNestedUpdateRequest['product_unit_conversion_value'];
  product_unit_cogs: StockAdjustmentInItemNestedUpdateRequest['product_unit_cogs'];
  product_unit_total_cogs?: number | null;
  remarks: StockAdjustmentInItemNestedUpdateRequest['remarks'];
  is_use_serial_number?: boolean;
  serials: { id: string | null; serial: string }[];
  delete_serial_ids: string[];
};

type StockAdjustmentOutItemFormItem = {
  id: StockAdjustmentOutItemNestedUpdateRequest['id'];
  qty: StockAdjustmentOutItemNestedUpdateRequest['qty'];
  product_unit_id: StockAdjustmentOutItemNestedUpdateRequest['product_unit_id'];
  product_unit_product_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_unit_name?: string | null;
  product_unit_base_unit_name?: string | null;
  product_unit_conversion_value: StockAdjustmentOutItemNestedUpdateRequest['product_unit_conversion_value'];
  remarks: StockAdjustmentOutItemNestedUpdateRequest['remarks'];
  is_use_serial_number?: boolean;
  serials: { id: string | null; serial: string }[];
  delete_serial_ids: string[];
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
  is_use_serial_number: boolean;
};

const inItemsRemarksExpanded = ref<boolean[]>([]);
const outItemsRemarksExpanded = ref<boolean[]>([]);

const { t } = useI18n();
const router = useRouter();
const route = useRoute();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits(['mode-state', 'loading-state', 'update-profile', 'show-alertplaceholder']);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const stockAdjustmentService = new StockAdjustmentService();
const stockAdjustmentForm = stockAdjustmentService.useStockAdjustmentEditForm(route.params.ulid as string);
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

const showProductUnitModal = ref<boolean>(false);
const productSearchText = ref<string>('');
const isSearchingProductUnit = ref<boolean>(false);
const productUnitOptions = ref<Array<ProductUnitOption>>([]);
const editingInProductIndex = ref<number | null>(null);
const inItemQtyToFocus = ref<number | null>(null);
const editingOutProductIndex = ref<number | null>(null);
const outItemQtyToFocus = ref<number | null>(null);
const productUnitSelectionTarget = ref<'in' | 'out'>('in');

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
    title: 'views.stock_adjustment.field_groups.in_items',
    state: CardState.Collapsed,
  },
  {
    title: 'views.stock_adjustment.field_groups.out_items',
    state: CardState.Collapsed,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const inItemsForm = computed<StockAdjustmentInItemFormItem[]>(
  () => stockAdjustmentForm.in_items as StockAdjustmentInItemFormItem[],
);
const outItemsForm = computed<StockAdjustmentOutItemFormItem[]>(
  () => stockAdjustmentForm.out_items as StockAdjustmentOutItemFormItem[],
);
// #endregion

const getProductMainImageUrl = (product: any): string | null => {
  const images: any[] = product?.product_images ?? [];
  if (images.length === 0) return null;

  const mainImage = images.find((img: any) => img?.is_main);
  if (mainImage?.url) return mainImage.url;

  return images[0]?.url ?? null;
};

// #region Vue Core
const handleExpandCard = (index: number) => {
  if (cards.value[index].state === CardState.Collapsed) {
    cards.value[index].state = CardState.Expanded;
  } else if (cards.value[index].state === CardState.Expanded) {
    cards.value[index].state = CardState.Collapsed;
  }
};

watch(
  () => stockAdjustmentForm.in_items as StockAdjustmentInItemFormItem[],
  (items: StockAdjustmentInItemFormItem[]) => {
    if (!items) return;
    items.forEach((item: StockAdjustmentInItemFormItem) => {
      const qty = Number(item.qty ?? 0);
      const cogs = Number(item.product_unit_cogs ?? 0);
      item.product_unit_total_cogs = qty * cogs;
    });
  },
  { deep: true },
);

watch(showProductUnitModal, (open) => {
  if (!open) return;
  nextTick(() => {
    const el = document.getElementById('product-unit-search-input') as HTMLInputElement | null;
    el?.focus();
  });
});

watch(
  stockAdjustmentForm,
  debounce((newValue): void => {
    cacheServices.setLastEntity('STOCK_ADJUSTMENT_EDIT', newValue.data());
  }, 500),
  { deep: true },
);

onMounted(async () => {
  emits('mode-state', ViewMode.FORM_EDIT);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  emits('loading-state', true);

  try {
    await loadData();

    await Promise.all([loadCategoryDDL(), loadInWarehouseDDL(), loadOutWarehouseDDL()]);
  } finally {
    emits('loading-state', false);
  }
});

const loadData = async () => {
  const ulid = route.params.ulid as string | undefined;
  if (!ulid) return;

  const result = await stockAdjustmentService.read(ulid);

  if (result.success && result.data) {
    const data = result.data as StockAdjustment;

    stockAdjustmentData.value = data;

    const inItems: StockAdjustmentInItemFormItem[] = (data.in_items || []).map((item: any) => {
      const productUnit = item.product_unit;
      const unit = productUnit?.unit;
      const product = (productUnit as any)?.product;

      return {
        id: item.id ?? null,
        qty: item.qty,
        product_unit_id: productUnit?.id ?? '',
        product_unit_product_code: productUnit?.code ?? '',
        product_unit_product_name: product?.name ?? '',
        product_unit_unit_name: unit?.name ?? '',
        product_unit_base_unit_name: '',
        product_unit_conversion_value: item.product_unit_conversion_value,
        product_unit_cogs: item.product_unit_cogs,
        product_unit_total_cogs: item.product_unit_total_cogs,
        remarks: item.remarks ?? '',
        is_use_serial_number: product?.is_use_serial_number ?? false,
        serials: item.serials ? item.serials.map((s: any) => ({ id: s.id ?? null, serial: s.serial })) : [],
        delete_serial_ids: [],
      };
    });

    const outItems: StockAdjustmentOutItemFormItem[] = (data.out_items || []).map((item: any) => {
      const productUnit = item.product_unit;
      const unit = productUnit?.unit;
      const product = (productUnit as any)?.product;

      return {
        id: item.id ?? null,
        qty: item.qty,
        product_unit_id: productUnit?.id ?? '',
        product_unit_product_code: productUnit?.code ?? '',
        product_unit_product_name: product?.name ?? '',
        product_unit_unit_name: unit?.name ?? '',
        product_unit_base_unit_name: '',
        product_unit_conversion_value: item.product_unit_conversion_value,
        remarks: item.remarks ?? '',
        is_use_serial_number: product?.is_use_serial_number ?? false,
        serials: item.serials ? item.serials.map((s: any) => ({ id: s.id ?? null, serial: s.serial })) : [],
        delete_serial_ids: [],
      };
    });

    stockAdjustmentForm.setData({
      company_id: data.company.id,
      branch_id: data.branch.id,
      code: data.code,
      date: formatDate(data.date, 'YYYY-MM-DD HH:mm:ss'),
      category_id: data.category?.id ?? '',
      in_warehouse_id: data.in_warehouse?.id ?? '',
      out_warehouse_id: data.out_warehouse?.id ?? '',
      remarks: data.remarks ?? '',
      is_posted: data.is_posted,
      delete_in_item_ids: [],
      in_items: inItems as any,
      delete_out_item_ids: [],
      out_items: outItems as any,
    } as any);

    inItemsRemarksExpanded.value = inItems.map(() => false);
    outItemsRemarksExpanded.value = outItems.map(() => false);

    if (inItems.length > 0) {
      cards.value = cards.value.map((card) =>
        card.title === 'views.stock_adjustment.field_groups.in_items'
          ? { ...card, state: CardState.Expanded }
          : card,
      );
    }

    if (outItems.length > 0) {
      cards.value = cards.value.map((card) =>
        card.title === 'views.stock_adjustment.field_groups.out_items'
          ? { ...card, state: CardState.Expanded }
          : card,
      );
    }
  }
};
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

const loadCategoryDDL = async (search = '') => {
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

const clearCategory = () => {
  stockAdjustmentForm.setData({ category_id: '' });
  stockAdjustmentForm.forgetError('category_id');
  stockAdjustmentForm.validate('category_id');
};

const loadInWarehouseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await warehouseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    include_id: stockAdjustmentData.value?.in_warehouse?.id as string | undefined,
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
    include_id: stockAdjustmentData.value?.out_warehouse?.id as string | undefined,
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
  stockAdjustmentForm.forgetError('out_warehouse_id');
  stockAdjustmentForm.validate('out_warehouse_id');
};
// #endregion

// #region Methods - In Items
const selectProductUnitForIn = (option: ProductUnitOption) => {
  const baseData: Partial<StockAdjustmentInItemFormItem> = {
    product_unit_id: option.product_unit_id,
    product_unit_product_code: option.product_unit_code,
    product_unit_product_name: option.product_name,
    product_unit_unit_name: option.unit_name,
    product_unit_base_unit_name: option.conversion_value != 1 ? option.base_unit_name : '',
    product_unit_conversion_value: option.conversion_value,
    product_unit_cogs: option.cogs,
    is_use_serial_number: option.is_use_serial_number,
    serials: [],
  };

  let targetIndex: number;

  if (editingInProductIndex.value === null) {
    const item: StockAdjustmentInItemFormItem = {
      id: null,
      qty: 0,
      remarks: '',
      delete_serial_ids: [],
      ...baseData,
    } as StockAdjustmentInItemFormItem;

    stockAdjustmentForm.in_items.push(item);
    inItemsRemarksExpanded.value.push(false);
    targetIndex = stockAdjustmentForm.in_items.length - 1;
  } else {
    const index = editingInProductIndex.value;
    const current = stockAdjustmentForm.in_items[index] as StockAdjustmentInItemFormItem;
    stockAdjustmentForm.in_items[index] = {
      ...current,
      ...baseData,
    };
    targetIndex = index;
  }

  showProductUnitModal.value = false;
  editingInProductIndex.value = null;
  inItemQtyToFocus.value = targetIndex;

  Object.keys(stockAdjustmentForm.errors).forEach((key) => {
    if (key.startsWith('in_items.')) {
      stockAdjustmentForm.forgetError(key as any);
    }
  });
};

const selectProductUnitForOut = (option: ProductUnitOption) => {
  const baseData: Partial<StockAdjustmentOutItemFormItem> = {
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

  if (editingOutProductIndex.value === null) {
    const item: StockAdjustmentOutItemFormItem = {
      id: null,
      qty: 0,
      remarks: '',
      delete_serial_ids: [],
      ...baseData,
    } as StockAdjustmentOutItemFormItem;

    stockAdjustmentForm.out_items.push(item);
    outItemsRemarksExpanded.value.push(false);
    targetIndex = stockAdjustmentForm.out_items.length - 1;
  } else {
    const index = editingOutProductIndex.value;
    const current = stockAdjustmentForm.out_items[index] as StockAdjustmentOutItemFormItem;
    stockAdjustmentForm.out_items[index] = {
      ...current,
      ...baseData,
    };
    targetIndex = index;
  }

  showProductUnitModal.value = false;
  editingOutProductIndex.value = null;
  outItemQtyToFocus.value = targetIndex;

  Object.keys(stockAdjustmentForm.errors).forEach((key) => {
    if (key.startsWith('out_items.')) {
      stockAdjustmentForm.forgetError(key as any);
    }
  });
};

const selectProductUnit = (option: ProductUnitOption) => {
  if (productUnitSelectionTarget.value === 'out') {
    return selectProductUnitForOut(option);
  }

  return selectProductUnitForIn(option);
};

const handleProductUnitModalAfterLeave = () => {
  const inIndex = inItemQtyToFocus.value;
  const outIndex = outItemQtyToFocus.value;

  inItemQtyToFocus.value = null;
  outItemQtyToFocus.value = null;

  if (inIndex === null && outIndex === null) return;

  nextTick(() => {
    let el: HTMLInputElement | null = null;

    if (inIndex !== null) {
      el = document.getElementById(`in-product-qty-${inIndex}`) as HTMLInputElement | null;
    } else if (outIndex !== null) {
      el = document.getElementById(`out-product-qty-${outIndex}`) as HTMLInputElement | null;
    }

    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el?.focus();
    el?.select();
  });
};

const searchProductUnits = async () => {
  if (!selectedUserLocation.value) return;

  isSearchingProductUnit.value = true;

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
    refresh: false,
    limit: 20,
  });

  isSearchingProductUnit.value = false;

  if (result.success && result.data) {
    const products = result.data.data as any[];
    productUnitOptions.value = products.flatMap((p: any) => {
      const units: any[] = p.product_units || [];

      let baseUnit = units.find((u: any) => u.is_base);
      if (!baseUnit) baseUnit = units.find((u: any) => Number(u.conversion_value) === 1);
      if (!baseUnit) baseUnit = units.find((u: any) => u.is_primary_unit);
      if (!baseUnit && units.length > 0) baseUnit = units[0];
      const baseUnitName = baseUnit && baseUnit.unit ? baseUnit.unit.name : '';

      return units.map((u: any) => ({
        product_id: p.id,
        product_code: p.code,
        product_name: p.name,
        product_image_url: getProductMainImageUrl(p),
        product_unit_id: u.id,
        product_unit_code: u.code,
        unit_name: u.unit ? u.unit.name : '',
        base_unit_name: baseUnitName,
        conversion_value: Number(u.conversion_value),
        cogs: Number(u.price),
        is_use_serial_number: p.is_use_serial_number,
      }));
    });
  } else {
    productUnitOptions.value = [];
  }
};

const addInProduct = () => {
  productSearchText.value = '';
  productUnitOptions.value = [];
  isSearchingProductUnit.value = false;
  productUnitSelectionTarget.value = 'in';
  editingInProductIndex.value = null;
  showProductUnitModal.value = true;
};

const changeInProductProductUnit = (index: number) => {
  productSearchText.value = '';
  productUnitOptions.value = [];
  isSearchingProductUnit.value = false;
  productUnitSelectionTarget.value = 'in';
  editingInProductIndex.value = index;
  showProductUnitModal.value = true;
};

const toggleInProductRemarks = (index: number) => {
  const current = inItemsRemarksExpanded.value[index] ?? false;
  inItemsRemarksExpanded.value[index] = !current;
};

const addInProductSerial = (index: number) => {
  const items = stockAdjustmentForm.in_items as StockAdjustmentInItemFormItem[];
  const item = items[index];
  if (!item) return;
  item.serials.push({ id: null, serial: '' });
  stockAdjustmentForm.validate(`in_items.${index}.serials` as any);
};

const removeInProductSerial = (index: number, serialIndex: number) => {
  const items = stockAdjustmentForm.in_items as StockAdjustmentInItemFormItem[];
  const item = items[index];
  if (!item) return;
  const serial = item.serials[serialIndex];

  if (serial.id) {
    item.delete_serial_ids.push(serial.id);
  }

  item.serials.splice(serialIndex, 1);
  stockAdjustmentForm.validate(`in_items.${index}.serials` as any);
};

const removeInProduct = (index: number) => {
  const items = stockAdjustmentForm.in_items as StockAdjustmentInItemFormItem[];
  const item = items[index];

  if (item && item.id) {
    stockAdjustmentForm.delete_in_item_ids.push(item.id);
  }

  items.splice(index, 1);
  inItemsRemarksExpanded.value.splice(index, 1);

  Object.keys(stockAdjustmentForm.errors).forEach((key) => {
    if (key.startsWith('in_items.')) {
      stockAdjustmentForm.forgetError(key as any);
    }
  });
};
// #endregion

// #region Methods - Out Items
const addOutProduct = () => {
  productSearchText.value = '';
  productUnitOptions.value = [];
  isSearchingProductUnit.value = false;
  productUnitSelectionTarget.value = 'out';
  editingOutProductIndex.value = null;
  showProductUnitModal.value = true;
};

const changeOutProductProductUnit = (index: number) => {
  productSearchText.value = '';
  productUnitOptions.value = [];
  isSearchingProductUnit.value = false;
  productUnitSelectionTarget.value = 'out';
  editingOutProductIndex.value = index;
  showProductUnitModal.value = true;
};

const toggleOutProductRemarks = (index: number) => {
  const current = outItemsRemarksExpanded.value[index] ?? false;
  outItemsRemarksExpanded.value = [
    ...outItemsRemarksExpanded.value.slice(0, index),
    !current,
    ...outItemsRemarksExpanded.value.slice(index + 1),
  ];
};

const addOutProductSerial = (index: number) => {
  const items = stockAdjustmentForm.out_items as StockAdjustmentOutItemFormItem[];
  const item = items[index];
  if (!item) return;
  item.serials.push({ id: null, serial: '' });
  stockAdjustmentForm.validate(`out_items.${index}.serials` as any);
};

const removeOutProductSerial = (index: number, serialIndex: number) => {
  const items = stockAdjustmentForm.out_items as StockAdjustmentOutItemFormItem[];
  const item = items[index];
  if (!item) return;
  const serial = item.serials[serialIndex];

  if (serial.id) {
    item.delete_serial_ids.push(serial.id);
  }

  item.serials.splice(serialIndex, 1);
  stockAdjustmentForm.validate(`out_items.${index}.serials` as any);
};

const removeOutProduct = (index: number) => {
  const items = stockAdjustmentForm.out_items as StockAdjustmentOutItemFormItem[];
  const item = items[index];

  if (item && item.id) {
    stockAdjustmentForm.delete_out_item_ids.push(item.id);
  }

  items.splice(index, 1);
  outItemsRemarksExpanded.value.splice(index, 1);

  Object.keys(stockAdjustmentForm.errors).forEach((key) => {
    if (key.startsWith('out_items.')) {
      stockAdjustmentForm.forgetError(key as any);
    }
  });
};
// #endregion

// #region Actions
const scrollToError = (id: string): void => {
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const resetForm = async () => {
  stockAdjustmentForm.reset();
  stockAdjustmentForm.setErrors({});
  inItemsRemarksExpanded.value = [];
  outItemsRemarksExpanded.value = [];
  await loadData();
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
  if (stockAdjustmentForm.hasErrors) {
    const firstErrorKey = Object.keys(stockAdjustmentForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  const originalInProducts = stockAdjustmentForm.in_items as StockAdjustmentInItemFormItem[];
  const cleanedInProducts: StockAdjustmentInItemNestedUpdateRequest[] = originalInProducts.map(
    (item: StockAdjustmentInItemFormItem) => ({
      id: item.id,
      qty: item.qty,
      product_unit_id: item.product_unit_id,
      product_unit_conversion_value: item.product_unit_conversion_value,
      product_unit_cogs: item.product_unit_cogs,
      remarks: item.remarks,
      delete_serial_ids: item.delete_serial_ids,
      serials: item.serials.map((serialItem) => ({
        id: serialItem.id,
        serial: serialItem.serial,
      })),
    }),
  );

  const originalOutProducts = stockAdjustmentForm.out_items as StockAdjustmentOutItemFormItem[];
  const cleanedOutProducts: StockAdjustmentOutItemNestedUpdateRequest[] = originalOutProducts.map(
    (item: StockAdjustmentOutItemFormItem) => ({
      id: item.id,
      qty: item.qty,
      product_unit_id: item.product_unit_id,
      product_unit_conversion_value: item.product_unit_conversion_value,
      remarks: item.remarks,
      delete_serial_ids: item.delete_serial_ids,
      serials: item.serials.map((serialItem) => ({
        id: serialItem.id,
        serial: serialItem.serial,
      })),
    }),
  );

  const backupInProducts = [...originalInProducts];
  const backupOutProducts = [...originalOutProducts];

  stockAdjustmentForm.in_items = cleanedInProducts as any;
  stockAdjustmentForm.out_items = cleanedOutProducts as any;

  emits('loading-state', true);

  try {
    await stockAdjustmentForm.submit();
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    router.push({ name: 'side-menu-stock-adjustment-list' });
  } catch (error) {
    stockAdjustmentForm.in_items = backupInProducts as any;
    stockAdjustmentForm.out_items = backupOutProducts as any;
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
      <template #card-items-0>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.company.code }}
                <br />
                {{ selectedUserLocation.company.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="stockAdjustmentForm.company_id" />
            </div>

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

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
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
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel :class="{ 'text-danger': stockAdjustmentForm.invalid('date') }">
                {{ t('views.stock_adjustment.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto v-model="stockAdjustmentForm.date" :class="{
                'border-danger': stockAdjustmentForm.invalid('date'),
              }" :placeholder="t('views.stock_adjustment.fields.date')"
                @change="stockAdjustmentForm.validate('date')" />
              <FormErrorMessages :messages="stockAdjustmentForm.errors.date" />
            </div>
            <div class="col-span-12 lg:col-span-4">
              <FormLabel :class="{
                'text-danger': stockAdjustmentForm.invalid('category_id'),
              }">
                {{ t('views.stock_adjustment.fields.category_id') }}
              </FormLabel>
              <FormSelectSearch v-model="stockAdjustmentForm.category_id" v-model:search="categorySearch"
                :options="categoryOptions" :placeholder="t('components.dropdown.placeholder')" :class="{
                  'border-danger': stockAdjustmentForm.invalid('category_id'),
                }" @change="stockAdjustmentForm.validate('category_id')" @search="loadCategoryDDL"
                @clear="clearCategory" />
              <FormErrorMessages :messages="stockAdjustmentForm.errors.category_id" />
            </div>
            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{
                'text-danger': stockAdjustmentForm.invalid('in_warehouse_id'),
              }">
                {{ t('views.stock_adjustment.fields.in_warehouse_id') }}
              </FormLabel>
              <FormSelectSearch v-model="stockAdjustmentForm.in_warehouse_id" v-model:search="inWarehouseSearch"
                :options="inWarehouseOptions" :placeholder="t('components.dropdown.placeholder')" :class="{
                  'border-danger': stockAdjustmentForm.invalid('in_warehouse_id'),
                }" @change="stockAdjustmentForm.validate('in_warehouse_id')" @search="loadInWarehouseDDL"
                @clear="clearInWarehouse" />
              <FormErrorMessages :messages="stockAdjustmentForm.errors.in_warehouse_id" />
            </div>
            <div class="col-span-12 lg:col-span-6">
              <FormLabel :class="{
                'text-danger': stockAdjustmentForm.invalid('out_warehouse_id'),
              }">
                {{ t('views.stock_adjustment.fields.out_warehouse_id') }}
              </FormLabel>
              <FormSelectSearch v-model="stockAdjustmentForm.out_warehouse_id" v-model:search="outWarehouseSearch"
                :options="outWarehouseOptions" :placeholder="t('components.dropdown.placeholder')" :class="{
                  'border-danger': stockAdjustmentForm.invalid('out_warehouse_id'),
                }" @change="stockAdjustmentForm.validate('out_warehouse_id')" @search="loadOutWarehouseDDL"
                @clear="clearOutWarehouse" />
              <FormErrorMessages :messages="stockAdjustmentForm.errors.out_warehouse_id" />
            </div>
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
          <div v-if="stockAdjustmentForm.in_items.length === 0" class="text-slate-500 text-sm">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <!-- in_product list -->
          <div v-else class="space-y-5">
            <div v-for="(item, index) in inItemsForm" :key="index"
              class="border border-slate-200/60 dark:border-darkmode-400 rounded-md p-4">
              <!-- in_product actions -->
              <div class="flex items-center justify-between mb-3">
                <div class="font-medium text-sm">
                  {{ t('views.stock_adjustment_in_item.page_title') }} #{{ index + 1 }}
                </div>
                <div class="flex items-center gap-2">
                  <Button type="button" class="text-xs text-slate-500 hover:text-primary"
                    @click="toggleInProductRemarks(index)">
                    {{ inItemsRemarksExpanded[index] ? '▲' : '▼' }}
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
                    'text-danger': stockAdjustmentForm.invalid(`in_items.${index}.qty` as any),
                  }">
                    {{ t('views.stock_adjustment_in_item.fields.qty') }}
                  </FormLabel>
                  <FormInputCurrency :id="`in-product-qty-${index}`" v-model="item.qty"
                    @change="
                      stockAdjustmentForm.validate(`in_items.${index}.qty` as any);
                      stockAdjustmentForm.validate(`in_items.${index}.serials` as any);
                    " :class="[
                      'text-right',
                      {
                        'border-danger': stockAdjustmentForm.invalid(`in_items.${index}.qty` as any),
                      },
                    ]" />
                  <FormErrorMessages :messages="(stockAdjustmentForm.errors as any)[`in_items.${index}.qty`]" />
                  <div class="text-sm text-slate-500 text-right font-bold mt-1">
                    {{ item.product_unit_unit_name }}
                  </div>
                </div>

                <!-- product description (name + code) -->
                <div class="col-span-12 lg:col-span-5">
                  <FormLabel :class="{
                    'text-danger': stockAdjustmentForm.invalid(`in_items.${index}.product_unit_id` as any),
                  }">
                    {{ t('views.stock_adjustment_in_item.table.cols.product') }}
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
                    :messages="(stockAdjustmentForm.errors as any)[`in_items.${index}.product_unit_id`]" />
                  <div class="text-sm text-slate-500 font-bold mt-1">
                    {{ item.product_unit_product_code }}
                  </div>
                </div>

                <!-- product unit conversion value -->
                <div class="col-span-12 lg:col-span-1">
                  <FormLabel :class="{
                    'text-danger': stockAdjustmentForm.invalid(
                      `in_items.${index}.product_unit_conversion_value` as any,
                    ),
                  }">
                    {{ t('views.stock_adjustment_in_item.fields.product_unit_conversion_value') }}
                  </FormLabel>
                  <FormInputCurrency v-model="item.product_unit_conversion_value" tabindex="-1"
                    @change="
                      stockAdjustmentForm.validate(`in_items.${index}.product_unit_conversion_value` as any);
                      stockAdjustmentForm.validate(`in_items.${index}.serials` as any);
                    "
                    :class="[
                      'text-right',
                      {
                        'border-danger': stockAdjustmentForm.invalid(
                          `in_items.${index}.product_unit_conversion_value` as any,
                        ),
                      },
                    ]" />
                  <FormErrorMessages :messages="(stockAdjustmentForm.errors as any)[`in_items.${index}.product_unit_conversion_value`]
                    " />
                  <div class="text-sm text-slate-500 text-right font-bold mt-1">
                    {{ item.product_unit_base_unit_name }}
                  </div>
                </div>

                <!-- product_unit_cogs -->
                <div class="col-span-12 lg:col-span-2">
                  <FormLabel :class="{
                    'text-danger': stockAdjustmentForm.invalid(`in_items.${index}.product_unit_cogs` as any),
                  }">
                    {{ t('views.stock_adjustment_in_item.fields.product_unit_cogs') }}
                  </FormLabel>
                  <FormInputCurrency v-model="item.product_unit_cogs"
                    @change="stockAdjustmentForm.validate(`in_items.${index}.product_unit_cogs` as any)" :class="[
                      'text-right',
                      {
                        'border-danger': stockAdjustmentForm.invalid(`in_items.${index}.product_unit_cogs` as any),
                      },
                    ]" />
                  <FormErrorMessages
                    :messages="(stockAdjustmentForm.errors as any)[`in_items.${index}.product_unit_cogs`]" />
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
                    {{ t('views.stock_adjustment_in_item.fields.product_unit_total_cogs') }}
                  </FormLabel>
                  <FormInputCurrency :model-value="item.product_unit_total_cogs ?? 0" readonly tabindex="-1"
                    class="text-right" />
                </div>

                <!-- serials -->
                <div v-if="item.is_use_serial_number" class="col-span-12">
                  <div class="flex items-center justify-between mb-2">
                    <FormLabel :class="{
                      'text-danger': stockAdjustmentForm.invalid(`in_items.${index}.serials` as any),
                    }">
                      {{ t('views.product.fields.serial_number') }}
                    </FormLabel>
                    <Button type="button" size="sm" variant="outline-primary" @click="addInProductSerial(index)">
                      <Lucide icon="Plus" class="w-3 h-3 mr-1" />
                      {{ t('components.buttons.create') }}
                    </Button>
                  </div>
                  <div v-if="item.serials.length === 0" class="text-slate-500 text-xs italic">
                    {{ t('components.data-list.data_not_found') }}
                  </div>
                  <div v-else class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    <div v-for="(serial, sIdx) in item.serials" :key="sIdx" class="flex gap-2">
                      <FormInput v-model="serial.serial" :placeholder="t('views.product.fields.serial_number')" :class="{
                        'border-danger': stockAdjustmentForm.invalid(`in_items.${index}.serials.${sIdx}.serial` as any),
                      }" @change="
                        stockAdjustmentForm.validate(`in_items.${index}.serials.${sIdx}.serial` as any);
                        stockAdjustmentForm.validate(`in_items.${index}.serials` as any);
                      " />
                      <Button type="button" variant="outline-secondary" @click="removeInProductSerial(index, sIdx)">
                        <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                      </Button>
                    </div>
                  </div>
                  <FormErrorMessages :messages="(stockAdjustmentForm.errors as any)[`in_items.${index}.serials`]" />
                  <FormErrorMessages
                    v-for="(_, sIdx) in item.serials"
                    :key="`in-products-serial-error-${index}-${sIdx}`"
                    :messages="(stockAdjustmentForm.errors as any)[`in_items.${index}.serials.${sIdx}.serial`]"
                  />
                </div>

                <div v-if="inItemsRemarksExpanded[index]" class="col-span-12 space-y-3">
                  <div>
                    <FormLabel :class="{
                      'text-danger': stockAdjustmentForm.invalid(`in_items.${index}.remarks` as any),
                    }">
                      {{ t('views.stock_adjustment_in_item.fields.remarks') }}
                    </FormLabel>
                    <FormTextarea rows="2" v-model="stockAdjustmentForm.in_items[index].remarks" :class="{
                      'border-danger': stockAdjustmentForm.invalid(`in_items.${index}.remarks` as any),
                    }" @change="stockAdjustmentForm.validate(`in_items.${index}.remarks` as any)" />
                    <FormErrorMessages
                      :messages="(stockAdjustmentForm.errors as any)[`in_items.${index}.remarks`]" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- in_product actions -->
          <div class="flex items-center justify-between mt-4">
            <FormLabel></FormLabel>
            <Button type="button" variant="primary" class="shadow-md" @click="addInProduct">
              {{ t('views.stock_adjustment_in_item.actions.create') }}
            </Button>
          </div>
        </div>
      </template>

      <!-- out_product -->
      <template #card-items-3>
        <div class="p-5">
          <div v-if="stockAdjustmentForm.out_items.length === 0" class="text-slate-500 text-sm">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <!-- out_product list -->
          <div v-else class="space-y-5">
            <div v-for="(item, index) in outItemsForm" :key="index"
              class="border border-slate-200/60 dark:border-darkmode-400 rounded-md p-4">
              <div class="flex items-center justify-between mb-3">
                <div class="font-medium text-sm">
                  {{ t('views.stock_adjustment_out_item.page_title') }} #{{ index + 1 }}
                </div>
                <div class="flex items-center gap-2">
                  <Button type="button" class="text-xs text-slate-500 hover:text-primary"
                    @click="toggleOutProductRemarks(index)">
                    {{ outItemsRemarksExpanded[index] ? '▲' : '▼' }}
                  </Button>
                  <Button type="button" variant="outline-secondary" @click="removeOutProduct(index)">
                    <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                  </Button>
                </div>
              </div>

              <!-- out_product fields -->
              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-2">
                  <FormLabel :class="{
                    'text-danger': stockAdjustmentForm.invalid(`out_items.${index}.qty` as any),
                  }">
                    {{ t('views.stock_adjustment_out_item.fields.qty') }}
                  </FormLabel>
                  <FormInputCurrency :id="`out-product-qty-${index}`" v-model="item.qty"
                    @change="
                      stockAdjustmentForm.validate(`out_items.${index}.qty` as any);
                      stockAdjustmentForm.validate(`out_items.${index}.serials` as any);
                    " :class="[
                      'text-right',
                      {
                        'border-danger': stockAdjustmentForm.invalid(`out_items.${index}.qty` as any),
                      },
                    ]" />
                  <FormErrorMessages :messages="(stockAdjustmentForm.errors as any)[`out_items.${index}.qty`]" />
                  <div class="text-sm text-slate-500 text-right font-bold mt-1">
                    {{ item.product_unit_unit_name }}
                  </div>
                </div>

                <div class="col-span-12 lg:col-span-5">
                  <FormLabel :class="{
                    'text-danger': stockAdjustmentForm.invalid(`out_items.${index}.product_unit_id` as any),
                  }">
                    {{ t('views.stock_adjustment_out_item.table.cols.product') }}
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
                    :messages="(stockAdjustmentForm.errors as any)[`out_items.${index}.product_unit_id`]" />
                  <div class="text-sm text-slate-500 font-bold mt-1">
                    {{ item.product_unit_product_code }}
                  </div>
                </div>

                <div class="col-span-12 lg:col-span-3">
                  <FormLabel :class="{
                    'text-danger': stockAdjustmentForm.invalid(
                      `out_items.${index}.product_unit_conversion_value` as any,
                    ),
                  }">
                    {{ t('views.stock_adjustment_out_item.fields.product_unit_conversion_value') }}
                  </FormLabel>
                  <FormInputCurrency v-model="item.product_unit_conversion_value" tabindex="-1"
                    @change="
                      stockAdjustmentForm.validate(`out_items.${index}.product_unit_conversion_value` as any);
                      stockAdjustmentForm.validate(`out_items.${index}.serials` as any);
                    "
                    :class="[
                      'text-right',
                      {
                        'border-danger': stockAdjustmentForm.invalid(
                          `out_items.${index}.product_unit_conversion_value` as any,
                        ),
                      },
                    ]" />
                  <FormErrorMessages :messages="(stockAdjustmentForm.errors as any)[`out_items.${index}.product_unit_conversion_value`]
                    " />
                  <div class="text-sm text-slate-500 text-right font-bold mt-1">
                    {{ item.product_unit_base_unit_name }}
                  </div>
                </div>

                <!-- serials -->
                <div v-if="item.is_use_serial_number" class="col-span-12">
                  <div class="flex items-center justify-between mb-2">
                    <FormLabel :class="{
                      'text-danger': stockAdjustmentForm.invalid(`out_items.${index}.serials` as any),
                    }">
                      {{ t('views.product.fields.serial_number') }}
                    </FormLabel>
                    <Button type="button" size="sm" variant="outline-primary" @click="addOutProductSerial(index)">
                      <Lucide icon="Plus" class="w-3 h-3 mr-1" />
                      {{ t('components.buttons.create') }}
                    </Button>
                  </div>
                  <div v-if="item.serials.length === 0" class="text-slate-500 text-xs italic">
                    {{ t('components.data-list.data_not_found') }}
                  </div>
                  <div v-else class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-3">
                    <div v-for="(serial, sIdx) in item.serials" :key="sIdx" class="flex gap-2">
                      <FormInput v-model="serial.serial" :placeholder="t('views.product.fields.serial_number')" :class="{
                        'border-danger': stockAdjustmentForm.invalid(`out_items.${index}.serials.${sIdx}.serial` as any),
                      }" @change="
                        stockAdjustmentForm.validate(`out_items.${index}.serials.${sIdx}.serial` as any);
                        stockAdjustmentForm.validate(`out_items.${index}.serials` as any);
                      " />
                      <Button type="button" variant="outline-secondary" @click="removeOutProductSerial(index, sIdx)">
                        <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                      </Button>
                    </div>
                  </div>
                  <FormErrorMessages :messages="(stockAdjustmentForm.errors as any)[`out_items.${index}.serials`]" />
                  <FormErrorMessages
                    v-for="(_, sIdx) in item.serials"
                    :key="`out-products-serial-error-${index}-${sIdx}`"
                    :messages="(stockAdjustmentForm.errors as any)[`out_items.${index}.serials.${sIdx}.serial`]"
                  />
                </div>

                <div v-if="outItemsRemarksExpanded[index]" class="col-span-12 space-y-3">
                  <div>
                    <FormLabel :class="{
                      'text-danger': stockAdjustmentForm.invalid(`out_items.${index}.remarks` as any),
                    }">
                      {{ t('views.stock_adjustment_out_item.fields.remarks') }}
                    </FormLabel>
                    <FormTextarea rows="2" v-model="stockAdjustmentForm.out_items[index].remarks" :class="{
                      'border-danger': stockAdjustmentForm.invalid(`out_items.${index}.remarks` as any),
                    }" @change="stockAdjustmentForm.validate(`out_items.${index}.remarks` as any)" />
                    <FormErrorMessages
                      :messages="(stockAdjustmentForm.errors as any)[`out_items.${index}.remarks`]" />
                  </div>
                </div>
              </div>
            </div>
          </div>

          <!-- out_product actions -->
          <div class="flex items-center justify-between mt-4">
            <FormLabel></FormLabel>
            <Button type="button" variant="primary" class="shadow-md" @click="addOutProduct">
              {{ t('views.stock_adjustment_out_item.actions.create') }}
            </Button>
          </div>
        </div>
      </template>

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

    <!-- product_unit modal -->
    <Dialog size="xl" :open="showProductUnitModal" @close="
      () => {
        showProductUnitModal = false;
      }
    " @after-leave="handleProductUnitModalAfterLeave">
      <Dialog.Panel>
        <div class="p-5">
          <!-- modal header -->
          <div class="flex items-center justify-between mb-4">
            <FormLabel>
              {{ t('views.stock_adjustment_in_item.table.title') }}
            </FormLabel>
            <button type="button" class="text-slate-500 hover:text-danger" @click="showProductUnitModal = false">
              <Lucide icon="X" class="w-4 h-4" />
            </button>
          </div>

          <!-- search box -->
          <div class="flex items-center gap-2 mb-4">
            <FormInput id="product-unit-search-input" v-model="productSearchText" type="text"
              :placeholder="t('components.search-box.placeholder.search')" @keyup.enter="searchProductUnits" />
            <Button type="button" variant="primary" class="shadow-md" @click="searchProductUnits" tabindex="-1"
              :disabled="isSearchingProductUnit">
              <template v-if="isSearchingProductUnit">
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
                  <th class="px-3 py-2 text-left">
                    {{ t('views.product.table.cols.image') }}
                  </th>
                  <!-- product -->
                  <th class="px-3 py-2 text-left">
                    {{ t('views.stock_adjustment_in_item.table.cols.product') }}
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
                    {{ t('views.stock_adjustment_in_item.table.cols.product_unit_cogs') }}
                  </th>
                  <!-- actions -->
                  <th class="px-3 py-2"></th>
                </tr>
              </thead>
              <tbody>
                <!-- empty state -->
                <tr v-if="productUnitOptions.length === 0">
                  <td colspan="6" class="px-3 py-4 text-center text-slate-500">
                    {{ t('components.data-list.data_not_found') }}
                  </td>
                </tr>

                <!-- product unit rows -->
                <tr v-for="(opt, idx) in productUnitOptions" :key="idx"
                  class="border-t border-slate-200/60 dark:border-darkmode-400">
                  <td class="px-3 py-2">
                    <ProductImagePreview :image-url="opt.product_image_url"
                      wrapper-class="w-10 h-10 rounded-md overflow-hidden bg-slate-100 dark:bg-darkmode-600 flex items-center justify-center cursor-zoom-in"
                      icon-class="w-4 h-4 text-slate-400" />
                  </td>
                  <!-- product -->
                  <td class="px-3 py-2">[{{ opt.product_code }}] {{ opt.product_name }}</td>
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
                    <Button type="button" variant="primary" size="sm" @click="selectProductUnit(opt)">
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
