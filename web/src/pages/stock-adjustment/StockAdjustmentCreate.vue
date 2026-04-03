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
  type StockAdjustmentInItemNestedStoreRequest,
  type StockAdjustmentOutItemNestedStoreRequest,
} from '@/types/services/stock-adjustment/StockAdjustmentRequest';
import { debounce } from 'lodash';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
// #endregion

// #region Declarations
type StockAdjustmentInItemFormItem = {
  qty: StockAdjustmentInItemNestedStoreRequest['qty'];
  product_unit_id: StockAdjustmentInItemNestedStoreRequest['product_unit_id'];
  product_unit_product_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_unit_name?: string | null;
  product_unit_base_unit_name?: string | null;
  product_unit_conversion_value: StockAdjustmentInItemNestedStoreRequest['product_unit_conversion_value'];
  product_unit_cogs: StockAdjustmentInItemNestedStoreRequest['product_unit_cogs'];
  product_unit_total_cogs?: number | null;
  remarks: StockAdjustmentInItemNestedStoreRequest['remarks'];
  is_use_serial_number?: boolean;
  serials: { serial: string }[];
};

type StockAdjustmentOutItemFormItem = {
  qty: StockAdjustmentOutItemNestedStoreRequest['qty'];
  product_unit_id: StockAdjustmentOutItemNestedStoreRequest['product_unit_id'];
  product_unit_product_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_unit_name?: string | null;
  product_unit_base_unit_name?: string | null;
  product_unit_conversion_value: StockAdjustmentOutItemNestedStoreRequest['product_unit_conversion_value'];
  remarks: StockAdjustmentOutItemNestedStoreRequest['remarks'];
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
const inItemQtyToFocus = ref<number | null>(null);
const inItemsRemarksExpanded = ref<boolean[]>([]);
const inItemsForm = computed<StockAdjustmentInItemFormItem[]>(
  () => stockAdjustmentForm.in_items as StockAdjustmentInItemFormItem[],
);

const showOutProductUnitModal = ref<boolean>(false);
const productSearchTextOut = ref<string>('');
const isSearchingOutProductUnit = ref<boolean>(false);
const productUnitOptionsOut = ref<Array<ProductUnitOption>>([]);
const editingOutProductIndex = ref<number | null>(null);
const outItemQtyToFocus = ref<number | null>(null);
const outItemsRemarksExpanded = ref<boolean[]>([]);
const outItemsForm = computed<StockAdjustmentOutItemFormItem[]>(
  () => stockAdjustmentForm.out_items as StockAdjustmentOutItemFormItem[],
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
    title: 'views.stock_adjustment.field_groups.in_items',
    state: CardState.Collapsed,
  },
  {
    title: 'views.stock_adjustment.field_groups.out_items',
    state: CardState.Collapsed,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);
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

const clearInWarehouse = () => {
  stockAdjustmentForm.setData({ in_warehouse_id: '' });
  stockAdjustmentForm.forgetError('in_warehouse_id');
  stockAdjustmentForm.validate('in_warehouse_id');
};

const clearOutWarehouse = () => {
  stockAdjustmentForm.setData({ out_warehouse_id: '' });
  stockAdjustmentForm.forgetError('out_warehouse_id');
  stockAdjustmentForm.validate('out_warehouse_id');
};

const clearCategory = () => {
  stockAdjustmentForm.setData({ category_id: '' });
  stockAdjustmentForm.forgetError('category_id');
  stockAdjustmentForm.validate('category_id');
};

const loadFromCache = () => {
  const data = cacheServices.getLastEntity('STOCK_ADJUSTMENT_CREATE') as Record<string, unknown>;
  if (!data) return;
  stockAdjustmentForm.setData(data);
};
// #endregion

// #region Methods - In Items
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
    productUnitOptionsIn.value = [];
  }
};

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
      qty: 0,
      remarks: '',
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

  showInProductUnitModal.value = false;
  editingInProductIndex.value = null;
  inItemQtyToFocus.value = targetIndex;
};

const toggleInProductRemarks = (index: number) => {
  const current = inItemsRemarksExpanded.value[index] ?? false;
  inItemsRemarksExpanded.value[index] = !current;
};

const handleInProductUnitModalAfterLeave = () => {
  const inIndex = inItemQtyToFocus.value;

  inItemQtyToFocus.value = null;

  if (inIndex === null) return;

  nextTick(() => {
    const el = document.getElementById(`in-product-qty-${inIndex}`) as HTMLInputElement | null;
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el?.focus();
    el?.select();
  });
};

const addInProductSerial = (index: number) => {
  const items = stockAdjustmentForm.in_items as StockAdjustmentInItemFormItem[];
  const item = items[index];
  if (!item) return;
  item.serials.push({ serial: '' });
  stockAdjustmentForm.validate(`in_items.${index}.serials` as any);
};

const removeInProductSerial = (index: number, serialIndex: number) => {
  const items = stockAdjustmentForm.in_items as StockAdjustmentInItemFormItem[];
  const item = items[index];
  if (!item) return;
  item.serials.splice(serialIndex, 1);
  stockAdjustmentForm.validate(`in_items.${index}.serials` as any);
};

const removeInProduct = (index: number) => {
  stockAdjustmentForm.in_items.splice(index, 1);
  inItemsRemarksExpanded.value.splice(index, 1);
};
// #endregion

// #region Methods - Out Items
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
    productUnitOptionsOut.value = [];
  }
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
      qty: 0,
      remarks: '',
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

  showOutProductUnitModal.value = false;
  editingOutProductIndex.value = null;
  outItemQtyToFocus.value = targetIndex;
};

const toggleOutProductRemarks = (index: number) => {
  const current = outItemsRemarksExpanded.value[index] ?? false;
  outItemsRemarksExpanded.value = [
    ...outItemsRemarksExpanded.value.slice(0, index),
    !current,
    ...outItemsRemarksExpanded.value.slice(index + 1),
  ];
};

const handleOutProductUnitModalAfterLeave = () => {
  const outIndex = outItemQtyToFocus.value;

  outItemQtyToFocus.value = null;

  if (outIndex === null) return;

  nextTick(() => {
    const el = document.getElementById(`out-product-qty-${outIndex}`) as HTMLInputElement | null;
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el?.focus();
    el?.select();
  });
};

const addOutProductSerial = (index: number) => {
  const items = stockAdjustmentForm.out_items as StockAdjustmentOutItemFormItem[];
  const item = items[index];
  if (!item) return;
  item.serials.push({ serial: '' });
  stockAdjustmentForm.validate(`out_items.${index}.serials` as any);
};

const removeOutProductSerial = (index: number, serialIndex: number) => {
  const items = stockAdjustmentForm.out_items as StockAdjustmentOutItemFormItem[];
  const item = items[index];
  if (!item) return;
  item.serials.splice(serialIndex, 1);
  stockAdjustmentForm.validate(`out_items.${index}.serials` as any);
};

const removeOutProduct = (index: number) => {
  stockAdjustmentForm.out_items.splice(index, 1);
  outItemsRemarksExpanded.value.splice(index, 1);
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
  inItemsRemarksExpanded.value = [];
  outItemsRemarksExpanded.value = [];
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
  const cleanedInProducts: StockAdjustmentInItemNestedStoreRequest[] = originalInProducts.map(
    (item: StockAdjustmentInItemFormItem) => ({
      qty: item.qty,
      product_unit_id: item.product_unit_id,
      product_unit_conversion_value: item.product_unit_conversion_value,
      product_unit_cogs: item.product_unit_cogs,
      remarks: item.remarks,
      serials: item.serials.map((serialItem) => ({
        serial: serialItem.serial,
      })),
    }),
  );

  const originalOutProducts = stockAdjustmentForm.out_items as StockAdjustmentOutItemFormItem[];
  const cleanedOutProducts: StockAdjustmentOutItemNestedStoreRequest[] = originalOutProducts.map(
    (item: StockAdjustmentOutItemFormItem) => ({
      qty: item.qty,
      product_unit_id: item.product_unit_id,
      product_unit_conversion_value: item.product_unit_conversion_value,
      remarks: item.remarks,
      serials: item.serials.map((serialItem) => ({
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
    resetForm();
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
              <FormInputDateTimeAuto v-model="stockAdjustmentForm.date" :class="{
                'border-danger': stockAdjustmentForm.invalid('date'),
              }" :placeholder="t('views.stock_adjustment.fields.date')"
                @change="stockAdjustmentForm.validate('date')" />
              <FormErrorMessages :messages="stockAdjustmentForm.errors.date" />
            </div>
            <!-- category -->
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
            <!-- in_warehouse -->
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
            <!-- out_warehouse -->
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

                <!-- product_unit_conversion_value -->
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
            <FormLabel>
              <!-- {{ t("views.stock_adjustment.field_groups.in_items") }} -->
            </FormLabel>
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
              <!-- out_product actions -->
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
                <!-- qty -->
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

                <!-- product description (name + code) -->
                <div class="col-span-12 lg:col-span-8">
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

                <!-- product_unit_conversion_value -->
                <div class="col-span-12 lg:col-span-2">
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
            <FormLabel>
              <!-- {{ t("views.stock_adjustment.field_groups.out_items") }} -->
            </FormLabel>
            <Button type="button" variant="primary" class="shadow-md" @click="addOutProduct">
              {{ t('views.stock_adjustment_out_item.actions.create') }}
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
              {{ t('views.stock_adjustment_in_item.table.title') }}
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
                  <th class="px-3 py-2 text-left">
                    {{ t('views.product.table.cols.image') }}
                  </th>
                  <!-- product -->
                  <th class="px-3 py-2 text-left">
                    {{ t('views.stock_adjustment_in_item.table.cols.product') }}
                  </th>
                  <!-- remaining_stock -->
                  <th class="px-3 py-2 text-right">
                    {{ t('views.stock_adjustment_in_item.table.cols.remaining_stock') }}
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
                <tr v-if="productUnitOptionsIn.length === 0">
                  <td colspan="7" class="px-3 py-4 text-center text-slate-500">
                    {{ t('components.data-list.data_not_found') }}
                  </td>
                </tr>

                <!-- product unit rows -->
                <tr v-for="(opt, idx) in productUnitOptionsIn" :key="idx"
                  class="border-t border-slate-200/60 dark:border-darkmode-400">
                  <td class="px-3 py-2">
                    <ProductImagePreview :image-url="opt.product_image_url"
                      wrapper-class="w-10 h-10 rounded-md overflow-hidden bg-slate-100 dark:bg-darkmode-600 flex items-center justify-center cursor-zoom-in"
                      icon-class="w-4 h-4 text-slate-400" />
                  </td>
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
              {{ t('views.stock_adjustment_out_item.table.title') }}
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
                  <th class="px-3 py-2 text-left">
                    {{ t('views.product.table.cols.image') }}
                  </th>
                  <!-- product -->
                  <th class="px-3 py-2 text-left">
                    {{ t('views.stock_adjustment_out_item.table.cols.product') }}
                  </th>
                  <!-- remaining_stock -->
                  <th class="px-3 py-2 text-right">
                    {{ t('views.stock_adjustment_out_item.table.cols.remaining_stock') }}
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
                    {{ t('views.stock_adjustment_out_item.table.cols.product_unit_cogs') }}
                  </th>
                  <!-- actions -->
                  <th class="px-3 py-2"></th>
                </tr>
              </thead>
              <tbody>
                <!-- empty state -->
                <tr v-if="productUnitOptionsOut.length === 0">
                  <td colspan="7" class="px-3 py-4 text-center text-slate-500">
                    {{ t('components.data-list.data_not_found') }}
                  </td>
                </tr>

                <!-- product unit rows -->
                <tr v-for="(opt, idx) in productUnitOptionsOut" :key="idx"
                  class="border-t border-slate-200/60 dark:border-darkmode-400">
                  <td class="px-3 py-2">
                    <ProductImagePreview :image-url="opt.product_image_url"
                      wrapper-class="w-10 h-10 rounded-md overflow-hidden bg-slate-100 dark:bg-darkmode-600 flex items-center justify-center cursor-zoom-in"
                      icon-class="w-4 h-4 text-slate-400" />
                  </td>
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
