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
  FormInputCurrency,
  FormInputDateTimeAuto,
  FormLabel,
  FormSelectSearch,
  FormSwitch,
  FormTextarea,
} from '@/components/Base/Form';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import ProductUnitPickerDialog from '@/components/Product/ProductUnitPickerDialog.vue';
import CashAccountService from '@/services/CashAccountService';
import CustomerService from '@/services/CustomerService';
import ProductService from '@/services/ProductService';
import SalesOrderDeliveryService from '@/services/SalesOrderDeliveryService';
import SalesOrderService from '@/services/SalesOrderService';
import WarehouseService from '@/services/WarehouseService';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { NotificationData } from '@/types/models/NotificationData';
import type { Product } from '@/types/models/Product';
import type { ProductUnit } from '@/types/models/ProductUnit';
import type { SalesOrder } from '@/types/models/SalesOrder';
import type { SalesOrderItem } from '@/types/models/SalesOrderItem';
import type { SalesOrderDelivery } from '@/types/models/SalesOrderDelivery';
import type {
  SalesOrderDeliveryCostNestedUpdateRequest,
  SalesOrderDeliveryItemNestedUpdateRequest,
} from '@/types/services/sales-order-delivery/SalesOrderDeliveryRequest';
// #endregion

// #region Declarations
type SalesOrderOption = DropDownOption & {
  ulid: string;
  customer_id: string | null;
  customer_name: string | null;
};

type SalesOrderDeliveryItemFormItem = SalesOrderDeliveryItemNestedUpdateRequest & {
  has_sales_order_item_product: boolean;
  product_unit_product_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_unit_name?: string | null;
  product_unit_base_unit_name?: string | null;
  product_unit_product_image_url?: string | null;
  sales_order_item_label?: string | null;
  is_use_serial_number?: boolean;
  base_unit_cogs?: number | null;
  total_cogs?: number | null;
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
const salesOrderDeliveryService = new SalesOrderDeliveryService();
const salesOrderService = new SalesOrderService();
const customerService = new CustomerService();
const warehouseService = new WarehouseService();
const cashAccountService = new CashAccountService();
const productService = new ProductService();

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const salesOrderDeliveryForm: any = salesOrderDeliveryService.useSalesOrderDeliveryEditForm(
  route.params.ulid as string,
);

const salesOrderDeliveryData = ref<SalesOrderDelivery | null>(null);
const selectedSalesOrderData = ref<SalesOrder | null>(null);
const initializing = ref<boolean>(true);
const isSearchingProductUnit = ref<boolean>(false);
const showProductUnitModal = ref<boolean>(false);
const editingItemIndex = ref<number | null>(null);
const productSearchText = ref<string>('');
const customerSearch = ref<string>('');
const salesOrderSearch = ref<string>('');
const warehouseSearch = ref<string>('');
const cashAccountSearch = ref<string>('');

const customerDDL = ref<Array<DropDownOption>>([]);
const salesOrderDDL = ref<Array<SalesOrderOption>>([]);
const warehouseDDL = ref<Array<DropDownOption>>([]);
const cashAccountDDL = ref<Array<DropDownOption>>([]);
const productUnitOptions = ref<Array<ProductUnitOption>>([]);

const cards = ref<Array<TwoColumnsLayoutCards>>([
  {
    title: 'views.sales_order_delivery.field_groups.company_info',
    state: CardState.Expanded,
  },
  {
    title: 'views.sales_order_delivery.field_groups.sales_order_delivery_data',
    state: CardState.Expanded,
  },
  {
    title: 'views.sales_order_delivery.field_groups.items',
    state: CardState.Expanded,
  },
  {
    title: 'views.sales_order_delivery.field_groups.costs',
    state: CardState.Expanded,
  },
  {
    title: 'views.sales_order_delivery.field_groups.summary',
    state: CardState.Expanded,
  },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const customerOptions = computed(() =>
  customerDDL.value.map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const salesOrderOptions = computed(() =>
  salesOrderDDL.value.map((item) => ({
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
    label: t('views.sales_order_delivery.fields.product_unit_conversion_value'),
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

  emits('loading-state', true);
  try {
    const loaded = await loadData();
    if (!loaded) {
      return;
    }

    await Promise.all([loadCustomerDDL(), loadSalesOrderDDL(), loadWarehouseDDL(), loadCashAccountDDL()]);
    initializing.value = false;
  } finally {
    emits('loading-state', false);
  }
});
// #endregion

// #region Methods - Helpers
const getItems = () => salesOrderDeliveryForm.items as SalesOrderDeliveryItemFormItem[];

const getCosts = () => salesOrderDeliveryForm.costs as SalesOrderDeliveryCostNestedUpdateRequest[];

const getItemFieldErrors = (field: string) => (salesOrderDeliveryForm.errors as any)[field];

const invalidField = (field: string) => salesOrderDeliveryForm.invalid(field);

const invalidItemField = (field: string) => salesOrderDeliveryForm.invalid(field as any);

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
  Object.keys(salesOrderDeliveryForm.errors).forEach((key) => {
    if (key.startsWith('items.')) {
      salesOrderDeliveryForm.forgetError(key as any);
    }
  });
};

const clearCostErrors = () => {
  Object.keys(salesOrderDeliveryForm.errors).forEach((key) => {
    if (key.startsWith('costs.')) {
      salesOrderDeliveryForm.forgetError(key as any);
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

const appendSalesOrderOption = (salesOrder: SalesOrder | null | undefined) => {
  if (!salesOrder?.id || !salesOrder.ulid) return;

  if (!salesOrderDDL.value.some((item) => item.code === salesOrder.id)) {
    salesOrderDDL.value = [
      ...salesOrderDDL.value,
      {
        code: salesOrder.id,
        name: `${salesOrder.code} - ${salesOrder.customer?.name ?? '-'}`,
        ulid: salesOrder.ulid,
        customer_id: salesOrder.customer?.id ?? null,
        customer_name: salesOrder.customer?.name ?? null,
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

const formatSalesOrderItemLabel = (salesOrderItem: SalesOrderItem | null | undefined) => {
  if (!salesOrderItem) return null;

  const productUnit = salesOrderItem.product_unit;
  const productCode = productUnit?.code ?? '';
  const productName = productUnit?.product?.name ?? '-';

  return productCode ? `[${productCode}] ${productName}` : productName;
};

const formatSalesOrderItemLabelFromProductUnit = (productUnit: ProductUnit | null | undefined) => {
  const productCode = productUnit?.code ?? '';
  const productName = productUnit?.product?.name ?? '-';

  return productCode ? `[${productCode}] ${productName}` : productName;
};

const buildManualItemFromProductUnit = (option: ProductUnitOption): SalesOrderDeliveryItemFormItem => ({
  id: null,
  has_sales_order_item_product: false,
  qty: 1,
  product_unit_id: option.product_unit_id,
  product_unit_conversion_value: option.conversion_value,
  remarks: '',
  delete_serial_ids: [],
  serials: [],
  product_unit_product_code: option.product_unit_code,
  product_unit_product_name: option.product_name,
  product_unit_unit_name: option.unit_name,
  product_unit_base_unit_name: option.base_unit_name,
  product_unit_product_image_url: option.product_image_url ?? null,
  sales_order_item_label: null,
  is_use_serial_number: option.is_use_serial_number,
  base_unit_cogs: null,
  total_cogs: null,
});

const buildDeliveryItemFromSalesOrderItem = (salesOrderItem: SalesOrderItem): SalesOrderDeliveryItemFormItem => {
  const productUnit = salesOrderItem.product_unit;
  const product = productUnit?.product;
  const conversionValue = Number(salesOrderItem.product_unit_conversion_value ?? productUnit?.conversion_value ?? 1);
  const outstandingBase = Number(salesOrderItem.qty_outstanding_base ?? 0);
  const defaultQty = outstandingBase > 0
    ? outstandingBase / Math.max(conversionValue, 1)
    : Number(salesOrderItem.qty ?? 0);

  return {
    id: null,
    has_sales_order_item_product: true,
    qty: defaultQty > 0 ? defaultQty : 1,
    product_unit_id: productUnit?.id ?? '',
    product_unit_conversion_value: conversionValue,
    remarks: salesOrderItem.remarks ?? '',
    delete_serial_ids: [],
    serials: [],
    product_unit_product_code: productUnit?.code ?? '',
    product_unit_product_name: product?.name ?? '-',
    product_unit_unit_name: productUnit?.unit?.name ?? '',
    product_unit_base_unit_name: buildBaseUnitName(product, Number(productUnit?.conversion_value ?? conversionValue)),
    product_unit_product_image_url: product?.main_product_image?.url ?? null,
    sales_order_item_label: formatSalesOrderItemLabel(salesOrderItem),
    is_use_serial_number: Boolean(product?.is_use_serial_number),
    base_unit_cogs: null,
    total_cogs: null,
  };
};

const buildFormItemFromDeliveryItem = (
  delivery: SalesOrderDelivery,
  item: any,
): SalesOrderDeliveryItemFormItem => {
  const productUnit: ProductUnit | null | undefined = item.product_unit;
  const product = productUnit?.product;
  const conversionValue = Number(item.product_unit_conversion_value ?? productUnit?.conversion_value ?? 1);

  return {
    id: item.id ?? null,
    has_sales_order_item_product: Boolean(item.has_sales_order_item_product),
    qty: Number(item.qty ?? 0),
    product_unit_id: productUnit?.id ?? '',
    product_unit_conversion_value: conversionValue,
    remarks: item.remarks ?? '',
    delete_serial_ids: [],
    serials: (item.serials ?? []).map((serial: any) => ({
      id: serial.id ?? null,
      serial: serial.serial,
    })),
    product_unit_product_code: productUnit?.code ?? '',
    product_unit_product_name: product?.name ?? '-',
    product_unit_unit_name: productUnit?.unit?.name ?? '',
    product_unit_base_unit_name: buildBaseUnitName(product, Number(productUnit?.conversion_value ?? conversionValue)),
    product_unit_product_image_url: product?.main_product_image?.url ?? null,
    sales_order_item_label:
      delivery.sales_order && item.has_sales_order_item_product
        ? formatSalesOrderItemLabelFromProductUnit(productUnit)
        : null,
    is_use_serial_number: Boolean(product?.is_use_serial_number),
    base_unit_cogs: Number(item.base_unit_cogs ?? 0),
    total_cogs: Number(item.total_cogs ?? 0),
  };
};

const getTotalCostPreview = () =>
  getCosts().reduce((total, cost) => total + Math.max(Number(cost.amount ?? 0), 0), 0);
// #endregion

// #region Methods - Sales Order Delivery
const loadCustomerDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const includeId =
    salesOrderDeliveryData.value?.customer?.id ??
    (salesOrderDeliveryForm.customer_id as string | null | undefined) ??
    undefined;

  const result = await customerService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    status: undefined,
    include_id: includeId,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    customerDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }

  appendDropDownOption(customerDDL, salesOrderDeliveryData.value?.customer
    ? {
      code: salesOrderDeliveryData.value.customer.id,
      name: salesOrderDeliveryData.value.customer.name,
    }
    : null);
};

const loadSalesOrderDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await salesOrderService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: null,
    end_date: null,
    customer_id: (salesOrderDeliveryForm.customer_id as string | null) ?? null,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    salesOrderDDL.value = result.data.data
      .filter((item) => Boolean(item.customer?.id))
      .map((item) => ({
        code: item.id,
        name: `${item.code} - ${item.customer?.name ?? '-'}`,
        ulid: item.ulid,
        customer_id: item.customer?.id ?? null,
        customer_name: item.customer?.name ?? null,
      }));
  } else {
    salesOrderDDL.value = [];
  }

  appendSalesOrderOption(salesOrderDeliveryData.value?.sales_order);
  appendSalesOrderOption(selectedSalesOrderData.value);
};

const loadWarehouseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const includeId =
    salesOrderDeliveryData.value?.warehouse?.id ??
    (salesOrderDeliveryForm.warehouse_id as string | null | undefined) ??
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

  appendDropDownOption(warehouseDDL, salesOrderDeliveryData.value?.warehouse
    ? {
      code: salesOrderDeliveryData.value.warehouse.id,
      name: salesOrderDeliveryData.value.warehouse.name,
    }
    : null);
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

  (salesOrderDeliveryData.value?.costs ?? []).forEach((cost) => appendDropDownOption(
    cashAccountDDL,
    cost.cash_account ? { code: cost.cash_account.id, name: cost.cash_account.name } : null,
  ));
};

const setCode = () => {
  salesOrderDeliveryForm.forgetError('code');

  if (salesOrderDeliveryForm.code === '_AUTO_') {
    salesOrderDeliveryForm.setData({ code: '' });
    return;
  }

  salesOrderDeliveryForm.setData({ code: '_AUTO_' });
};

const setCostCode = (index: number) => {
  salesOrderDeliveryForm.forgetError(`costs.${index}.code` as any);
  getCosts()[index].code = getCosts()[index].code === '_AUTO_' ? '' : '_AUTO_';
};

const loadSalesOrderDetail = async (salesOrderUlid: string) => {
  const result = await salesOrderService.read(salesOrderUlid);

  if (result.success && result.data) {
    selectedSalesOrderData.value = result.data;
    appendSalesOrderOption(result.data);

    if (result.data.customer) {
      appendDropDownOption(customerDDL, {
        code: result.data.customer.id,
        name: result.data.customer.name,
      });
    }

    return result.data;
  }

  showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  return null;
};

const syncItemsFromSalesOrder = (salesOrder: SalesOrder) => {
  getItems().forEach((item) => {
    if (item.id) {
      salesOrderDeliveryForm.delete_item_ids.push(item.id);
    }
  });

  salesOrderDeliveryForm.setData({
    customer_id: salesOrder.customer?.id ?? null,
    items: (salesOrder.items ?? []).map((item) => buildDeliveryItemFromSalesOrderItem(item)) as any,
  });

  clearItemErrors();
};

const clearSalesOrder = async () => {
  selectedSalesOrderData.value = null;
  salesOrderDeliveryForm.setData({ sales_order_id: null });
  salesOrderDeliveryForm.forgetError('sales_order_id');
  await loadSalesOrderDDL();
};

const clearCustomer = async () => {
  salesOrderDeliveryForm.setData({
    customer_id: null,
    sales_order_id: null,
  });
  selectedSalesOrderData.value = null;
  salesOrderDeliveryForm.forgetError('customer_id');
  salesOrderDeliveryForm.forgetError('sales_order_id');
  await loadSalesOrderDDL();
};

const clearWarehouse = () => {
  salesOrderDeliveryForm.setData({ warehouse_id: null });
  salesOrderDeliveryForm.forgetError('warehouse_id');
};

const clearCostCashAccount = (index: number) => {
  const cost = getCosts()[index];
  if (!cost) return;
  cost.cash_account_id = '';
  salesOrderDeliveryForm.validate(`costs.${index}.cash_account_id` as any);
};

const handleCustomerChanged = async () => {
  salesOrderDeliveryForm.validate('customer_id');

  if (salesOrderDeliveryForm.sales_order_id) {
    return;
  }

  await loadSalesOrderDDL();
};

const handleSalesOrderChanged = async (salesOrderId: string | number | null) => {
  salesOrderDeliveryForm.validate('sales_order_id');

  if (!salesOrderId) {
    await clearSalesOrder();
    return;
  }

  const option = salesOrderDDL.value.find((item) => item.code === salesOrderId);
  if (!option) {
    return;
  }

  const salesOrder = await loadSalesOrderDetail(option.ulid);
  if (!salesOrder) {
    return;
  }

  syncItemsFromSalesOrder(salesOrder);
};

const reloadItemsFromSalesOrder = async () => {
  if (!salesOrderDeliveryForm.sales_order_id) {
    return;
  }

  const option = salesOrderDDL.value.find((item) => item.code === salesOrderDeliveryForm.sales_order_id);
  if (!option) {
    return;
  }

  const salesOrder = await loadSalesOrderDetail(option.ulid);
  if (!salesOrder) {
    return;
  }

  syncItemsFromSalesOrder(salesOrder);
};

const loadData = async () => {
  const result = await salesOrderDeliveryService.read(route.params.ulid as string);

  if (!result.success || !result.data) {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
    return false;
  }

  const data = result.data;
  salesOrderDeliveryData.value = data;

  salesOrderDeliveryForm.setData({
    customer_id: data.customer?.id ?? null,
    sales_order_id: data.sales_order?.id ?? null,
    code: data.code,
    date: formatDate(data.date, 'YYYY-MM-DD HH:mm:ss'),
    warehouse_id: data.warehouse?.id ?? null,
    remarks: data.remarks ?? '',
    is_posted: data.is_posted,
    delete_item_ids: [],
    items: (data.items ?? []).map((item: any) => buildFormItemFromDeliveryItem(data, item)) as any,
    delete_cost_ids: [],
    costs: (data.costs ?? []).map((cost: any) => ({
      id: cost.id ?? null,
      code: cost.code,
      date: formatDate(cost.date, 'YYYY-MM-DD HH:mm:ss'),
      name: cost.name ?? '',
      cash_account_id: cost.cash_account?.id ?? '',
      amount: cost.amount,
      remarks: cost.remarks ?? '',
    })) as any,
  });

  appendSalesOrderOption(data.sales_order);

  if (data.sales_order?.ulid) {
    await loadSalesOrderDetail(data.sales_order.ulid);
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
    salesOrderDeliveryForm.items.push(baseData as any);
  } else {
    const currentItem = items[editingItemIndex.value];

    salesOrderDeliveryForm.items[editingItemIndex.value] = {
      ...currentItem,
      ...baseData,
      id: currentItem?.id ?? null,
      qty: currentItem?.qty ?? 1,
      remarks: currentItem?.remarks ?? '',
      delete_serial_ids: currentItem?.delete_serial_ids ?? [],
      serials: baseData.is_use_serial_number ? serials : [],
      base_unit_cogs: currentItem?.base_unit_cogs ?? null,
      total_cogs: currentItem?.total_cogs ?? null,
    };
  }

  clearItemErrors();
  showProductUnitModal.value = false;
  editingItemIndex.value = null;
};

const removeItem = (index: number) => {
  const items = getItems();
  const item = items[index];

  if (item?.id) {
    salesOrderDeliveryForm.delete_item_ids.push(item.id);
  }

  items.splice(index, 1);
  clearItemErrors();
};

const addSerial = (index: number) => {
  const item = getItems()[index];
  if (!item) return;

  item.serials.push({ id: null, serial: '' });
  salesOrderDeliveryForm.validate(`items.${index}.serials` as any);
};

const removeSerial = (index: number, serialIndex: number) => {
  const item = getItems()[index];
  if (!item) return;

  const serial = item.serials[serialIndex];
  if (serial?.id) {
    item.delete_serial_ids.push(serial.id);
  }

  item.serials.splice(serialIndex, 1);
  salesOrderDeliveryForm.validate(`items.${index}.serials` as any);
};
// #endregion

// #region Methods - Costs
const addCost = () => {
  getCosts().push({
    id: null,
    code: '_AUTO_',
    date: '_AUTO_',
    name: '',
    cash_account_id: '',
    amount: 0,
    remarks: '',
  });
};

const removeCost = (index: number) => {
  const costs = getCosts();
  const cost = costs[index];

  if (cost?.id) {
    salesOrderDeliveryForm.delete_cost_ids.push(cost.id);
  }

  costs.splice(index, 1);
  clearCostErrors();
};
// #endregion

// #region Actions
const onSubmit = async () => {
  if (salesOrderDeliveryForm.hasErrors) {
    const firstErrorKey = Object.keys(salesOrderDeliveryForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  const originalItems = getItems();
  const originalCosts = getCosts();
  const backupItems = [...originalItems];
  const backupCosts = [...originalCosts];

  const cleanedItems: SalesOrderDeliveryItemNestedUpdateRequest[] = originalItems.map((item) => ({
    id: item.id,
    qty: Number(item.qty ?? 0),
    product_unit_id: item.product_unit_id,
    product_unit_conversion_value: Number(item.product_unit_conversion_value ?? 0),
    remarks: item.remarks ?? '',
    delete_serial_ids: item.delete_serial_ids,
    serials: item.serials.map((serial) => ({
      id: serial.id,
      serial: serial.serial,
    })),
  }));

  const cleanedCosts: SalesOrderDeliveryCostNestedUpdateRequest[] = originalCosts.map((cost) => ({
    id: cost.id,
    code: cost.code,
    date: cost.date,
    name: cost.name,
    cash_account_id: cost.cash_account_id,
    amount: Number(cost.amount ?? 0),
    remarks: cost.remarks ?? '',
  }));

  salesOrderDeliveryForm.items = cleanedItems as any;
  salesOrderDeliveryForm.costs = cleanedCosts as any;
  emits('loading-state', true);

  try {
    await salesOrderDeliveryForm.submit();
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(
      t('views.sales_order_delivery.alert.update.title'),
      t('views.sales_order_delivery.alert.update.message'),
    );
    router.push({ name: 'side-menu-sales-delivery-list' });
  } catch (error) {
    salesOrderDeliveryForm.items = backupItems as any;
    salesOrderDeliveryForm.costs = backupCosts as any;
    showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
  } finally {
    emits('loading-state', false);
  }
};
// #endregion
</script>

<template>
  <form v-if="selectedUserLocation && !initializing" id="salesOrderDeliveryForm" @submit.prevent="onSubmit">
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
                {{ t('views.sales_order_delivery.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="salesOrderDeliveryForm.code"
                :class="{ 'border-danger': invalidField('code') }"
                :placeholder="t('views.sales_order_delivery.fields.code')"
                @set-auto="setCode"
                @change="salesOrderDeliveryForm.validate('code')"
              />
              <FormErrorMessages :messages="salesOrderDeliveryForm.errors.code" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('date') }">
                {{ t('views.sales_order_delivery.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="salesOrderDeliveryForm.date"
                :class="{ 'border-danger': invalidField('date') }"
                :placeholder="t('views.sales_order_delivery.fields.date')"
                @change="salesOrderDeliveryForm.validate('date')"
              />
              <FormErrorMessages :messages="salesOrderDeliveryForm.errors.date" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('customer_id') }">
                {{ t('views.sales_order_delivery.fields.customer_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="salesOrderDeliveryForm.customer_id"
                v-model:search="customerSearch"
                :options="customerOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :disabled="Boolean(salesOrderDeliveryForm.sales_order_id)"
                :class="{ 'border-danger': invalidField('customer_id') }"
                @change="handleCustomerChanged"
                @search="loadCustomerDDL"
                @clear="clearCustomer"
              />
              <FormErrorMessages :messages="salesOrderDeliveryForm.errors.customer_id" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('sales_order_id') }">
                {{ t('views.sales_order_delivery.fields.sales_order_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="salesOrderDeliveryForm.sales_order_id"
                v-model:search="salesOrderSearch"
                :options="salesOrderOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': invalidField('sales_order_id') }"
                @change="(value) => handleSalesOrderChanged(value as string | number | null)"
                @search="loadSalesOrderDDL"
                @clear="clearSalesOrder"
              />
              <FormErrorMessages :messages="salesOrderDeliveryForm.errors.sales_order_id" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('warehouse_id') }">
                {{ t('views.sales_order_delivery.fields.warehouse_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="salesOrderDeliveryForm.warehouse_id"
                v-model:search="warehouseSearch"
                :options="warehouseOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': invalidField('warehouse_id') }"
                @change="salesOrderDeliveryForm.validate('warehouse_id')"
                @search="loadWarehouseDDL"
                @clear="clearWarehouse"
              />
              <FormErrorMessages :messages="salesOrderDeliveryForm.errors.warehouse_id" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4 flex flex-col justify-center">
              <FormLabel :class="{ 'text-danger': invalidField('is_posted') }">
                {{ t('views.sales_order_delivery.fields.is_posted') }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input v-model="salesOrderDeliveryForm.is_posted" type="checkbox" />
              </FormSwitch>
              <FormErrorMessages :messages="salesOrderDeliveryForm.errors.is_posted" />
            </div>
          </div>

          <div v-if="salesOrderDeliveryForm.sales_order_id" class="mt-4 rounded-md border border-primary/20 bg-primary/5 px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
            {{ t('views.sales_order_delivery.fields.sales_order_link_hint') }}
          </div>

          <div class="mt-4">
            <FormLabel :class="{ 'text-danger': invalidField('remarks') }">
              {{ t('views.sales_order_delivery.fields.remarks') }}
            </FormLabel>
            <FormTextarea
              v-model="salesOrderDeliveryForm.remarks"
              :class="{ 'border-danger': invalidField('remarks') }"
              :placeholder="t('views.sales_order_delivery.fields.remarks')"
              @change="salesOrderDeliveryForm.validate('remarks')"
            />
            <FormErrorMessages :messages="salesOrderDeliveryForm.errors.remarks" />
          </div>
        </div>
      </template>

      <template #card-items-2>
        <div class="p-5 space-y-4">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="text-sm text-slate-500">
              {{ t('views.sales_order_delivery.fields.item_count') }}: {{ getItems().length }}
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <Button
                v-if="salesOrderDeliveryForm.sales_order_id"
                type="button"
                variant="outline-primary"
                @click="reloadItemsFromSalesOrder"
              >
                <Lucide icon="RefreshCw" class="mr-2 h-4 w-4" />
                {{ t('components.buttons.reload') }}
              </Button>
              <Button type="button" variant="outline-primary" @click="openAddProductUnit">
                <Lucide icon="Plus" class="mr-2 h-4 w-4" />
                {{ t('components.buttons.add') }}
              </Button>
            </div>
          </div>

          <div class="rounded-md border border-slate-200/70 bg-slate-50 px-4 py-3 text-xs text-slate-600 dark:border-darkmode-400 dark:bg-darkmode-600/40 dark:text-slate-300">
            {{ t('views.sales_order_delivery.fields.cogs_readonly_hint') }}
          </div>

          <FormErrorMessages :messages="salesOrderDeliveryForm.errors.items" />

          <div v-if="getItems().length === 0" class="rounded-md border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500 dark:border-darkmode-400">
            {{ t('views.sales_order_delivery.fields.items_empty') }}
          </div>

          <div
            v-for="(item, index) in getItems()"
            :key="`${item.has_sales_order_item_product ? 'sales-order' : 'manual'}-${item.product_unit_id ?? 'item'}-${index}`"
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
                <div v-if="item.sales_order_item_label" class="mt-1 text-xs text-primary">
                  {{ item.sales_order_item_label }}
                </div>
              </div>
              <div class="flex items-center gap-2">
                <Button type="button" size="sm" variant="outline-primary" @click="openChangeProductUnit(index)">
                  <Lucide icon="Pencil" class="h-4 w-4" />
                </Button>
                <Button type="button" size="sm" variant="outline-secondary" @click="removeItem(index)">
                  <Lucide icon="Trash2" class="h-4 w-4 text-danger" />
                </Button>
              </div>
            </div>

            <div class="mt-4 grid grid-cols-12 gap-4 gap-y-3">
              <div class="col-span-12 lg:col-span-4">
                <FormLabel :class="{ 'text-danger': invalidItemField(`items.${index}.product_unit_id`) }">
                  {{ t('views.sales_order_delivery.fields.product_unit_id') }}
                </FormLabel>
                <FormInput
                  :model-value="item.product_unit_product_code ? `[${item.product_unit_product_code}] ${item.product_unit_product_name}` : item.product_unit_product_name"
                  readonly
                />
                <FormErrorMessages :messages="getItemFieldErrors(`items.${index}.product_unit_id`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel :class="{ 'text-danger': invalidItemField(`items.${index}.qty`) }">
                  {{ t('views.sales_order_delivery.fields.qty') }}
                </FormLabel>
                <FormInput
                  :id="`items.${index}.qty`"
                  v-model="item.qty"
                  type="number"
                  min="0"
                  step="any"
                  :class="{ 'border-danger': invalidItemField(`items.${index}.qty`) }"
                  @change="salesOrderDeliveryForm.validate(`items.${index}.qty` as any)"
                />
                <FormErrorMessages :messages="getItemFieldErrors(`items.${index}.qty`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel :class="{ 'text-danger': invalidItemField(`items.${index}.product_unit_conversion_value`) }">
                  {{ t('views.sales_order_delivery.fields.product_unit_conversion_value') }}
                </FormLabel>
                <FormInput
                  v-model="item.product_unit_conversion_value"
                  readonly
                  :class="{ 'border-danger': invalidItemField(`items.${index}.product_unit_conversion_value`) }"
                />
                <FormErrorMessages :messages="getItemFieldErrors(`items.${index}.product_unit_conversion_value`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel>{{ t('views.sales_order_delivery.fields.base_unit_cogs') }}</FormLabel>
                <FormInputCurrency :model-value="Number(item.base_unit_cogs ?? 0)" readonly />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel>{{ t('views.sales_order_delivery.fields.total_cogs') }}</FormLabel>
                <FormInputCurrency :model-value="Number(item.total_cogs ?? 0)" readonly />
              </div>

              <div class="col-span-12">
                <FormLabel :class="{ 'text-danger': invalidItemField(`items.${index}.remarks`) }">
                  {{ t('views.sales_order_delivery.fields.remarks') }}
                </FormLabel>
                <FormTextarea
                  v-model="item.remarks"
                  :class="{ 'border-danger': invalidItemField(`items.${index}.remarks`) }"
                  :placeholder="t('views.sales_order_delivery.fields.remarks')"
                  @change="salesOrderDeliveryForm.validate(`items.${index}.remarks` as any)"
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
                        salesOrderDeliveryForm.validate(`items.${index}.serials.${serialIndex}.serial` as any);
                        salesOrderDeliveryForm.validate(`items.${index}.serials` as any);
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

      <template #card-items-3>
        <div class="p-5 space-y-4">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="text-sm text-slate-500">
              {{ t('views.sales_order_delivery.fields.cost_count') }}: {{ getCosts().length }}
            </div>
            <Button type="button" variant="outline-primary" @click="addCost">
              <Lucide icon="Plus" class="mr-2 h-4 w-4" />
              {{ t('components.buttons.add') }}
            </Button>
          </div>

          <FormErrorMessages :messages="salesOrderDeliveryForm.errors.costs" />

          <div v-if="getCosts().length === 0" class="rounded-md border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500 dark:border-darkmode-400">
            {{ t('views.sales_order_delivery.fields.costs_empty') }}
          </div>

          <div
            v-for="(cost, index) in getCosts()"
            :key="`cost-${index}`"
            class="rounded-md border border-slate-200/70 p-4 dark:border-darkmode-400"
          >
            <div class="grid grid-cols-12 gap-4 gap-y-3">
              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel :class="{ 'text-danger': invalidItemField(`costs.${index}.code`) }">
                  {{ t('views.sales_order_delivery.fields.code') }}
                </FormLabel>
                <FormInputCode
                  v-model="cost.code"
                  :class="{ 'border-danger': invalidItemField(`costs.${index}.code`) }"
                  :placeholder="t('views.sales_order_delivery.fields.code')"
                  @set-auto="setCostCode(index)"
                  @change="salesOrderDeliveryForm.validate(`costs.${index}.code` as any)"
                />
                <FormErrorMessages :messages="getItemFieldErrors(`costs.${index}.code`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-3">
                <FormLabel :class="{ 'text-danger': invalidItemField(`costs.${index}.date`) }">
                  {{ t('views.sales_order_delivery.fields.date') }}
                </FormLabel>
                <FormInputDateTimeAuto
                  v-model="cost.date"
                  :class="{ 'border-danger': invalidItemField(`costs.${index}.date`) }"
                  :placeholder="t('views.sales_order_delivery.fields.date')"
                  @change="salesOrderDeliveryForm.validate(`costs.${index}.date` as any)"
                />
                <FormErrorMessages :messages="getItemFieldErrors(`costs.${index}.date`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-3">
                <FormLabel :class="{ 'text-danger': invalidItemField(`costs.${index}.name`) }">
                  {{ t('views.sales_order_delivery.fields.name') }}
                </FormLabel>
                <FormInput
                  v-model="cost.name"
                  :class="{ 'border-danger': invalidItemField(`costs.${index}.name`) }"
                  :placeholder="t('views.sales_order_delivery.fields.name')"
                  @change="salesOrderDeliveryForm.validate(`costs.${index}.name` as any)"
                />
                <FormErrorMessages :messages="getItemFieldErrors(`costs.${index}.name`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel :class="{ 'text-danger': invalidItemField(`costs.${index}.cash_account_id`) }">
                  {{ t('views.sales_order_delivery.fields.cash_account_id') }}
                </FormLabel>
                <FormSelectSearch
                  v-model="cost.cash_account_id"
                  v-model:search="cashAccountSearch"
                  :options="cashAccountOptions"
                  :placeholder="t('components.dropdown.placeholder')"
                  :class="{ 'border-danger': invalidItemField(`costs.${index}.cash_account_id`) }"
                  @change="salesOrderDeliveryForm.validate(`costs.${index}.cash_account_id` as any)"
                  @search="loadCashAccountDDL"
                  @clear="clearCostCashAccount(index)"
                />
                <FormErrorMessages :messages="getItemFieldErrors(`costs.${index}.cash_account_id`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel :class="{ 'text-danger': invalidItemField(`costs.${index}.amount`) }">
                  {{ t('views.sales_order_delivery.fields.amount') }}
                </FormLabel>
                <div class="flex items-start gap-2">
                  <div class="flex-1 min-w-0">
                    <FormInputCurrency
                      v-model="cost.amount"
                      :allow-negative="false"
                      :class="{ 'border-danger': invalidItemField(`costs.${index}.amount`) }"
                      @change="salesOrderDeliveryForm.validate(`costs.${index}.amount` as any)"
                    />
                  </div>
                  <div class="shrink-0">
                    <Button
                      type="button"
                      variant="outline-secondary"
                      class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
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
                  {{ t('views.sales_order_delivery.fields.remarks') }}
                </FormLabel>
                <FormTextarea
                  v-model="cost.remarks"
                  :class="{ 'border-danger': invalidItemField(`costs.${index}.remarks`) }"
                  @change="salesOrderDeliveryForm.validate(`costs.${index}.remarks` as any)"
                />
                <FormErrorMessages :messages="getItemFieldErrors(`costs.${index}.remarks`)" />
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #card-items-4>
        <div class="p-5 space-y-4">
          <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-4">
            <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
              <!-- summary spacer: keeps totals aligned to the right on desktop -->
              <div class="col-span-12 lg:col-span-9"></div>
              <div class="col-span-12 lg:col-span-3">
                <FormLabel>{{ t('views.sales_order_delivery.fields.total_cogs') }}</FormLabel>
                <FormInputCurrency :model-value="Number(salesOrderDeliveryData?.total_cogs ?? 0)" readonly />
              </div>
            </div>

            <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
              <div class="col-span-12 lg:col-span-9"></div>
              <div class="col-span-12 lg:col-span-3">
                <FormLabel>{{ t('views.sales_order_delivery.fields.total_cost') }}</FormLabel>
                <FormInputCurrency :model-value="getTotalCostPreview()" readonly />
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
            :disabled="salesOrderDeliveryForm.validating || salesOrderDeliveryForm.hasErrors"
          >
            <Lucide v-if="salesOrderDeliveryForm.validating" icon="Loader" class="mr-2 h-4 w-4 animate-spin" />
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
    :title="t('views.sales_order_delivery.fields.product_unit_id')"
    :is-searching="isSearchingProductUnit"
    :options="productUnitOptions"
    :columns="productUnitDialogColumns"
    @close="showProductUnitModal = false"
    @search="searchProductUnits"
    @select="selectProductUnit($event as ProductUnitOption)"
  />
</template>
