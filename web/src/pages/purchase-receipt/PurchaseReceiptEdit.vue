<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { convertErrorTypeToAlertListType, formatDate } from '@/utils/helper';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { type TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import { CardState } from '@/types/enums/CardState';
import { ViewMode } from '@/types/enums/ViewMode';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import {
  FormErrorMessages,
  FormInput,
  FormInputCode,
  FormInputDateTimeAuto,
  FormLabel,
  FormSelectSearch,
  FormSwitch,
  FormTextarea,
} from '@/components/Base/Form';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import ProductUnitPickerDialog from '@/components/Product/ProductUnitPickerDialog.vue';
import ProductService from '@/services/ProductService';
import PurchaseReceiptService from '@/services/PurchaseReceiptService';
import PurchaseService from '@/services/PurchaseService';
import SupplierService from '@/services/SupplierService';
import WarehouseService from '@/services/WarehouseService';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { NotificationData } from '@/types/models/NotificationData';
import type { Product } from '@/types/models/Product';
import type { ProductUnit } from '@/types/models/ProductUnit';
import type { Purchase } from '@/types/models/Purchase';
import type { PurchaseItem } from '@/types/models/PurchaseItem';
import type { PurchaseReceipt } from '@/types/models/PurchaseReceipt';
import type { PurchaseReceiptItemNestedStoreRequest } from '@/types/services/purchase-receipt/PurchaseReceiptRequest';
// #endregion

// #region Declarations
type PurchaseOption = DropDownOption & {
  ulid: string;
  supplier_id: string | null;
  supplier_name: string | null;
};

type PurchaseReceiptSerialFormItem = {
  serial: string;
};

type PurchaseReceiptItemFormItem = PurchaseReceiptItemNestedStoreRequest & {
  product_unit_product_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_unit_name?: string | null;
  product_unit_base_unit_name?: string | null;
  product_unit_product_image_url?: string | null;
  purchase_item_label?: string | null;
  is_use_serial_number?: boolean;
};

type ProductUnitOption = {
  product_unit_id: string;
  product_unit_code: string;
  product_name: string;
  product_image_url?: string | null;
  unit_name: string;
  base_unit_name: string;
  conversion_value: number;
  is_use_serial_number: boolean;
};

const emits = defineEmits([
  'loading-state',
  'mode-state',
  'update-profile',
  'show-alertplaceholder',
  'show-notification',
]);

const { t } = useI18n();
const route = useRoute();
const router = useRouter();

const selectedUserLocationStore = useSelectedUserLocationStore();
const purchaseReceiptService = new PurchaseReceiptService();
const purchaseService = new PurchaseService();
const supplierService = new SupplierService();
const warehouseService = new WarehouseService();
const productService = new ProductService();

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const purchaseReceiptForm: any = purchaseReceiptService.usePurchaseReceiptEditForm(route.params.ulid as string);

const purchaseReceiptData = ref<PurchaseReceipt | null>(null);
const selectedPurchaseData = ref<Purchase | null>(null);
const initializing = ref<boolean>(true);
const isSearchingProductUnit = ref<boolean>(false);
const showProductUnitModal = ref<boolean>(false);
const editingItemIndex = ref<number | null>(null);
const productSearchText = ref<string>('');
const supplierSearch = ref<string>('');
const purchaseSearch = ref<string>('');
const warehouseSearch = ref<string>('');

const supplierDDL = ref<Array<DropDownOption>>([]);
const purchaseDDL = ref<Array<PurchaseOption>>([]);
const warehouseDDL = ref<Array<DropDownOption>>([]);
const productUnitOptions = ref<Array<ProductUnitOption>>([]);

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.purchase_receipt.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.purchase_receipt.field_groups.purchase_receipt_data',
    state: CardState.Expanded,
  },
  {
    title: 'views.purchase_receipt.field_groups.items',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const supplierOptions = computed(() =>
  supplierDDL.value.map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const purchaseOptions = computed(() =>
  purchaseDDL.value.map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const warehouseOptions = computed(() =>
  warehouseDDL.value.map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const productUnitDialogColumns = computed(() => [
  {
    key: 'unit_name',
    label: t('views.product.table.cols.unit'),
  },
  {
    key: 'base_unit_name',
    label: t('views.product.fields.base_unit'),
  },
  {
    key: 'conversion_value',
    label: t('views.purchase_receipt.fields.product_unit_conversion_value'),
    align: 'right' as const,
    formatter: 'number' as const,
  },
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

onMounted(async () => {
  emits('mode-state', ViewMode.FORM_EDIT);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  const loaded = await loadData();
  if (!loaded) {
    return;
  }

  await Promise.all([loadSupplierDDL(), loadPurchaseDDL(), loadWarehouseDDL()]);
  initializing.value = false;
});
// #endregion

// #region Methods - Helpers
const getItems = () => purchaseReceiptForm.items as PurchaseReceiptItemFormItem[];

const getItemFieldErrors = (field: string) => (purchaseReceiptForm.errors as any)[field];

const invalidField = (field: string) => purchaseReceiptForm.invalid(field);

const invalidItemField = (field: string) => purchaseReceiptForm.invalid(field as any);

const scrollToError = (id: string): void => {
  const el = document.getElementById(id);
  if (!el) return;
  el.scrollIntoView({ behavior: 'smooth', block: 'center' });
};

const showAlertPlaceholder = (
  alertType: 'hidden' | 'danger' | 'success' | 'warning' | 'pending' | 'dark',
  title: string,
  alertList: Record<string, Array<string>> | null,
) => {
  const payload: AlertPlaceholderProps = {
    alertType,
    title,
    alertList,
  };

  emits('show-alertplaceholder', payload);
};

const showNotification = (title: string, content: string) => {
  const payload: NotificationData = {
    title,
    content,
  };

  emits('show-notification', payload);
};

const clearItemErrors = () => {
  Object.keys(purchaseReceiptForm.errors).forEach((key) => {
    if (key.startsWith('items.')) {
      purchaseReceiptForm.forgetError(key as any);
    }
  });
};

const appendDropDownOption = (
  target: { value: Array<DropDownOption> },
  option: DropDownOption | null | undefined,
) => {
  if (!option?.code) return;

  if (!target.value.some((item) => item.code === option.code)) {
    target.value = [...target.value, option];
  }
};

const appendPurchaseOption = (purchase: Purchase | null | undefined) => {
  if (!purchase?.id || !purchase.ulid) return;

  if (!purchaseDDL.value.some((item) => item.code === purchase.id)) {
    purchaseDDL.value = [
      ...purchaseDDL.value,
      {
        code: purchase.id,
        name: `${purchase.code} - ${purchase.supplier?.name ?? '-'}`,
        ulid: purchase.ulid,
        supplier_id: purchase.supplier?.id ?? null,
        supplier_name: purchase.supplier?.name ?? null,
      },
    ];
  }
};

const buildBaseUnitName = (product: Product | null | undefined, conversionValue: number) => {
  if (!product || conversionValue === 1) {
    return '';
  }

  return product.product_units.find((unit) => Number(unit.conversion_value ?? 1) === 1)?.unit?.name ?? '';
};

const formatPurchaseItemLabel = (purchaseItem: PurchaseItem | null | undefined) => {
  if (!purchaseItem) return null;

  const productUnit = purchaseItem.product_unit;
  const productCode = productUnit?.code ?? '';
  const productName = productUnit?.product?.name ?? '-';

  return productCode ? `[${productCode}] ${productName}` : productName;
};

const buildManualItemFromProductUnit = (option: ProductUnitOption): PurchaseReceiptItemFormItem => ({
  purchase_item_id: null,
  qty: 1,
  product_unit_id: option.product_unit_id,
  product_unit_conversion_value: option.conversion_value,
  remarks: '',
  serials: [],
  product_unit_product_code: option.product_unit_code,
  product_unit_product_name: option.product_name,
  product_unit_unit_name: option.unit_name,
  product_unit_base_unit_name: option.base_unit_name,
  product_unit_product_image_url: option.product_image_url ?? null,
  purchase_item_label: null,
  is_use_serial_number: option.is_use_serial_number,
});

const buildReceiptItemFromPurchaseItem = (purchaseItem: PurchaseItem): PurchaseReceiptItemFormItem => {
  const productUnit = purchaseItem.product_unit;
  const product = productUnit?.product;
  const conversionValue = Number(purchaseItem.product_unit_conversion_value ?? productUnit?.conversion_value ?? 1);
  const outstandingBase = Number(purchaseItem.qty_outstanding_base ?? 0);
  const defaultQty = outstandingBase > 0
    ? outstandingBase / Math.max(conversionValue, 1)
    : Number(purchaseItem.qty ?? 0);

  return {
    purchase_item_id: purchaseItem.id,
    qty: defaultQty > 0 ? defaultQty : 1,
    product_unit_id: productUnit?.id ?? '',
    product_unit_conversion_value: conversionValue,
    remarks: purchaseItem.remarks ?? '',
    serials: [],
    product_unit_product_code: productUnit?.code ?? '',
    product_unit_product_name: product?.name ?? '-',
    product_unit_unit_name: productUnit?.unit?.name ?? '',
    product_unit_base_unit_name: buildBaseUnitName(product, Number(productUnit?.conversion_value ?? conversionValue)),
    product_unit_product_image_url: product?.main_product_image?.url ?? null,
    purchase_item_label: formatPurchaseItemLabel(purchaseItem),
    is_use_serial_number: Boolean(product?.is_use_serial_number),
  };
};

const buildFormItemFromReceiptItem = (receipt: PurchaseReceipt, item: any): PurchaseReceiptItemFormItem => {
  const productUnit: ProductUnit | null | undefined = item.product_unit;
  const purchaseItem: PurchaseItem | null | undefined = item.purchase_item;
  const product = productUnit?.product ?? purchaseItem?.product_unit?.product;
  const conversionValue = Number(item.product_unit_conversion_value ?? productUnit?.conversion_value ?? 1);

  return {
    purchase_item_id: purchaseItem?.id ?? null,
    qty: Number(item.qty ?? 0),
    product_unit_id: productUnit?.id ?? purchaseItem?.product_unit?.id ?? '',
    product_unit_conversion_value: conversionValue,
    remarks: item.remarks ?? '',
    serials: (item.serials ?? []).map((serial: any) => ({
      serial: serial.serial,
    })) as PurchaseReceiptSerialFormItem[],
    product_unit_product_code: productUnit?.code ?? purchaseItem?.product_unit?.code ?? '',
    product_unit_product_name: product?.name ?? '-',
    product_unit_unit_name: productUnit?.unit?.name ?? purchaseItem?.product_unit?.unit?.name ?? '',
    product_unit_base_unit_name: buildBaseUnitName(product, Number(productUnit?.conversion_value ?? conversionValue)),
    product_unit_product_image_url: product?.main_product_image?.url ?? null,
    purchase_item_label: receipt.purchase ? formatPurchaseItemLabel(purchaseItem) : null,
    is_use_serial_number: Boolean(product?.is_use_serial_number),
  };
};
// #endregion

// #region Methods - Purchase Receipt
const loadSupplierDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const includeId =
    purchaseReceiptData.value?.supplier?.id ??
    (purchaseReceiptForm.supplier_id as string | null | undefined) ??
    undefined;

  const result = await supplierService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    status: undefined,
    include_id: includeId,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    supplierDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }

  appendDropDownOption(supplierDDL, purchaseReceiptData.value?.supplier
    ? {
      code: purchaseReceiptData.value.supplier.id,
      name: purchaseReceiptData.value.supplier.name,
    }
    : null);
};

const loadPurchaseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await purchaseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: null,
    end_date: undefined,
    supplier_id: (purchaseReceiptForm.supplier_id as string | null) ?? undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    purchaseDDL.value = result.data.data.map((item) => ({
      code: item.id,
      name: `${item.code} - ${item.supplier?.name ?? '-'}`,
      ulid: item.ulid,
      supplier_id: item.supplier?.id ?? null,
      supplier_name: item.supplier?.name ?? null,
    }));
  } else {
    purchaseDDL.value = [];
  }

  appendPurchaseOption(purchaseReceiptData.value?.purchase);
  appendPurchaseOption(selectedPurchaseData.value);
};

const loadWarehouseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const includeId =
    purchaseReceiptData.value?.warehouse?.id ??
    (purchaseReceiptForm.warehouse_id as string | null | undefined) ??
    undefined;

  const result = await warehouseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    include_id: includeId,
    status: undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    warehouseDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }

  appendDropDownOption(warehouseDDL, purchaseReceiptData.value?.warehouse
    ? {
      code: purchaseReceiptData.value.warehouse.id,
      name: purchaseReceiptData.value.warehouse.name,
    }
    : null);
};

const setCode = () => {
  purchaseReceiptForm.forgetError('code');

  if (purchaseReceiptForm.code === '_AUTO_') {
    purchaseReceiptForm.setData({ code: '' });
    return;
  }

  purchaseReceiptForm.setData({ code: '_AUTO_' });
};

const loadPurchaseDetail = async (purchaseUlid: string) => {
  const result = await purchaseService.read(purchaseUlid);

  if (result.success && result.data) {
    selectedPurchaseData.value = result.data;
    appendPurchaseOption(result.data);

    if (result.data.supplier) {
      appendDropDownOption(supplierDDL, {
        code: result.data.supplier.id,
        name: result.data.supplier.name,
      });
    }

    return result.data;
  }

  showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  return null;
};

const syncItemsFromPurchase = (purchase: Purchase) => {
  purchaseReceiptForm.setData({
    supplier_id: purchase.supplier?.id ?? null,
    items: (purchase.items ?? []).map((item) => buildReceiptItemFromPurchaseItem(item)) as any,
  });

  clearItemErrors();
};

const clearPurchase = async () => {
  selectedPurchaseData.value = null;
  purchaseReceiptForm.setData({
    purchase_id: null,
    items: [] as any,
  });
  purchaseReceiptForm.forgetError('purchase_id');
  clearItemErrors();
  await loadPurchaseDDL();
};

const clearSupplier = async () => {
  purchaseReceiptForm.setData({
    supplier_id: null,
    purchase_id: null,
    items: [] as any,
  });
  selectedPurchaseData.value = null;
  purchaseReceiptForm.forgetError('supplier_id');
  purchaseReceiptForm.forgetError('purchase_id');
  clearItemErrors();
  await loadPurchaseDDL();
};

const clearWarehouse = () => {
  purchaseReceiptForm.setData({ warehouse_id: null });
  purchaseReceiptForm.forgetError('warehouse_id');
};

const handleSupplierChanged = async () => {
  purchaseReceiptForm.validate('supplier_id');

  if (purchaseReceiptForm.purchase_id) {
    return;
  }

  await loadPurchaseDDL();
};

const handlePurchaseChanged = async (purchaseId: string | number | null) => {
  purchaseReceiptForm.validate('purchase_id');

  if (!purchaseId) {
    await clearPurchase();
    return;
  }

  const option = purchaseDDL.value.find((item) => item.code === purchaseId);
  if (!option) {
    return;
  }

  const purchase = await loadPurchaseDetail(option.ulid);
  if (!purchase) {
    return;
  }

  syncItemsFromPurchase(purchase);
};

const reloadItemsFromPurchase = async () => {
  if (!purchaseReceiptForm.purchase_id) {
    return;
  }

  const option = purchaseDDL.value.find((item) => item.code === purchaseReceiptForm.purchase_id);
  if (!option) {
    return;
  }

  const purchase = await loadPurchaseDetail(option.ulid);
  if (!purchase) {
    return;
  }

  syncItemsFromPurchase(purchase);
};

const loadData = async () => {
  const result = await purchaseReceiptService.read(route.params.ulid as string);

  if (!result.success || !result.data) {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
    return false;
  }

  const data = result.data;
  purchaseReceiptData.value = data;

  if (data.is_from_direct_purchase) {
    showAlertPlaceholder(
      'warning',
      t('views.purchase_receipt.alert.edit_blocked_direct.title'),
      { general: [t('views.purchase_receipt.alert.edit_blocked_direct.message')] },
    );
    router.push({ name: 'side-menu-purchase-receipt-list' });
    return false;
  }

  purchaseReceiptForm.setData({
    supplier_id: data.supplier?.id ?? null,
    purchase_id: data.purchase?.id ?? null,
    is_from_direct_purchase: false,
    code: data.code,
    date: formatDate(data.date, 'YYYY-MM-DD HH:mm:ss'),
    warehouse_id: data.warehouse?.id ?? null,
    remarks: data.remarks ?? '',
    is_posted: data.is_posted,
    items: (data.items ?? []).map((item: any) => buildFormItemFromReceiptItem(data, item)) as any,
  });

  appendPurchaseOption(data.purchase);

  if (data.purchase?.ulid) {
    await loadPurchaseDetail(data.purchase.ulid);
  }

  return true;
};
// #endregion

// #region Methods - Items
const searchProductUnits = async () => {
  if (!selectedUserLocation.value) return;

  isSearchingProductUnit.value = true;

  const result = await productService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search: productSearchText.value,
    category_id: undefined,
    brand_id: undefined,
    default_vat_profile_id: undefined,
    is_price_include_vat: undefined,
    is_use_serial_number: undefined,
    is_expirable: undefined,
    type: undefined,
    status: undefined,
    include_id: undefined,
    with_remaining_stock: undefined,
    refresh: true,
    limit: 50,
  } as any);

  isSearchingProductUnit.value = false;

  if (result.success && result.data) {
    const products = result.data.data as Product[];

    productUnitOptions.value = products.flatMap((product: Product) => {
      const units = product.product_units ?? [];
      const baseUnitName =
        units.find((unit) => Number(unit.conversion_value ?? 1) === 1)?.unit?.name ?? '';

      return units.map((unit: ProductUnit) => ({
        product_unit_id: unit.id,
        product_unit_code: unit.code,
        product_name: product.name,
        product_image_url: product.main_product_image?.url ?? null,
        unit_name: unit.unit?.name ?? '',
        base_unit_name: baseUnitName,
        conversion_value: Number(unit.conversion_value ?? 1),
        is_use_serial_number: Boolean(product.is_use_serial_number),
      }));
    });
  } else {
    productUnitOptions.value = [];
  }
};

const openAddProductUnit = () => {
  productSearchText.value = '';
  productUnitOptions.value = [];
  editingItemIndex.value = null;
  showProductUnitModal.value = true;
};

const openChangeProductUnit = (index: number) => {
  productSearchText.value = '';
  productUnitOptions.value = [];
  editingItemIndex.value = index;
  showProductUnitModal.value = true;
};

const selectProductUnit = (option: ProductUnitOption) => {
  const items = getItems();
  const serials = editingItemIndex.value === null
    ? []
    : items[editingItemIndex.value]?.is_use_serial_number
      ? [...(items[editingItemIndex.value]?.serials ?? [])]
      : [];

  const baseData = buildManualItemFromProductUnit(option);
  baseData.serials = serials;

  if (editingItemIndex.value === null) {
    purchaseReceiptForm.items.push(baseData as any);
  } else {
    const currentItem = items[editingItemIndex.value];

    purchaseReceiptForm.items[editingItemIndex.value] = {
      ...currentItem,
      ...baseData,
      qty: currentItem?.qty ?? 1,
      remarks: currentItem?.remarks ?? '',
      serials: baseData.is_use_serial_number ? serials : [],
    };
  }

  clearItemErrors();
  showProductUnitModal.value = false;
  editingItemIndex.value = null;
};

const removeItem = (index: number) => {
  const items = getItems();
  items.splice(index, 1);
  clearItemErrors();
};

const addSerial = (index: number) => {
  const item = getItems()[index];
  if (!item) return;

  item.serials.push({ serial: '' });
  purchaseReceiptForm.validate(`items.${index}.serials` as any);
};

const removeSerial = (index: number, serialIndex: number) => {
  const item = getItems()[index];
  if (!item) return;

  item.serials.splice(serialIndex, 1);
  purchaseReceiptForm.validate(`items.${index}.serials` as any);
};
// #endregion

// #region Actions
const onSubmit = async () => {
  if (purchaseReceiptForm.hasErrors) {
    const firstErrorKey = Object.keys(purchaseReceiptForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  const originalItems = getItems();
  const cleanedItems = originalItems.map((item) => ({
    purchase_item_id: item.purchase_item_id,
    qty: Number(item.qty ?? 0),
    product_unit_id: item.product_unit_id,
    product_unit_conversion_value: Number(item.product_unit_conversion_value ?? 0),
    remarks: item.remarks ?? '',
    serials: item.serials.map((serial) => ({
      serial: serial.serial,
    })),
  }));
  const backupItems = [...originalItems];

  purchaseReceiptForm.items = cleanedItems as any;
  emits('loading-state', true);

  try {
    await purchaseReceiptForm.submit();
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(
      t('views.purchase_receipt.alert.update.title'),
      t('views.purchase_receipt.alert.update.message'),
    );
    router.push({ name: 'side-menu-purchase-receipt-list' });
  } catch (error) {
    purchaseReceiptForm.items = backupItems as any;
    showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
  } finally {
    emits('loading-state', false);
  }
};
// #endregion
</script>

<template>
  <form v-if="selectedUserLocation && !initializing" id="purchaseReceiptForm" @submit.prevent="onSubmit">
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
            </div>

            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('code') }">
                {{ t('views.purchase_receipt.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="purchaseReceiptForm.code"
                :class="{ 'border-danger': invalidField('code') }"
                :placeholder="t('views.purchase_receipt.fields.code')"
                @set-auto="setCode"
                @change="purchaseReceiptForm.validate('code')"
              />
              <FormErrorMessages :messages="purchaseReceiptForm.errors.code" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('date') }">
                {{ t('views.purchase_receipt.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="purchaseReceiptForm.date"
                :class="{ 'border-danger': invalidField('date') }"
                :placeholder="t('views.purchase_receipt.fields.date')"
                @change="purchaseReceiptForm.validate('date')"
              />
              <FormErrorMessages :messages="purchaseReceiptForm.errors.date" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('supplier_id') }">
                {{ t('views.purchase_receipt.fields.supplier_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="purchaseReceiptForm.supplier_id"
                v-model:search="supplierSearch"
                :options="supplierOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :disabled="Boolean(purchaseReceiptForm.purchase_id)"
                :class="{ 'border-danger': invalidField('supplier_id') }"
                @change="handleSupplierChanged"
                @search="loadSupplierDDL"
                @clear="clearSupplier"
              />
              <FormErrorMessages :messages="purchaseReceiptForm.errors.supplier_id" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('purchase_id') }">
                {{ t('views.purchase_receipt.fields.purchase_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="purchaseReceiptForm.purchase_id"
                v-model:search="purchaseSearch"
                :options="purchaseOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': invalidField('purchase_id') }"
                @change="(value) => handlePurchaseChanged(value as string | number | null)"
                @search="loadPurchaseDDL"
                @clear="clearPurchase"
              />
              <FormErrorMessages :messages="purchaseReceiptForm.errors.purchase_id" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('warehouse_id') }">
                {{ t('views.purchase_receipt.fields.warehouse_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="purchaseReceiptForm.warehouse_id"
                v-model:search="warehouseSearch"
                :options="warehouseOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': invalidField('warehouse_id') }"
                @change="purchaseReceiptForm.validate('warehouse_id')"
                @search="loadWarehouseDDL"
                @clear="clearWarehouse"
              />
              <FormErrorMessages :messages="purchaseReceiptForm.errors.warehouse_id" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4 flex flex-col justify-center">
              <FormLabel :class="{ 'text-danger': invalidField('is_posted') }">
                {{ t('views.purchase_receipt.fields.is_posted') }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input v-model="purchaseReceiptForm.is_posted" type="checkbox" />
              </FormSwitch>
              <FormErrorMessages :messages="purchaseReceiptForm.errors.is_posted" />
            </div>
          </div>

          <FormInput v-model="purchaseReceiptForm.is_from_direct_purchase" type="hidden" />

          <div v-if="purchaseReceiptForm.purchase_id" class="mt-4 rounded-md border border-primary/20 bg-primary/5 px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
            {{ t('views.purchase_receipt.fields.purchase_link_hint') }}
          </div>

          <div class="mt-4">
            <FormLabel :class="{ 'text-danger': invalidField('remarks') }">
              {{ t('views.purchase_receipt.fields.remarks') }}
            </FormLabel>
            <FormTextarea
              v-model="purchaseReceiptForm.remarks"
              :class="{ 'border-danger': invalidField('remarks') }"
              :placeholder="t('views.purchase_receipt.fields.remarks')"
              @change="purchaseReceiptForm.validate('remarks')"
            />
            <FormErrorMessages :messages="purchaseReceiptForm.errors.remarks" />
          </div>
        </div>
      </template>

      <template #card-items-2>
        <div class="p-5 space-y-4">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="text-sm text-slate-500">
              {{ t('views.purchase_receipt.fields.item_count') }}: {{ getItems().length }}
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <Button
                v-if="purchaseReceiptForm.purchase_id"
                type="button"
                variant="outline-primary"
                @click="reloadItemsFromPurchase"
              >
                <Lucide icon="RefreshCw" class="mr-2 h-4 w-4" />
                {{ t('components.buttons.reload') }}
              </Button>
              <Button
                v-else
                type="button"
                variant="outline-primary"
                @click="openAddProductUnit"
              >
                <Lucide icon="Plus" class="mr-2 h-4 w-4" />
                {{ t('components.buttons.add') }}
              </Button>
            </div>
          </div>

          <FormErrorMessages :messages="purchaseReceiptForm.errors.items" />

          <div v-if="getItems().length === 0" class="rounded-md border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500 dark:border-darkmode-400">
            {{ t('views.purchase_receipt.fields.items_empty') }}
          </div>

          <div
            v-for="(item, index) in getItems()"
            :key="`${item.purchase_item_id ?? item.product_unit_id ?? 'item'}-${index}`"
            class="rounded-md border border-slate-200/70 p-4 dark:border-darkmode-400"
          >
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div>
                <div class="text-sm font-medium text-slate-900 dark:text-slate-100">
                  {{ item.product_unit_product_name || '-' }}
                </div>
                <div class="text-xs text-slate-500">
                  <span v-if="item.product_unit_product_code">[{{ item.product_unit_product_code }}]</span>
                  <span v-if="item.product_unit_unit_name">
                    {{ item.product_unit_product_code ? ' - ' : '' }}{{ item.product_unit_unit_name }}
                  </span>
                  <span v-if="item.product_unit_base_unit_name">
                    / {{ item.product_unit_base_unit_name }}
                  </span>
                </div>
                <div v-if="item.purchase_item_label" class="mt-1 text-xs text-primary">
                  {{ item.purchase_item_label }}
                </div>
              </div>
              <div class="flex items-center gap-2">
                <Button
                  v-if="!purchaseReceiptForm.purchase_id"
                  type="button"
                  size="sm"
                  variant="outline-primary"
                  @click="openChangeProductUnit(index)"
                >
                  <Lucide icon="Pencil" class="h-4 w-4" />
                </Button>
                <Button
                  type="button"
                  size="sm"
                  variant="outline-secondary"
                  @click="removeItem(index)"
                >
                  <Lucide icon="Trash2" class="h-4 w-4 text-danger" />
                </Button>
              </div>
            </div>

            <div class="mt-4 grid grid-cols-12 gap-4 gap-y-3">
              <div class="col-span-12 lg:col-span-4">
                <FormLabel :class="{ 'text-danger': invalidItemField(`items.${index}.product_unit_id`) }">
                  {{ t('views.purchase_receipt.fields.product_unit_id') }}
                </FormLabel>
                <FormInput
                  :model-value="item.product_unit_product_code ? `[${item.product_unit_product_code}] ${item.product_unit_product_name}` : item.product_unit_product_name"
                  readonly
                />
                <FormErrorMessages :messages="getItemFieldErrors(`items.${index}.product_unit_id`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel :class="{ 'text-danger': invalidItemField(`items.${index}.qty`) }">
                  {{ t('views.purchase_receipt.fields.qty') }}
                </FormLabel>
                <FormInput
                  :id="`items.${index}.qty`"
                  v-model="item.qty"
                  type="number"
                  min="0"
                  step="any"
                  :class="{ 'border-danger': invalidItemField(`items.${index}.qty`) }"
                  @change="purchaseReceiptForm.validate(`items.${index}.qty` as any)"
                />
                <FormErrorMessages :messages="getItemFieldErrors(`items.${index}.qty`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel :class="{ 'text-danger': invalidItemField(`items.${index}.product_unit_conversion_value`) }">
                  {{ t('views.purchase_receipt.fields.product_unit_conversion_value') }}
                </FormLabel>
                <FormInput
                  v-model="item.product_unit_conversion_value"
                  readonly
                  :class="{ 'border-danger': invalidItemField(`items.${index}.product_unit_conversion_value`) }"
                />
                <FormErrorMessages :messages="getItemFieldErrors(`items.${index}.product_unit_conversion_value`)" />
              </div>

              <div class="col-span-12 lg:col-span-4">
                <FormLabel :class="{ 'text-danger': invalidItemField(`items.${index}.remarks`) }">
                  {{ t('views.purchase_receipt.fields.remarks') }}
                </FormLabel>
                <FormTextarea
                  v-model="item.remarks"
                  :class="{ 'border-danger': invalidItemField(`items.${index}.remarks`) }"
                  :placeholder="t('views.purchase_receipt.fields.remarks')"
                  @change="purchaseReceiptForm.validate(`items.${index}.remarks` as any)"
                />
                <FormErrorMessages :messages="getItemFieldErrors(`items.${index}.remarks`)" />
              </div>

              <div v-if="item.is_use_serial_number" class="col-span-12">
                <div class="mb-2 flex items-center justify-between">
                  <FormLabel :class="{ 'text-danger': invalidItemField(`items.${index}.serials`) }">
                    {{ t('views.product.fields.serial_number') }}
                  </FormLabel>
                  <Button type="button" size="sm" variant="outline-primary" @click="addSerial(index)">
                    <Lucide icon="Plus" class="mr-1 h-3 w-3" />
                    {{ t('components.buttons.create') }}
                  </Button>
                </div>

                <div v-if="item.serials.length === 0" class="text-xs italic text-slate-500">
                  {{ t('components.data-list.data_not_found') }}
                </div>

                <div v-else class="grid grid-cols-1 gap-3 md:grid-cols-3 lg:grid-cols-4">
                  <div v-for="(serial, serialIndex) in item.serials" :key="`${index}-${serialIndex}`" class="flex gap-2">
                    <FormInput
                      v-model="serial.serial"
                      :placeholder="t('views.product.fields.serial_number')"
                      :class="{ 'border-danger': invalidItemField(`items.${index}.serials.${serialIndex}.serial`) }"
                      @change="
                        purchaseReceiptForm.validate(`items.${index}.serials.${serialIndex}.serial` as any);
                        purchaseReceiptForm.validate(`items.${index}.serials` as any);
                      "
                    />
                    <Button type="button" variant="outline-secondary" @click="removeSerial(index, serialIndex)">
                      <Lucide icon="Trash2" class="h-4 w-4 text-danger" />
                    </Button>
                  </div>
                </div>

                <FormErrorMessages :messages="getItemFieldErrors(`items.${index}.serials`)" />
                <FormErrorMessages
                  v-for="(_, serialIndex) in item.serials"
                  :key="`serial-error-${index}-${serialIndex}`"
                  :messages="getItemFieldErrors(`items.${index}.serials.${serialIndex}.serial`)"
                />
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #card-items-button>
        <div class="flex justify-end gap-2 p-5">
          <Button
            type="submit"
            variant="primary"
            class="w-32 shadow-md"
            :disabled="purchaseReceiptForm.validating || purchaseReceiptForm.hasErrors"
          >
            <Lucide v-if="purchaseReceiptForm.validating" icon="Loader" class="mr-2 h-4 w-4 animate-spin" />
            <Lucide v-else icon="Save" class="mr-2 h-4 w-4" />
            {{ t('components.buttons.save') }}
          </Button>
        </div>
      </template>
    </TwoColumnsLayout>
  </form>

  <ProductUnitPickerDialog
    v-if="selectedUserLocation"
    v-model:search-text="productSearchText"
    :open="showProductUnitModal"
    :title="t('views.purchase_receipt.fields.product_unit_id')"
    :is-searching="isSearchingProductUnit"
    :options="productUnitOptions"
    :columns="productUnitDialogColumns"
    @close="showProductUnitModal = false"
    @search="searchProductUnits"
    @select="selectProductUnit($event as ProductUnitOption)"
  />
</template>
