<script setup lang="ts">
// #region Imports
import { computed, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';
import { convertErrorTypeToAlertListType } from '@/utils/helper';
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
  FormInputCurrency,
  FormInputDateTimeAuto,
  FormLabel,
  FormSelectSearch,
  FormSwitch,
  FormTextarea,
} from '@/components/Base/Form';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import ProductImagePreview from '@/components/Product/ProductImagePreview.vue';
import ProductUnitSelectSearch, {
  type ProductUnitSelectOption,
} from '@/components/Product/ProductUnitSelectSearch.vue';
import CacheService from '@/services/CacheService';
import CashAccountService from '@/services/CashAccountService';
import ProductService from '@/services/ProductService';
import PurchaseOrderReceiptService from '@/services/PurchaseOrderReceiptService';
import PurchaseOrderService from '@/services/PurchaseOrderService';
import WarehouseService from '@/services/WarehouseService';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { NotificationData } from '@/types/models/NotificationData';
import type { Product } from '@/types/models/Product';
import type { ProductUnit } from '@/types/models/ProductUnit';
import type { PurchaseOrder } from '@/types/models/PurchaseOrder';
import type { PurchaseOrderItem } from '@/types/models/PurchaseOrderItem';
import type {
  PurchaseOrderReceiptCostNestedStoreRequest,
  PurchaseOrderReceiptItemNestedStoreRequest,
} from '@/types/services/purchase-order-receipt/PurchaseOrderReceiptRequest';
// #endregion

// #region Declarations
type PurchaseOrderOption = DropDownOption & {
  ulid: string;
  supplier_id: string | null;
  supplier_name: string | null;
};

type PurchaseOrderReceiptSerialFormItem = {
  serial: string;
};

type PurchaseOrderReceiptItemFormItem = PurchaseOrderReceiptItemNestedStoreRequest & {
  product_unit_product_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_unit_name?: string | null;
  product_unit_base_unit_name?: string | null;
  product_unit_product_image_url?: string | null;
  purchase_order_item_label?: string | null;
  ui_expanded?: boolean;
  is_use_serial_number?: boolean;
};

type PurchaseOrderReceiptCostFormItem = PurchaseOrderReceiptCostNestedStoreRequest;

type ProductUnitOption = ProductUnitSelectOption;

const emits = defineEmits([
  'loading-state',
  'mode-state',
  'update-profile',
  'show-alertplaceholder',
  'show-notification',
]);

const { t } = useI18n();
const router = useRouter();

const selectedUserLocationStore = useSelectedUserLocationStore();
const purchaseOrderReceiptService = new PurchaseOrderReceiptService();
const purchaseOrderService = new PurchaseOrderService();
const warehouseService = new WarehouseService();
const cashAccountService = new CashAccountService();
const productService = new ProductService();
const cacheService = new CacheService();

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const purchaseOrderReceiptForm: any = purchaseOrderReceiptService.usePurchaseOrderReceiptCreateForm();

const selectedPurchaseOrderData = ref<PurchaseOrder | null>(null);
const initializing = ref<boolean>(true);
const purchaseOrderSearch = ref<string>('');
const warehouseSearch = ref<string>('');
const cashAccountSearch = ref<string>('');

const purchaseOrderDDL = ref<Array<PurchaseOrderOption>>([]);
const warehouseDDL = ref<Array<DropDownOption>>([]);
const cashAccountDDL = ref<Array<DropDownOption>>([]);

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
  {
    title: 'views.purchase_receipt.field_groups.costs',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const purchaseOrderOptions = computed(() =>
  purchaseOrderDDL.value.map((item) => ({
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

const cashAccountOptions = computed(() =>
  cashAccountDDL.value.map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const selectedSupplierName = computed(
  () =>
    selectedPurchaseOrderData.value?.supplier?.name
    ?? purchaseOrderDDL.value.find((item) => item.code === purchaseOrderReceiptForm.purchase_order_id)?.supplier_name
    ?? '',
);

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
  purchaseOrderReceiptForm,
  debounce((newValue: any) => {
    cacheService.setLastEntity('PURCHASE_RECEIPT_CREATE', newValue.data());
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

  purchaseOrderReceiptForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
  });

  await Promise.all([loadPurchaseOrderDDL(), loadWarehouseDDL(), loadCashAccountDDL()]);
  initializing.value = false;
});
// #endregion

// #region Methods - Helpers
const getItems = () => purchaseOrderReceiptForm.items as PurchaseOrderReceiptItemFormItem[];

const getCosts = () => purchaseOrderReceiptForm.costs as PurchaseOrderReceiptCostFormItem[];

const getItemFieldErrors = (field: string) => (purchaseOrderReceiptForm.errors as any)[field];

const invalidField = (field: string) => purchaseOrderReceiptForm.invalid(field);

const invalidItemField = (field: string) => purchaseOrderReceiptForm.invalid(field as any);

const itemDetailsInvalid = (index: number, item: PurchaseOrderReceiptItemFormItem) =>
  invalidItemField(`items.${index}.remarks`)
  || invalidItemField(`items.${index}.serials`)
  || (item.serials ?? []).some((_: unknown, serialIndex: number) =>
    invalidItemField(`items.${index}.serials.${serialIndex}.serial`),
  );

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
  Object.keys(purchaseOrderReceiptForm.errors).forEach((key) => {
    if (key.startsWith('items.')) {
      purchaseOrderReceiptForm.forgetError(key as any);
    }
  });
};

const clearCostErrors = () => {
  Object.keys(purchaseOrderReceiptForm.errors).forEach((key) => {
    if (key.startsWith('costs.')) {
      purchaseOrderReceiptForm.forgetError(key as any);
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

const appendPurchaseOrderOption = (purchaseOrder: PurchaseOrder | null | undefined) => {
  if (!purchaseOrder?.id || !purchaseOrder.ulid) return;

  if (!purchaseOrderDDL.value.some((item) => item.code === purchaseOrder.id)) {
    purchaseOrderDDL.value = [
      ...purchaseOrderDDL.value,
      {
        code: purchaseOrder.id,
        name: `${purchaseOrder.code} - ${purchaseOrder.supplier?.name ?? '-'}`,
        ulid: purchaseOrder.ulid,
        supplier_id: purchaseOrder.supplier?.id ?? null,
        supplier_name: purchaseOrder.supplier?.name ?? null,
      },
    ];
  }
};

const buildBaseUnitName = (product: Product | null | undefined, conversionValue: number) => {
  if (!product || conversionValue === 1) {
    return '';
  }

  // product_units is only present when the API eager-loads it; the parent
  // document detail sends base_product_unit instead.
  const baseUnitName = product.base_product_unit?.unit?.name;
  if (baseUnitName) return baseUnitName;

  return (product.product_units ?? []).find((unit) => Number(unit.conversion_value ?? 1) === 1)?.unit?.name ?? '';
};

const formatPurchaseOrderItemLabel = (purchaseOrderItem: PurchaseOrderItem | null | undefined) => {
  if (!purchaseOrderItem) return null;

  const productUnit = purchaseOrderItem.product_unit;
  const productCode = productUnit?.code ?? '';
  const productName = productUnit?.product?.name ?? '-';

  return productCode ? `[${productCode}] ${productName}` : productName;
};

const buildItemFromProductUnitOption = (option: ProductUnitOption): PurchaseOrderReceiptItemFormItem => ({
  purchase_order_item_id: option.purchase_order_item_id ?? null,
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
  ui_expanded: option.is_use_serial_number,
  purchase_order_item_label: null,
  is_use_serial_number: option.is_use_serial_number,
});

const buildReceiptItemFromPurchaseOrderItem = (
  purchaseOrderItem: PurchaseOrderItem,
): PurchaseOrderReceiptItemFormItem => {
  const productUnit = purchaseOrderItem.product_unit;
  const product = productUnit?.product;
  const conversionValue = Number(purchaseOrderItem.product_unit_conversion_value ?? productUnit?.conversion_value ?? 1);
  const outstandingBase = Number(purchaseOrderItem.qty_outstanding_base ?? 0);
  const defaultQty = outstandingBase > 0
    ? outstandingBase / Math.max(conversionValue, 1)
    : Number(purchaseOrderItem.qty ?? 0);

  return {
    purchase_order_item_id: purchaseOrderItem.id ?? null,
    qty: defaultQty > 0 ? defaultQty : 1,
    product_unit_id: productUnit?.id ?? '',
    product_unit_conversion_value: conversionValue,
    remarks: purchaseOrderItem.remarks ?? '',
    serials: [],
    product_unit_product_code: productUnit?.code ?? '',
    product_unit_product_name: product?.name ?? '-',
    product_unit_unit_name: productUnit?.unit?.name ?? '',
    product_unit_base_unit_name: buildBaseUnitName(product, Number(productUnit?.conversion_value ?? conversionValue)),
    product_unit_product_image_url: product?.main_product_image?.url ?? null,
    ui_expanded: Boolean(product?.is_use_serial_number),
    purchase_order_item_label: formatPurchaseOrderItemLabel(purchaseOrderItem),
    is_use_serial_number: Boolean(product?.is_use_serial_number),
  };
};

const loadFromCache = () => {
  const data = cacheService.getLastEntity('PURCHASE_RECEIPT_CREATE') as Record<string, unknown>;
  if (!data) return;
  purchaseOrderReceiptForm.setData(data);
};
// #endregion

// #region Methods - Purchase Order Receipt
const loadPurchaseOrderDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await purchaseOrderService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: null,
    end_date: null,
    supplier_id: null,
    progress_status: null,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    purchaseOrderDDL.value = result.data.data
      // a receipt may only be created for a purchase order that already has a supplier
      .filter((item) => Boolean(item.supplier?.id))
      .map((item) => ({
        code: item.id,
        name: `${item.code} - ${item.supplier?.name ?? '-'}`,
        ulid: item.ulid,
        supplier_id: item.supplier?.id ?? null,
        supplier_name: item.supplier?.name ?? null,
      }));
  } else {
    purchaseOrderDDL.value = [];
  }

  appendPurchaseOrderOption(selectedPurchaseOrderData.value);
};

const loadWarehouseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await warehouseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    include_id: (purchaseOrderReceiptForm.warehouse_id as string | null | undefined) ?? undefined,
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
};

const loadCashAccountDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await cashAccountService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    is_bank: undefined,
    include_id: undefined,
    with_remaining_balance: undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    cashAccountDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const setCode = () => {
  purchaseOrderReceiptForm.forgetError('code');

  if (purchaseOrderReceiptForm.code === '_AUTO_') {
    purchaseOrderReceiptForm.setData({ code: '' });
    return;
  }

  purchaseOrderReceiptForm.setData({ code: '_AUTO_' });
};

const setCostCode = (index: number) => {
  purchaseOrderReceiptForm.forgetError(`costs.${index}.code` as any);
  const cost = getCosts()[index];
  if (!cost) return;
  cost.code = cost.code === '_AUTO_' ? '' : '_AUTO_';
};

const loadPurchaseOrderDetail = async (purchaseOrderUlid: string) => {
  const result = await purchaseOrderService.read(purchaseOrderUlid);

  if (result.success && result.data) {
    selectedPurchaseOrderData.value = result.data;
    appendPurchaseOrderOption(result.data);

    return result.data;
  }

  showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  return null;
};

const syncItemsFromPurchaseOrder = (purchaseOrder: PurchaseOrder) => {
  purchaseOrderReceiptForm.setData({
    supplier_id: purchaseOrder.supplier?.id ?? null,
    items: (purchaseOrder.items ?? []).map((item) => buildReceiptItemFromPurchaseOrderItem(item)) as any,
  });

  clearItemErrors();
};

const clearPurchaseOrder = async () => {
  selectedPurchaseOrderData.value = null;
  purchaseOrderReceiptForm.setData({
    purchase_order_id: null,
    supplier_id: null,
    items: [] as any,
  });
  purchaseOrderReceiptForm.forgetError('purchase_order_id');
  purchaseOrderReceiptForm.forgetError('supplier_id');
  clearItemErrors();
  await loadPurchaseOrderDDL();
};

const clearWarehouse = () => {
  purchaseOrderReceiptForm.setData({ warehouse_id: null });
  purchaseOrderReceiptForm.forgetError('warehouse_id');
};

const clearCostCashAccount = (index: number) => {
  const cost = getCosts()[index];
  if (!cost) return;
  cost.cash_account_id = '';
  purchaseOrderReceiptForm.validate(`costs.${index}.cash_account_id` as any);
};

const handlePurchaseOrderChanged = async (purchaseOrderId: string | number | null) => {
  if (!purchaseOrderId) {
    await clearPurchaseOrder();
    return;
  }

  const option = purchaseOrderDDL.value.find((item) => item.code === purchaseOrderId);
  if (!option) {
    purchaseOrderReceiptForm.validate('purchase_order_id');
    return;
  }

  const purchaseOrder = await loadPurchaseOrderDetail(option.ulid);
  if (!purchaseOrder) {
    purchaseOrderReceiptForm.validate('purchase_order_id');
    return;
  }

  syncItemsFromPurchaseOrder(purchaseOrder);
  purchaseOrderReceiptForm.forgetError('supplier_id');
  purchaseOrderReceiptForm.forgetError('branch_id');
  purchaseOrderReceiptForm.validate('purchase_order_id');
};

const reloadItemsFromPurchaseOrder = async () => {
  if (!purchaseOrderReceiptForm.purchase_order_id) {
    return;
  }

  const option = purchaseOrderDDL.value.find((item) => item.code === purchaseOrderReceiptForm.purchase_order_id);
  if (!option) {
    return;
  }

  const purchaseOrder = await loadPurchaseOrderDetail(option.ulid);
  if (!purchaseOrder) {
    return;
  }

  syncItemsFromPurchaseOrder(purchaseOrder);
};
// #endregion

// #region Methods - Items
const buildPurchaseOrderProductUnitOptions = (): Array<ProductUnitOption> =>
  (selectedPurchaseOrderData.value?.items ?? []).map((purchaseOrderItem) => {
    const productUnit = purchaseOrderItem.product_unit;
    const product = productUnit?.product;
    const conversionValue = Number(
      purchaseOrderItem.product_unit_conversion_value ?? productUnit?.conversion_value ?? 1,
    );

    return {
      product_unit_id: productUnit?.id ?? '',
      product_unit_code: productUnit?.code ?? '',
      product_name: product?.name ?? '-',
      product_image_url: product?.main_product_image?.url ?? null,
      unit_name: productUnit?.unit?.name ?? '',
      base_unit_name: buildBaseUnitName(product, Number(productUnit?.conversion_value ?? conversionValue)),
      conversion_value: conversionValue,
      is_use_serial_number: Boolean(product?.is_use_serial_number),
      purchase_order_item_id: purchaseOrderItem.id ?? null,
    };
  });

const fetchProductUnitOptions = async (search: string): Promise<Array<ProductUnitOption>> => {
  if (!selectedUserLocation.value) return [];

  // with no search term the picker offers the selected purchase order's products first
  if (!search && selectedPurchaseOrderData.value) {
    return buildPurchaseOrderProductUnitOptions();
  }

  const result = await productService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
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

  if (!result.success || !result.data) return [];

  const products = result.data.data as Product[];
  const purchaseOrderItemIdByProductUnitId = new Map<string, string>();
  (selectedPurchaseOrderData.value?.items ?? []).forEach((purchaseOrderItem) => {
    if (purchaseOrderItem.product_unit?.id && purchaseOrderItem.id) {
      purchaseOrderItemIdByProductUnitId.set(purchaseOrderItem.product_unit.id, purchaseOrderItem.id);
    }
  });

  return products.flatMap((product: Product) => {
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
      purchase_order_item_id: purchaseOrderItemIdByProductUnitId.get(unit.id) ?? null,
    }));
  });
};

const buildItemInitialOption = (item: PurchaseOrderReceiptItemFormItem): ProductUnitSelectOption | null => {
  if (!item.product_unit_id) return null;

  return {
    product_unit_id: item.product_unit_id,
    product_unit_code: item.product_unit_product_code ?? '',
    product_name: item.product_unit_product_name ?? '-',
    product_image_url: item.product_unit_product_image_url ?? null,
    unit_name: item.product_unit_unit_name ?? '',
    base_unit_name: item.product_unit_base_unit_name ?? '',
    conversion_value: Number(item.product_unit_conversion_value ?? 1),
    is_use_serial_number: Boolean(item.is_use_serial_number),
    purchase_order_item_id: item.purchase_order_item_id ?? null,
  };
};

const addItem = () => {
  purchaseOrderReceiptForm.items.push({
    purchase_order_item_id: null,
    qty: 1,
    product_unit_id: '',
    product_unit_conversion_value: 1,
    remarks: '',
    serials: [],
    product_unit_product_code: null,
    product_unit_product_name: null,
    product_unit_unit_name: null,
    product_unit_base_unit_name: null,
    product_unit_product_image_url: null,
    ui_expanded: false,
    purchase_order_item_label: null,
    is_use_serial_number: false,
  } as any);
};

const handleProductUnitSelected = (index: number, option: ProductUnitOption | null) => {
  const items = getItems();
  const currentItem = items[index];
  if (!currentItem) return;

  if (!option) {
    currentItem.purchase_order_item_id = null;
    currentItem.product_unit_id = '';
    currentItem.product_unit_conversion_value = 1;
    currentItem.product_unit_product_code = null;
    currentItem.product_unit_product_name = null;
    currentItem.product_unit_unit_name = null;
    currentItem.product_unit_base_unit_name = null;
    currentItem.product_unit_product_image_url = null;
    currentItem.purchase_order_item_label = null;
    currentItem.is_use_serial_number = false;
    currentItem.serials = [];
    purchaseOrderReceiptForm.forgetError(`items.${index}.product_unit_id` as any);
    return;
  }

  const serials = currentItem.is_use_serial_number ? [...(currentItem.serials ?? [])] : [];
  const baseData = buildItemFromProductUnitOption(option);

  purchaseOrderReceiptForm.items[index] = {
    ...currentItem,
    ...baseData,
    qty: currentItem.qty ?? 1,
    remarks: currentItem.remarks ?? '',
    serials: baseData.is_use_serial_number ? serials : [],
  };

  clearItemErrors();
  purchaseOrderReceiptForm.validate(`items.${index}.product_unit_id` as any);
};

const removeItem = (index: number) => {
  const items = getItems();
  items.splice(index, 1);
  clearItemErrors();
};

const addSerial = (index: number) => {
  const item = getItems()[index];
  if (!item) return;

  item.serials.push({ serial: '' } as PurchaseOrderReceiptSerialFormItem);
  purchaseOrderReceiptForm.validate(`items.${index}.serials` as any);
};

const removeSerial = (index: number, serialIndex: number) => {
  const item = getItems()[index];
  if (!item) return;

  item.serials.splice(serialIndex, 1);
  purchaseOrderReceiptForm.validate(`items.${index}.serials` as any);
};
// #endregion

// #region Methods - Costs
const addCost = () => {
  getCosts().push({
    code: '_AUTO_',
    date: '_AUTO_',
    name: '',
    cash_account_id: '',
    amount: 0,
    remarks: '',
  });
};

const removeCost = (index: number) => {
  getCosts().splice(index, 1);
  clearCostErrors();
};

const getCostsTotalPreview = () =>
  getCosts().reduce((total, cost) => total + Math.max(Number(cost.amount || 0), 0), 0);
// #endregion

// #region Actions
const onSubmit = async () => {
  if (purchaseOrderReceiptForm.hasErrors) {
    const firstErrorKey = Object.keys(purchaseOrderReceiptForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  const originalItems = getItems();
  const originalCosts = getCosts();
  const cleanedItems: PurchaseOrderReceiptItemNestedStoreRequest[] = originalItems.map((item) => ({
    purchase_order_item_id: item.purchase_order_item_id ?? null,
    qty: Number(item.qty ?? 0),
    product_unit_id: item.product_unit_id,
    product_unit_conversion_value: Number(item.product_unit_conversion_value ?? 0),
    remarks: item.remarks ?? '',
    serials: item.serials.map((serial) => ({
      serial: serial.serial,
    })),
  }));
  const cleanedCosts: PurchaseOrderReceiptCostNestedStoreRequest[] = originalCosts.map((cost) => ({
    code: cost.code,
    date: cost.date,
    name: cost.name,
    cash_account_id: cost.cash_account_id,
    amount: Number(cost.amount ?? 0),
    remarks: cost.remarks ?? '',
  }));
  const backupItems = [...originalItems];
  const backupCosts = [...originalCosts];

  purchaseOrderReceiptForm.items = cleanedItems as any;
  purchaseOrderReceiptForm.costs = cleanedCosts as any;
  emits('loading-state', true);

  try {
    await purchaseOrderReceiptForm.submit();
    cacheService.removeLastEntity('PURCHASE_RECEIPT_CREATE');
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(
      t('views.purchase_receipt.alert.create.title'),
      t('views.purchase_receipt.alert.create.message'),
    );
    router.push({ name: 'side-menu-purchase-receipt-list' });
  } catch (error) {
    purchaseOrderReceiptForm.items = backupItems as any;
    purchaseOrderReceiptForm.costs = backupCosts as any;
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
              <FormInput v-model="purchaseOrderReceiptForm.company_id" type="hidden" />
            </div>

            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput v-model="purchaseOrderReceiptForm.branch_id" type="hidden" />
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
                v-model="purchaseOrderReceiptForm.code"
                :class="{ 'border-danger': invalidField('code') }"
                :placeholder="t('views.purchase_receipt.fields.code')"
                @set-auto="setCode"
                @change="purchaseOrderReceiptForm.validate('code')"
              />
              <FormErrorMessages :messages="purchaseOrderReceiptForm.errors.code" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('date') }">
                {{ t('views.purchase_receipt.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="purchaseOrderReceiptForm.date"
                :class="{ 'border-danger': invalidField('date') }"
                :placeholder="t('views.purchase_receipt.fields.date')"
                @change="purchaseOrderReceiptForm.validate('date')"
              />
              <FormErrorMessages :messages="purchaseOrderReceiptForm.errors.date" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('purchase_order_id') }">
                {{ t('views.purchase_receipt.fields.purchase_order_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="purchaseOrderReceiptForm.purchase_order_id"
                v-model:search="purchaseOrderSearch"
                :options="purchaseOrderOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': invalidField('purchase_order_id') }"
                @change="(value) => handlePurchaseOrderChanged(value as string | number | null)"
                @search="loadPurchaseOrderDDL"
                @clear="clearPurchaseOrder"
              />
              <FormErrorMessages :messages="purchaseOrderReceiptForm.errors.purchase_order_id" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('supplier_id') }">
                {{ t('views.purchase_receipt.fields.supplier_id') }}
              </FormLabel>
              <FormInput :model-value="selectedSupplierName || '-'" readonly />
              <FormErrorMessages :messages="purchaseOrderReceiptForm.errors.supplier_id" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('warehouse_id') }">
                {{ t('views.purchase_receipt.fields.warehouse_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="purchaseOrderReceiptForm.warehouse_id"
                v-model:search="warehouseSearch"
                :options="warehouseOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': invalidField('warehouse_id') }"
                @change="purchaseOrderReceiptForm.validate('warehouse_id')"
                @search="loadWarehouseDDL"
                @clear="clearWarehouse"
              />
              <FormErrorMessages :messages="purchaseOrderReceiptForm.errors.warehouse_id" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4 flex flex-col justify-center">
              <FormLabel :class="{ 'text-danger': invalidField('is_posted') }">
                {{ t('views.purchase_receipt.fields.is_posted') }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input v-model="purchaseOrderReceiptForm.is_posted" type="checkbox" />
              </FormSwitch>
              <FormErrorMessages :messages="purchaseOrderReceiptForm.errors.is_posted" />
            </div>
          </div>

          <div
            v-if="purchaseOrderReceiptForm.purchase_order_id"
            class="mt-4 rounded-md border border-primary/20 bg-primary/5 px-4 py-3 text-sm text-slate-700 dark:text-slate-200"
          >
            {{ t('views.purchase_receipt.fields.purchase_order_link_hint') }}
          </div>

          <div class="mt-4">
            <FormLabel :class="{ 'text-danger': invalidField('remarks') }">
              {{ t('views.purchase_receipt.fields.remarks') }}
            </FormLabel>
            <FormTextarea
              v-model="purchaseOrderReceiptForm.remarks"
              :class="{ 'border-danger': invalidField('remarks') }"
              :placeholder="t('views.purchase_receipt.fields.remarks')"
              @change="purchaseOrderReceiptForm.validate('remarks')"
            />
            <FormErrorMessages :messages="purchaseOrderReceiptForm.errors.remarks" />
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
                v-if="purchaseOrderReceiptForm.purchase_order_id"
                type="button"
                variant="outline-secondary"
                @click="reloadItemsFromPurchaseOrder"
              >
                <Lucide icon="RefreshCw" class="mr-2 h-4 w-4" />
                {{ t('components.buttons.reload') }}
              </Button>
              <Button
                type="button"
                variant="outline-primary"
                :disabled="!purchaseOrderReceiptForm.purchase_order_id"
                @click="addItem"
              >
                <Lucide icon="Plus" class="mr-2 h-4 w-4" />
                {{ t('components.buttons.add') }}
              </Button>
            </div>
          </div>

          <FormErrorMessages :messages="purchaseOrderReceiptForm.errors.items" />

          <div
            v-if="getItems().length === 0"
            class="rounded-md border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500 dark:border-darkmode-400"
          >
            {{ t('views.purchase_receipt.fields.items_empty') }}
          </div>

          <div
            v-else
            class="divide-y divide-slate-200/70 rounded-md border border-slate-200/70 dark:divide-darkmode-400 dark:border-darkmode-400"
          >
            <div
              class="grid grid-cols-[minmax(0,1fr)_6rem_4.5rem_4rem_5.5rem] items-center gap-3 bg-slate-50/70 px-3 py-2 text-xs font-medium text-slate-500 dark:bg-darkmode-600/30 dark:text-slate-400"
            >
              <div class="truncate">{{ t('views.purchase_receipt.fields.product_unit_id') }}</div>
              <div class="truncate text-right">{{ t('views.purchase_receipt.fields.qty') }}</div>
              <div class="truncate">{{ t('views.product.table.cols.unit') }}</div>
              <div class="truncate text-right" :title="t('views.purchase_receipt.fields.product_unit_conversion_value')">
                {{ t('views.purchase_receipt.fields.product_unit_conversion_value') }}
              </div>
              <div></div>
            </div>
          <div
            v-for="(item, index) in getItems()"
            :key="`${item.purchase_order_item_id ? 'po' : 'extra'}-${item.product_unit_id ?? 'item'}-${index}`"
          >
            <div class="grid grid-cols-[minmax(0,1fr)_6rem_4.5rem_4rem_5.5rem] items-center gap-3 px-3 py-2">
              <div class="flex min-w-0 items-center gap-3">
              <ProductImagePreview
                :image-url="item.product_unit_product_image_url"
                wrapper-class="flex h-10 w-10 shrink-0 cursor-zoom-in items-center justify-center overflow-hidden rounded-md bg-slate-100 dark:bg-darkmode-600"
                icon-class="h-4 w-4 text-slate-400"
                :preview-title="item.product_unit_product_name || '-'"
              />
              <div class="min-w-0 flex-1">
                <ProductUnitSelectSearch
                  :model-value="item.product_unit_id"
                  :fetch-options="fetchProductUnitOptions"
                  :initial-option="buildItemInitialOption(item)"
                  :invalid="invalidItemField(`items.${index}.product_unit_id`)"
                  :placeholder="t('components.dropdown.placeholder')"
                  @select="handleProductUnitSelected(index, $event)"
                />
              </div>
              </div>
              <div>
                <FormInput
                  :id="`items.${index}.qty`"
                  v-model="item.qty"
                  type="number"
                  min="0"
                  step="any"
                  class="text-right"
                  :title="t('views.purchase_receipt.fields.qty')"
                  :class="{ 'border-danger': invalidItemField(`items.${index}.qty`) }"
                  @change="purchaseOrderReceiptForm.validate(`items.${index}.qty` as any)"
                />
              </div>
              <div class="min-w-0 truncate text-xs text-slate-500 dark:text-slate-400">
                {{ item.product_unit_unit_name || '-' }}
              </div>
              <div
                class="text-right text-xs text-slate-500"
                :title="t('views.purchase_receipt.fields.product_unit_conversion_value')"
              >
                &times; {{ item.product_unit_conversion_value }}
              </div>
              <div class="flex items-center justify-end gap-1">
                <Button
                  type="button"
                  variant="outline-secondary"
                  class="flex h-[38px] w-[38px] min-w-0 items-center justify-center p-0"
                  :class="{ 'border-danger text-danger': !item.ui_expanded && itemDetailsInvalid(index, item) }"
                  @click="item.ui_expanded = !item.ui_expanded"
                >
                  <Lucide :icon="item.ui_expanded ? 'ChevronUp' : 'ChevronDown'" class="h-4 w-4" />
                </Button>
                <Button type="button" variant="outline-secondary" class="flex h-[38px] w-[38px] min-w-0 items-center justify-center p-0" @click="removeItem(index)">
                  <Lucide icon="Trash2" class="h-4 w-4 text-danger" />
                </Button>
              </div>
            </div>

            <div class="px-3">
              <FormErrorMessages :messages="getItemFieldErrors(`items.${index}.product_unit_id`)" />
              <FormErrorMessages :messages="getItemFieldErrors(`items.${index}.purchase_order_item_id`)" />
              <FormErrorMessages :messages="getItemFieldErrors(`items.${index}.qty`)" />
              <FormErrorMessages :messages="getItemFieldErrors(`items.${index}.product_unit_conversion_value`)" />
            </div>

            <div v-if="item.ui_expanded" class="space-y-3 bg-slate-50/70 px-3 py-3 dark:bg-darkmode-600/30">
              <div>
                <FormLabel :class="{ 'text-danger': invalidItemField(`items.${index}.remarks`) }">
                  {{ t('views.purchase_receipt.fields.remarks') }}
                </FormLabel>
                <FormTextarea
                  v-model="item.remarks"
                  :class="{ 'border-danger': invalidItemField(`items.${index}.remarks`) }"
                  :placeholder="t('views.purchase_receipt.fields.remarks')"
                  @change="purchaseOrderReceiptForm.validate(`items.${index}.remarks` as any)"
                />
                <FormErrorMessages :messages="getItemFieldErrors(`items.${index}.remarks`)" />
              </div>

              <div v-if="item.is_use_serial_number">
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
                        purchaseOrderReceiptForm.validate(`items.${index}.serials.${serialIndex}.serial` as any);
                        purchaseOrderReceiptForm.validate(`items.${index}.serials` as any);
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
        </div>
      </template>

      <template #card-items-3>
        <div class="p-5 space-y-4">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="text-sm text-slate-500">
              {{ t('views.purchase_receipt.fields.cost_count') }}: {{ getCosts().length }}
            </div>
            <Button type="button" variant="outline-primary" @click="addCost">
              <Lucide icon="Plus" class="mr-2 h-4 w-4" />
              {{ t('components.buttons.add') }}
            </Button>
          </div>

          <FormErrorMessages :messages="purchaseOrderReceiptForm.errors.costs" />

          <div
            v-if="getCosts().length === 0"
            class="rounded-md border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500 dark:border-darkmode-400"
          >
            {{ t('views.purchase_receipt.fields.costs_empty') }}
          </div>

          <div
            v-for="(cost, index) in getCosts()"
            :key="`cost-${index}`"
            class="rounded-md border border-slate-200/70 p-4 dark:border-darkmode-400"
          >
            <div class="grid grid-cols-12 gap-4 gap-y-3">
              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel :class="{ 'text-danger': invalidItemField(`costs.${index}.code`) }">
                  {{ t('views.purchase_receipt.fields.code') }}
                </FormLabel>
                <FormInputCode
                  v-model="cost.code"
                  :class="{ 'border-danger': invalidItemField(`costs.${index}.code`) }"
                  :placeholder="t('views.purchase_receipt.fields.code')"
                  @set-auto="setCostCode(index)"
                  @change="purchaseOrderReceiptForm.validate(`costs.${index}.code` as any)"
                />
                <FormErrorMessages :messages="getItemFieldErrors(`costs.${index}.code`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-3">
                <FormLabel :class="{ 'text-danger': invalidItemField(`costs.${index}.date`) }">
                  {{ t('views.purchase_receipt.fields.date') }}
                </FormLabel>
                <FormInputDateTimeAuto
                  v-model="cost.date"
                  :class="{ 'border-danger': invalidItemField(`costs.${index}.date`) }"
                  :placeholder="t('views.purchase_receipt.fields.date')"
                  @change="purchaseOrderReceiptForm.validate(`costs.${index}.date` as any)"
                />
                <FormErrorMessages :messages="getItemFieldErrors(`costs.${index}.date`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-3">
                <FormLabel :class="{ 'text-danger': invalidItemField(`costs.${index}.name`) }">
                  {{ t('views.purchase_receipt.fields.cost_name') }}
                </FormLabel>
                <FormInput
                  v-model="cost.name"
                  :class="{ 'border-danger': invalidItemField(`costs.${index}.name`) }"
                  :placeholder="t('views.purchase_receipt.fields.cost_name')"
                  @change="purchaseOrderReceiptForm.validate(`costs.${index}.name` as any)"
                />
                <FormErrorMessages :messages="getItemFieldErrors(`costs.${index}.name`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel :class="{ 'text-danger': invalidItemField(`costs.${index}.cash_account_id`) }">
                  {{ t('views.purchase_receipt.fields.cash_account_id') }}
                </FormLabel>
                <FormSelectSearch
                  v-model="cost.cash_account_id"
                  v-model:search="cashAccountSearch"
                  :options="cashAccountOptions"
                  :placeholder="t('components.dropdown.placeholder')"
                  :class="{ 'border-danger': invalidItemField(`costs.${index}.cash_account_id`) }"
                  @change="purchaseOrderReceiptForm.validate(`costs.${index}.cash_account_id` as any)"
                  @search="loadCashAccountDDL"
                  @clear="clearCostCashAccount(index)"
                />
                <FormErrorMessages :messages="getItemFieldErrors(`costs.${index}.cash_account_id`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel :class="{ 'text-danger': invalidItemField(`costs.${index}.amount`) }">
                  {{ t('views.purchase_receipt.fields.amount') }}
                </FormLabel>
                <div class="flex items-start gap-2">
                  <div class="min-w-0 flex-1">
                    <FormInputCurrency
                      v-model="cost.amount"
                      :allow-negative="false"
                      :class="{ 'border-danger': invalidItemField(`costs.${index}.amount`) }"
                      @change="purchaseOrderReceiptForm.validate(`costs.${index}.amount` as any)"
                    />
                  </div>
                  <div class="shrink-0">
                    <Button
                      type="button"
                      variant="outline-secondary"
                      class="flex h-[38px] w-[38px] min-w-0 items-center justify-center"
                      @click="removeCost(index)"
                    >
                      <Lucide icon="Trash2" class="h-4 w-4 text-danger" />
                    </Button>
                  </div>
                </div>
                <FormErrorMessages :messages="getItemFieldErrors(`costs.${index}.amount`)" />
              </div>

              <div class="col-span-12">
                <FormLabel :class="{ 'text-danger': invalidItemField(`costs.${index}.remarks`) }">
                  {{ t('views.purchase_receipt.fields.remarks') }}
                </FormLabel>
                <FormTextarea
                  v-model="cost.remarks"
                  :class="{ 'border-danger': invalidItemField(`costs.${index}.remarks`) }"
                  :placeholder="t('views.purchase_receipt.fields.remarks')"
                  @change="purchaseOrderReceiptForm.validate(`costs.${index}.remarks` as any)"
                />
                <FormErrorMessages :messages="getItemFieldErrors(`costs.${index}.remarks`)" />
              </div>
            </div>
          </div>

          <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-9"></div>
            <div class="col-span-12 lg:col-span-3">
              <FormLabel>{{ t('views.purchase_receipt.fields.total_cost') }}</FormLabel>
              <FormInputCurrency :model-value="getCostsTotalPreview()" readonly />
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
            :disabled="purchaseOrderReceiptForm.validating || purchaseOrderReceiptForm.hasErrors"
          >
            <Lucide v-if="purchaseOrderReceiptForm.validating" icon="Loader" class="mr-2 h-4 w-4 animate-spin" />
            <Lucide v-else icon="Save" class="mr-2 h-4 w-4" />
            {{ t('components.buttons.save') }}
          </Button>
        </div>
      </template>
    </TwoColumnsLayout>
  </form>
</template>
