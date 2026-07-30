<script setup lang="ts">
// #region Imports
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import { CardState } from '@/types/enums/CardState';
import { ViewMode } from '@/types/enums/ViewMode';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import {
  FormInput,
  FormInputCode,
  FormInputCurrency,
  FormInputDateTimeAuto,
  FormLabel,
  FormErrorMessages,
  FormSelectSearch,
  FormSwitch,
  FormTextarea,
} from '@/components/Base/Form';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import ProductImagePreview from '@/components/Product/ProductImagePreview.vue';
import ProductUnitPickerDialog from '@/components/Product/ProductUnitPickerDialog.vue';
import CacheService from '@/services/CacheService';
import CashAccountService from '@/services/CashAccountService';
import CustomerService from '@/services/CustomerService';
import ProductService from '@/services/ProductService';
import SalesInvoiceService from '@/services/SalesInvoiceService';
import SalesOrderDeliveryService from '@/services/SalesOrderDeliveryService';
import SalesReturnService from '@/services/SalesReturnService';
import VatProfileService from '@/services/VatProfileService';
import WarehouseService from '@/services/WarehouseService';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { DropDownOption } from '@/types/models/DropDownOption';
import { NotificationData } from '@/types/models/NotificationData';
import {
  SalesReturnItemNestedStoreRequest,
  SalesReturnRefundNestedStoreRequest,
} from '@/types/services/sales-return/SalesReturnRequest';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { convertErrorTypeToAlertListType, formatCurrency } from '@/utils/helper';
// #endregion

// #region Declarations
type SalesReturnItemFormItem = SalesReturnItemNestedStoreRequest & {
  product_unit_product_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_product_image_url?: string | null;
  product_unit_unit_name?: string | null;
  product_unit_base_unit_name?: string | null;
  vat_profile_name?: string | null;
  is_use_serial_number?: boolean;
  sales_order_delivery_item_label?: string | null;
  sales_order_delivery_item_qty_base?: number | null;
  base_unit_cogs?: number | null;
  total_cogs?: number | null;
};

type SalesReturnRefundFormItem = SalesReturnRefundNestedStoreRequest;

type ProductUnitOption = {
  product_unit_id: string;
  product_unit_code: string;
  product_name: string;
  product_image_url: string | null;
  unit_name: string;
  base_unit_name: string;
  conversion_value: number;
  price: number;
  product_unit_is_price_include_vat: boolean;
  is_use_serial_number: boolean;
  vat_profile_id: string | null;
  vat_profile_name: string | null;
  vat_rate: number;
  vat_base_numerator: number;
  vat_base_denominator: number;
};

type DeliveryItemOption = {
  sales_order_delivery_item_id: string;
  product_unit_id: string;
  product_unit_code: string;
  product_name: string;
  product_image_url: string | null;
  unit_name: string;
  base_unit_name: string;
  conversion_value: number;
  qty: number;
  delivered_qty_base: number;
  base_unit_cogs: number;
  price: number;
  product_unit_is_price_include_vat: boolean;
  is_use_serial_number: boolean;
  delivery_code: string;
};

type VatProfileOption = {
  code: string;
  name: string;
  vat_rate: number;
  vat_base_numerator: number;
  vat_base_denominator: number;
};

type SalesInvoiceOption = DropDownOption & {
  customer_id: string | null;
  sales_order_id: string | null;
};

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits([
  'mode-state',
  'loading-state',
  'update-profile',
  'show-alertplaceholder',
  'show-notification',
]);

const salesReturnService = new SalesReturnService();
const salesInvoiceService = new SalesInvoiceService();
const customerService = new CustomerService();
const warehouseService = new WarehouseService();
const vatProfileService = new VatProfileService();
const cashAccountService = new CashAccountService();
const productService = new ProductService();
const cacheService = new CacheService();
const salesOrderDeliveryService = new SalesOrderDeliveryService();

const salesReturnForm = salesReturnService.useSalesReturnCreateForm();

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'views.sales_return.field_groups.company_info', state: CardState.Expanded },
  { title: 'views.sales_return.field_groups.sales_return_data', state: CardState.Expanded },
  { title: 'views.sales_return.field_groups.items', state: CardState.Expanded },
  { title: 'views.sales_return.field_groups.summary', state: CardState.Expanded },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const customerDDL = ref<Array<DropDownOption>>([]);
const customerSearch = ref<string>('');
const customerOptions = computed(() =>
  customerDDL.value.map((item) => ({ value: item.code, label: item.name })),
);

const warehouseDDL = ref<Array<DropDownOption>>([]);
const warehouseSearch = ref<string>('');
const warehouseOptions = computed(() =>
  warehouseDDL.value.map((item) => ({ value: item.code, label: item.name })),
);

const salesInvoiceDDL = ref<Array<SalesInvoiceOption>>([]);
const salesInvoiceSearch = ref<string>('');
const salesInvoiceOptions = computed(() =>
  salesInvoiceDDL.value.map((item) => ({ value: item.code, label: item.name })),
);

const vatProfileDDL = ref<Array<VatProfileOption> | null>(null);
const vatProfileSearch = ref<string>('');
const vatProfileOptions = computed(() =>
  (vatProfileDDL.value ?? []).map((item) => ({ value: item.code, label: item.name })),
);

const cashAccountDDL = ref<Array<DropDownOption> | null>(null);
const cashAccountSearch = ref<string>('');
const cashAccountOptions = computed(() =>
  (cashAccountDDL.value ?? []).map((item) => ({ value: item.code, label: item.name })),
);

const showProductUnitModal = ref<boolean>(false);
const productSearchText = ref<string>('');
const isSearchingProductUnit = ref<boolean>(false);
const productUnitOptions = ref<Array<ProductUnitOption>>([]);
const editingProductUnitIndex = ref<number | null>(null);
const productUnitQtyToFocus = ref<number | null>(null);

const showDeliveryItemModal = ref<boolean>(false);
const deliveryItemSearchText = ref<string>('');
const isSearchingDeliveryItem = ref<boolean>(false);
const deliveryItemOptions = ref<Array<DeliveryItemOption>>([]);

const salesReturnItemDetailsExpanded = ref<boolean[]>([]);
const viewportWidth = ref<number>(window.innerWidth);

const isTotalsBreakdownExpanded = ref(false);
const isRefundEditorExpanded = ref(false);

const productUnitDialogColumns = [
  { key: 'unit_name', label: t('views.product.table.cols.unit') },
  {
    key: 'conversion_value',
    label: t('views.sales_return.fields.product_unit_conversion_value'),
    align: 'right' as const,
    formatter: 'number' as const,
  },
  {
    key: 'price',
    label: t('views.sales_return.fields.product_unit_price'),
    align: 'right' as const,
    formatter: 'number' as const,
  },
];

const deliveryItemDialogColumns = [
  { key: 'delivery_code', label: t('views.sales_return.fields.sales_order_delivery_item_label') },
  { key: 'unit_name', label: t('views.product.table.cols.unit') },
  {
    key: 'delivered_qty_base',
    label: t('views.sales_return.fields.delivered_qty_base'),
    align: 'right' as const,
    formatter: 'number' as const,
  },
];

const currentItemLayout = computed<'sm' | 'md' | 'lg'>(() => {
  if (viewportWidth.value >= 1024) return 'lg';
  if (viewportWidth.value >= 768) return 'md';
  return 'sm';
});

const salesReturnItemsForm = computed<SalesReturnItemFormItem[]>(
  () => salesReturnForm.items as SalesReturnItemFormItem[],
);
const salesReturnRefundsForm = computed<SalesReturnRefundFormItem[]>(
  () => salesReturnForm.refunds as SalesReturnRefundFormItem[],
);

// without a linked sales invoice the backend rejects any VAT on return items
const isVatDisabled = computed(() => !salesReturnForm.sales_invoice_id);

const invalidSalesReturnField = (field: string) => salesReturnForm.invalid(field as any);
const validateSalesReturnField = (field: string) => salesReturnForm.validate(field as any);
const getSalesReturnFieldErrors = (field: string) =>
  (salesReturnForm.errors as Record<string, string | undefined>)[field];

const handleExpandCard = (index: number) => {
  cards.value[index].state =
    cards.value[index].state === CardState.Collapsed ? CardState.Expanded : CardState.Collapsed;
};

watch(
  salesReturnForm,
  debounce((newValue) => {
    cacheService.setLastEntity('SALES_RETURN_CREATE', newValue.data());
  }, 500),
  { deep: true },
);

const syncViewportWidth = () => {
  viewportWidth.value = window.innerWidth;
};
// #endregion

// #region Vue Core
onMounted(async () => {
  syncViewportWidth();
  window.addEventListener('resize', syncViewportWidth);
  emits('mode-state', ViewMode.FORM_CREATE);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  loadFromCache();

  salesReturnForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
  });

  await Promise.all([
    loadCustomerDDL(),
    loadWarehouseDDL(),
    loadSalesInvoiceDDL(),
    loadVatProfileDDL(),
    loadCashAccountDDL(),
  ]);
});

onUnmounted(() => {
  window.removeEventListener('resize', syncViewportWidth);
});
// #endregion

// #region Methods - Helpers
const loadFromCache = () => {
  const data = cacheService.getLastEntity('SALES_RETURN_CREATE') as Record<string, unknown>;
  if (!data) return;
  salesReturnForm.setData(data);
  salesReturnItemDetailsExpanded.value = salesReturnItemsForm.value.map(() => false);
};

const setCode = () => {
  salesReturnForm.forgetError('code');
  salesReturnForm.setData({
    code: salesReturnForm.code === '_AUTO_' ? '' : '_AUTO_',
  });
};

const setRefundCode = (index: number) => {
  salesReturnForm.forgetError(`refunds.${index}.code` as any);
  salesReturnRefundsForm.value[index].code =
    salesReturnRefundsForm.value[index].code === '_AUTO_' ? '' : '_AUTO_';
};

const clearItemErrors = () => {
  Object.keys(salesReturnForm.errors).forEach((key) => {
    if (key.startsWith('items.')) {
      salesReturnForm.forgetError(key as any);
    }
  });
};

const clearRefundErrors = () => {
  Object.keys(salesReturnForm.errors).forEach((key) => {
    if (key.startsWith('refunds')) {
      salesReturnForm.forgetError(key as any);
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

const appendVatProfileOption = (
  vatProfile?: {
    id?: string | null;
    name?: string | null;
    vat_rate?: number | null;
    vat_base_numerator?: number | null;
    vat_base_denominator?: number | null;
  } | null,
) => {
  if (!vatProfile?.id) return;

  const currentOptions = vatProfileDDL.value ?? [];
  if (currentOptions.some((option) => option.code === vatProfile.id)) return;

  currentOptions.push({
    code: vatProfile.id,
    name: vatProfile.name ?? vatProfile.id,
    vat_rate: Number(vatProfile.vat_rate ?? 0),
    vat_base_numerator: Number(vatProfile.vat_base_numerator ?? 1),
    vat_base_denominator: Number(vatProfile.vat_base_denominator ?? 1),
  });

  vatProfileDDL.value = [...currentOptions];
};

const resetItemVat = (item: SalesReturnItemFormItem) => {
  item.vat_profile_id = null;
  item.vat_profile_name = null;
  item.vat_rate = 0;
  item.vat_base_numerator = 1;
  item.vat_base_denominator = 1;
};

// returns without a sales invoice must stay VAT free
const enforceVatRules = () => {
  if (!isVatDisabled.value) return;
  salesReturnItemsForm.value.forEach((item) => resetItemVat(item));
};

const applyVatProfileToItem = (item: SalesReturnItemFormItem, vatProfileId: string | null) => {
  if (isVatDisabled.value) {
    resetItemVat(item);
    return;
  }

  item.vat_profile_id = vatProfileId;

  const selectedVatProfile = (vatProfileDDL.value ?? []).find((vatProfile) => vatProfile.code === vatProfileId);

  if (!selectedVatProfile) {
    item.vat_profile_name = null;
    item.vat_rate = 0;
    item.vat_base_numerator = 1;
    item.vat_base_denominator = 1;
    return;
  }

  item.vat_profile_name = selectedVatProfile.name;
  item.vat_rate = selectedVatProfile.vat_rate;
  item.vat_base_numerator = selectedVatProfile.vat_base_numerator;
  item.vat_base_denominator = selectedVatProfile.vat_base_denominator;
};

const clearVatProfile = (index: number) => {
  const item = salesReturnItemsForm.value[index];
  if (!item) return;
  applyVatProfileToItem(item, null);
  salesReturnForm.validate(`items.${index}.vat_profile_id` as any);
};

const syncVatProfile = (index: number) => {
  const item = salesReturnItemsForm.value[index];
  if (!item) return;
  applyVatProfileToItem(item, item.vat_profile_id ?? null);
  salesReturnForm.validate(`items.${index}.vat_profile_id` as any);
};

const formatCompactNumberValue = (value: number | string, precision = 4) =>
  formatCurrency(Number(Number(value ?? 0).toFixed(precision)));

const formatCurrencyPreviewValue = (value: number) => Number(value.toFixed(2));

const scrollToError = (id: string) => {
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

const showNotification = (title: string, content: string) => {
  const notification: NotificationData = {
    title,
    content,
  };
  emits('show-notification', notification);
};
// #endregion

// #region Methods - DDL
const loadCustomerDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await customerService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    status: undefined,
    include_id: (salesReturnForm.customer_id as string | null) ?? undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    customerDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadWarehouseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await warehouseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    status: undefined,
    include_id: (salesReturnForm.warehouse_id as string | null) ?? undefined,
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

const loadSalesInvoiceDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await salesInvoiceService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    customer_id: (salesReturnForm.customer_id as string | null) ?? undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    salesInvoiceDDL.value = (result.data.data as Array<any>).map((item: any) => ({
      code: item.id,
      name: `${item.code} - ${item.customer?.name ?? '-'}`,
      customer_id: item.customer?.id ?? null,
      sales_order_id: item.sales_order?.id ?? null,
    }));
  } else {
    salesInvoiceDDL.value = [];
  }
};

const loadVatProfileDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await vatProfileService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    vatProfileDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
      vat_rate: Number(item.vat_rate ?? 0),
      vat_base_numerator: Number(item.vat_base_numerator ?? 1),
      vat_base_denominator: Number(item.vat_base_denominator ?? 1),
    }));
  }

  salesReturnItemsForm.value.forEach((item) => {
    appendVatProfileOption({
      id: item.vat_profile_id,
      name: item.vat_profile_name,
      vat_rate: item.vat_rate,
      vat_base_numerator: item.vat_base_numerator,
      vat_base_denominator: item.vat_base_denominator,
    });
  });
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
      name: `${item.code} - ${item.name}`,
    }));
  }
};
// #endregion

// #region Methods - Header linkage
const handleCustomerChanged = async () => {
  salesReturnForm.validate('customer_id');
  salesReturnForm.setData({ sales_invoice_id: null });
  deliveryItemOptions.value = [];
  enforceVatRules();
  await loadSalesInvoiceDDL();
};

const clearCustomer = async () => {
  salesReturnForm.setData({ customer_id: null, sales_invoice_id: null });
  salesReturnForm.forgetError('customer_id');
  deliveryItemOptions.value = [];
  enforceVatRules();
  await loadSalesInvoiceDDL();
};

const clearWarehouse = () => {
  salesReturnForm.setData({ warehouse_id: null });
  salesReturnForm.forgetError('warehouse_id');
};

const handleSalesInvoiceChanged = () => {
  const option = salesInvoiceDDL.value.find((item) => item.code === salesReturnForm.sales_invoice_id);
  if (option?.customer_id) {
    salesReturnForm.setData({ customer_id: option.customer_id });
    appendDropDownOption(customerDDL, { code: option.customer_id, name: option.name });
    salesReturnForm.forgetError('customer_id');
    salesReturnForm.forgetError('branch_id');
  }

  enforceVatRules();
  clearItemErrors();
  salesReturnForm.validate('sales_invoice_id');
};

const clearSalesInvoice = () => {
  salesReturnForm.setData({ sales_invoice_id: null });
  salesReturnForm.forgetError('sales_invoice_id');
  enforceVatRules();
  clearItemErrors();
};
// #endregion

// #region Methods - Delivery item picker
const searchDeliveryItems = async () => {
  if (!selectedUserLocation.value) return;
  if (!salesReturnForm.customer_id) {
    deliveryItemOptions.value = [];
    return;
  }

  isSearchingDeliveryItem.value = true;

  const selectedInvoice = salesInvoiceDDL.value.find((item) => item.code === salesReturnForm.sales_invoice_id);

  const result = await salesOrderDeliveryService.readAnyPaginate({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search: deliveryItemSearchText.value,
    start_date: null,
    end_date: null,
    customer_id: salesReturnForm.customer_id,
    sales_order_id: selectedInvoice?.sales_order_id ?? null,
    warehouse_id: null,
    is_posted: null,
    refresh: true,
    page: 1,
    per_page: 50,
  });

  isSearchingDeliveryItem.value = false;

  if (result.success && result.data) {
    const deliveries = (result.data.data ?? []) as Array<any>;
    deliveryItemOptions.value = deliveries.flatMap((delivery: any) =>
      ((delivery.items ?? []) as Array<any>).map((deliveryItem: any) => {
        const productUnit = deliveryItem.product_unit;
        const product = productUnit?.product;
        const conversionValue = Number(deliveryItem.product_unit_conversion_value ?? 1) || 1;

        return {
          sales_order_delivery_item_id: deliveryItem.id,
          product_unit_id: productUnit?.id ?? '',
          product_unit_code: productUnit?.code ?? '',
          product_name: product?.name ?? '-',
          product_image_url: product?.main_product_image?.url ?? null,
          unit_name: productUnit?.unit?.name ?? '',
          base_unit_name: product?.base_product_unit?.unit?.name ?? '',
          conversion_value: conversionValue,
          qty: Number(deliveryItem.qty ?? 0),
          delivered_qty_base: Number(deliveryItem.product_unit_qty_base ?? 0),
          base_unit_cogs: Number(deliveryItem.base_unit_cogs ?? 0),
          price: Number(productUnit?.price ?? 0),
          product_unit_is_price_include_vat: Boolean(product?.is_price_include_vat),
          is_use_serial_number: Boolean(product?.is_use_serial_number),
          delivery_code: delivery.code ?? '-',
        };
      }),
    );
  } else {
    deliveryItemOptions.value = [];
  }
};

const openDeliveryItemPicker = async () => {
  deliveryItemSearchText.value = '';
  deliveryItemOptions.value = [];
  showDeliveryItemModal.value = true;
  await searchDeliveryItems();
};

const selectDeliveryItem = (option: DeliveryItemOption) => {
  const itemData: SalesReturnItemFormItem = {
    sales_order_delivery_item_id: option.sales_order_delivery_item_id,
    qty: option.qty > 0 ? option.qty : 1,
    product_unit_id: option.product_unit_id,
    product_unit_conversion_value: option.conversion_value,
    product_unit_price: option.price,
    product_unit_is_price_include_vat: option.product_unit_is_price_include_vat,
    price_discount: 0,
    subtotal_discount: 0,
    vat_profile_id: null,
    vat_rate: 0,
    vat_base_numerator: 1,
    vat_base_denominator: 1,
    remarks: '',
    serials: [],
    product_unit_product_code: option.product_unit_code,
    product_unit_product_name: option.product_name,
    product_unit_product_image_url: option.product_image_url,
    product_unit_unit_name: option.unit_name,
    product_unit_base_unit_name: option.conversion_value !== 1 ? option.base_unit_name : '',
    vat_profile_name: null,
    is_use_serial_number: option.is_use_serial_number,
    sales_order_delivery_item_label: `${option.delivery_code} - ${option.product_unit_code
      ? `[${option.product_unit_code}] ` : ''}${option.product_name}`,
    sales_order_delivery_item_qty_base: option.delivered_qty_base,
    base_unit_cogs: option.base_unit_cogs,
    total_cogs: null,
  };

  salesReturnForm.items.push(itemData as any);
  const targetIndex = salesReturnForm.items.length - 1;
  salesReturnItemDetailsExpanded.value[targetIndex] = false;

  showDeliveryItemModal.value = false;
  productUnitQtyToFocus.value = targetIndex;

  clearItemErrors();
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
  });

  isSearchingProductUnit.value = false;

  if (result.success && result.data) {
    const products = result.data.data as any[];
    productUnitOptions.value = products.flatMap((product: any) => {
      const units: any[] = product.product_units || [];
      const baseUnitName = units.find((unit: any) => Number(unit.conversion_value ?? 1) === 1)?.unit?.name ?? '';
      return units.map((unit: any) => ({
        product_unit_id: unit.id,
        product_unit_code: unit.code,
        product_name: product.name,
        product_image_url: product.main_product_image?.url ?? null,
        unit_name: unit.unit?.name ?? '',
        base_unit_name: baseUnitName,
        conversion_value: Number(unit.conversion_value ?? 1),
        price: Number(unit.price ?? 0),
        product_unit_is_price_include_vat: Boolean(product.is_price_include_vat),
        is_use_serial_number: Boolean(product.is_use_serial_number),
        vat_profile_id: product.default_vat_profile?.id ?? null,
        vat_profile_name: product.default_vat_profile?.name ?? null,
        vat_rate: Number(product.default_vat_profile?.vat_rate ?? 0),
        vat_base_numerator: Number(product.default_vat_profile?.vat_base_numerator ?? 1),
        vat_base_denominator: Number(product.default_vat_profile?.vat_base_denominator ?? 1),
      }));
    });
  } else {
    productUnitOptions.value = [];
  }
};

const openAddProductUnit = () => {
  productSearchText.value = '';
  productUnitOptions.value = [];
  editingProductUnitIndex.value = null;
  showProductUnitModal.value = true;
};

const openChangeProductUnit = (index: number) => {
  productSearchText.value = '';
  productUnitOptions.value = [];
  editingProductUnitIndex.value = index;
  showProductUnitModal.value = true;
};

const selectProductUnit = (option: ProductUnitOption) => {
  let vatProfileId: string | null = null;
  let vatProfileName: string | null = null;
  let vatRate = 0;
  let vatBaseNumerator = 1;
  let vatBaseDenominator = 1;

  if (option.vat_profile_id && !isVatDisabled.value) {
    vatProfileId = option.vat_profile_id;
    vatProfileName = option.vat_profile_name;
    vatRate = option.vat_rate;
    vatBaseNumerator = option.vat_base_numerator;
    vatBaseDenominator = option.vat_base_denominator;

    appendVatProfileOption({
      id: vatProfileId,
      name: vatProfileName,
      vat_rate: vatRate,
      vat_base_numerator: vatBaseNumerator,
      vat_base_denominator: vatBaseDenominator,
    });
  }

  const itemData: SalesReturnItemFormItem = {
    sales_order_delivery_item_id: null,
    qty: 1,
    product_unit_id: option.product_unit_id,
    product_unit_conversion_value: option.conversion_value,
    product_unit_price: option.price,
    product_unit_is_price_include_vat: option.product_unit_is_price_include_vat,
    price_discount: 0,
    subtotal_discount: 0,
    vat_profile_id: vatProfileId,
    vat_rate: vatRate,
    vat_base_numerator: vatBaseNumerator,
    vat_base_denominator: vatBaseDenominator,
    remarks: '',
    serials: [],
    product_unit_product_code: option.product_unit_code,
    product_unit_product_name: option.product_name,
    product_unit_product_image_url: option.product_image_url,
    product_unit_unit_name: option.unit_name,
    product_unit_base_unit_name: option.base_unit_name,
    vat_profile_name: vatProfileName,
    is_use_serial_number: option.is_use_serial_number,
    sales_order_delivery_item_label: null,
    sales_order_delivery_item_qty_base: null,
    base_unit_cogs: null,
    total_cogs: null,
  };

  let targetIndex: number;

  if (editingProductUnitIndex.value === null) {
    salesReturnForm.items.push(itemData as any);
    targetIndex = salesReturnForm.items.length - 1;
    salesReturnItemDetailsExpanded.value[targetIndex] = false;
  } else {
    const currentItem = salesReturnItemsForm.value[editingProductUnitIndex.value];
    salesReturnItemsForm.value[editingProductUnitIndex.value] = {
      ...currentItem,
      ...itemData,
      qty: currentItem?.qty ?? 1,
      price_discount: currentItem?.price_discount ?? 0,
      subtotal_discount: currentItem?.subtotal_discount ?? 0,
      remarks: currentItem?.remarks ?? '',
      serials: option.is_use_serial_number ? currentItem?.serials ?? [] : [],
      sales_order_delivery_item_id: currentItem?.sales_order_delivery_item_id ?? null,
      sales_order_delivery_item_label: currentItem?.sales_order_delivery_item_label ?? null,
      sales_order_delivery_item_qty_base: currentItem?.sales_order_delivery_item_qty_base ?? null,
    };
    targetIndex = editingProductUnitIndex.value;
  }

  showProductUnitModal.value = false;
  editingProductUnitIndex.value = null;
  productUnitQtyToFocus.value = targetIndex;

  clearItemErrors();
};

const handleItemPickerAfterLeave = () => {
  const index = productUnitQtyToFocus.value;
  productUnitQtyToFocus.value = null;

  if (index === null) return;

  nextTick(() => {
    const el = document.getElementById(`sales-return-item-qty-${index}`) as HTMLInputElement | null;
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el?.focus();
    el?.select();
  });
};

const removeProductUnit = (index: number) => {
  salesReturnItemsForm.value.splice(index, 1);
  salesReturnItemDetailsExpanded.value.splice(index, 1);
  clearItemErrors();
};

const unlinkDeliveryItem = (index: number) => {
  const item = salesReturnItemsForm.value[index];
  if (!item) return;
  item.sales_order_delivery_item_id = null;
  item.sales_order_delivery_item_label = null;
  item.sales_order_delivery_item_qty_base = null;
  clearItemErrors();
};

const toggleSalesReturnItemDetails = (index: number) => {
  salesReturnItemDetailsExpanded.value[index] = !salesReturnItemDetailsExpanded.value[index];
};

const addSerial = (index: number) => {
  const item = salesReturnItemsForm.value[index];
  if (!item) return;
  item.serials.push({ serial: '' });
  salesReturnForm.validate(`items.${index}.serials` as any);
};

const removeSerial = (index: number, serialIndex: number) => {
  const item = salesReturnItemsForm.value[index];
  if (!item) return;
  item.serials.splice(serialIndex, 1);
  salesReturnForm.validate(`items.${index}.serials` as any);
};
// #endregion

// #region Methods - Money previews
const getItemUnitPriceAfterDiscountPreview = (item: SalesReturnItemFormItem) => {
  const price = Math.max(Number(item.product_unit_price || 0), 0);
  const priceDiscount = Math.min(Math.max(Number(item.price_discount || 0), 0), price);
  return Math.max(price - priceDiscount, 0);
};

const getItemUnitPriceSubtotalAfterDiscountPreview = (item: SalesReturnItemFormItem) =>
  Number(item.qty || 0) * getItemUnitPriceAfterDiscountPreview(item);

const getItemSubtotalAfterDiscountPreview = (item: SalesReturnItemFormItem) => {
  const subtotal = getItemUnitPriceSubtotalAfterDiscountPreview(item);
  const subtotalDiscount = Math.min(Math.max(Number(item.subtotal_discount || 0), 0), subtotal);
  return Math.max(subtotal - subtotalDiscount, 0);
};

const getItemsSubtotalAfterDiscountPreview = () =>
  salesReturnItemsForm.value.reduce((total, item) => total + getItemSubtotalAfterDiscountPreview(item), 0);

const getSalesReturnGlobalDiscountPreview = () =>
  Math.min(
    Math.max(Number(salesReturnForm.global_discount || 0), 0),
    getItemsSubtotalAfterDiscountPreview(),
  );

const getItemGlobalDiscountPreview = (item: SalesReturnItemFormItem, itemIndex: number) => {
  const totalBeforeGlobalDiscount = getItemsSubtotalAfterDiscountPreview();
  const totalGlobalDiscount = getSalesReturnGlobalDiscountPreview();

  if (totalBeforeGlobalDiscount <= 0 || totalGlobalDiscount <= 0) {
    return 0;
  }

  const allocations = salesReturnItemsForm.value.map((currentItem, index) => {
    if (index === salesReturnItemsForm.value.length - 1) {
      return 0;
    }

    return totalGlobalDiscount * (getItemSubtotalAfterDiscountPreview(currentItem) / totalBeforeGlobalDiscount);
  });

  const allocatedBeforeCurrent = allocations
    .slice(0, itemIndex)
    .reduce((total, allocation) => total + allocation, 0);

  if (itemIndex === salesReturnItemsForm.value.length - 1) {
    return Math.max(totalGlobalDiscount - allocatedBeforeCurrent, 0);
  }

  return Math.min(Math.max(allocations[itemIndex] || 0, 0), getItemSubtotalAfterDiscountPreview(item));
};

const getItemSubtotalAfterGlobalDiscountPreview = (item: SalesReturnItemFormItem, itemIndex: number) =>
  Math.max(getItemSubtotalAfterDiscountPreview(item) - getItemGlobalDiscountPreview(item, itemIndex), 0);

const getItemVatBasePreview = (item: SalesReturnItemFormItem, itemIndex: number) => {
  const subtotalAfterGlobalDiscount = getItemSubtotalAfterGlobalDiscountPreview(item, itemIndex);
  const vatRate = Number(item.vat_rate || 0);
  const vatBaseFactor = Number(item.vat_base_denominator || 0) > 0
    ? Number(item.vat_base_numerator || 0) / Number(item.vat_base_denominator || 1)
    : 0;

  if (subtotalAfterGlobalDiscount <= 0 || vatRate <= 0 || vatBaseFactor <= 0) {
    return 0;
  }

  let taxableBase = subtotalAfterGlobalDiscount;

  if (item.product_unit_is_price_include_vat) {
    taxableBase = taxableBase / (1 + (vatRate / 100));
  }

  return taxableBase * vatBaseFactor;
};

const getItemVatPreview = (item: SalesReturnItemFormItem, itemIndex: number) => {
  const vatBase = getItemVatBasePreview(item, itemIndex);
  const vatRate = Number(item.vat_rate || 0);

  if (vatBase <= 0 || vatRate <= 0) {
    return 0;
  }

  return vatBase * (vatRate / 100);
};

const getItemTotalBeforeRoundingPreview = (item: SalesReturnItemFormItem, itemIndex: number) => {
  const subtotalAfterGlobalDiscount = getItemSubtotalAfterGlobalDiscountPreview(item, itemIndex);

  if (item.product_unit_is_price_include_vat) {
    return subtotalAfterGlobalDiscount;
  }

  return subtotalAfterGlobalDiscount + getItemVatPreview(item, itemIndex);
};

const getSalesReturnItemTotalAfterGlobalDiscountPreview = () =>
  salesReturnItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemSubtotalAfterGlobalDiscountPreview(item, itemIndex),
    0,
  );

const getSalesReturnVatBasePreview = () =>
  salesReturnItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemVatBasePreview(item, itemIndex),
    0,
  );

const getSalesReturnVatPreview = () =>
  salesReturnItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemVatPreview(item, itemIndex),
    0,
  );

const getTotalAmountPayableBeforeRoundingPreview = () =>
  salesReturnItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemTotalBeforeRoundingPreview(item, itemIndex),
    0,
  );

const getSalesReturnAmountPayablePreview = () =>
  getTotalAmountPayableBeforeRoundingPreview() + Number(salesReturnForm.rounding || 0);

const getRefundsTotalPreview = () =>
  salesReturnRefundsForm.value.reduce(
    (total, refund) => total + Math.max(Number(refund.amount || 0), 0),
    0,
  );

// a brand new return has nothing allocated to an invoice yet
const getAmountAllocatedToInvoicePreview = () => 0;

const getAmountAvailablePreview = () =>
  getSalesReturnAmountPayablePreview()
  - getAmountAllocatedToInvoicePreview()
  - getRefundsTotalPreview();
// #endregion

// #region Methods - Refunds
const addRefund = () => {
  salesReturnRefundsForm.value.push({
    code: '_AUTO_',
    date: '_AUTO_',
    cash_account_id: '',
    amount: 0,
    remarks: '',
  });
};

const removeRefund = (index: number) => {
  salesReturnRefundsForm.value.splice(index, 1);
  clearRefundErrors();
};

const clearRefundCashAccount = (index: number) => {
  const refund = salesReturnRefundsForm.value[index];
  if (!refund) return;
  refund.cash_account_id = '';
  validateSalesReturnField(`refunds.${index}.cash_account_id`);
};

// refunds may never exceed what is still available on the return
const clampRefundAmount = (index: number) => {
  const refund = salesReturnRefundsForm.value[index];
  if (!refund) return;

  const otherRefundsTotal = salesReturnRefundsForm.value.reduce(
    (total, current, currentIndex) => currentIndex === index
      ? total
      : total + Math.max(Number(current.amount || 0), 0),
    0,
  );
  const maxForRow = Math.max(
    getSalesReturnAmountPayablePreview() - getAmountAllocatedToInvoicePreview() - otherRefundsTotal,
    0,
  );

  if (Number(refund.amount || 0) > maxForRow) {
    refund.amount = maxForRow;
  }

  validateSalesReturnField(`refunds.${index}.amount`);
};
// #endregion

// #region Actions
const onSubmit = async () => {
  if (salesReturnForm.hasErrors) {
    const firstErrorKey = Object.keys(salesReturnForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  const backupItems = [...salesReturnItemsForm.value];
  const backupRefunds = [...salesReturnRefundsForm.value];

  // the cost snapshot is server-resolved and must never be sent
  const cleanedItems: SalesReturnItemNestedStoreRequest[] = salesReturnItemsForm.value.map((item) => ({
    sales_order_delivery_item_id: item.sales_order_delivery_item_id,
    qty: item.qty,
    product_unit_id: item.product_unit_id,
    product_unit_conversion_value: item.product_unit_conversion_value,
    product_unit_price: item.product_unit_price,
    product_unit_is_price_include_vat: item.product_unit_is_price_include_vat,
    price_discount: item.price_discount,
    subtotal_discount: item.subtotal_discount,
    vat_profile_id: item.vat_profile_id,
    vat_rate: item.vat_rate,
    vat_base_numerator: item.vat_base_numerator,
    vat_base_denominator: item.vat_base_denominator,
    remarks: item.remarks,
    serials: (item.serials ?? []).map((serial) => ({ serial: serial.serial })),
  }));

  const cleanedRefunds: SalesReturnRefundNestedStoreRequest[] = salesReturnRefundsForm.value.map((refund) => ({
    code: refund.code,
    date: refund.date,
    cash_account_id: refund.cash_account_id,
    amount: refund.amount,
    remarks: refund.remarks,
  }));

  salesReturnForm.items = cleanedItems as any;
  salesReturnForm.refunds = cleanedRefunds as any;

  emits('loading-state', true);

  try {
    await salesReturnForm.submit();
    cacheService.removeLastEntity('SALES_RETURN_CREATE');
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(t('views.sales_return.alert.create.title'), t('views.sales_return.alert.create.message'));
    router.push({ name: 'side-menu-sales-return-list' });
  } catch (error) {
    salesReturnForm.items = backupItems as any;
    salesReturnForm.refunds = backupRefunds as any;
    showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
  } finally {
    emits('loading-state', false);
  }
};
// #endregion
</script>

<template>
  <form id="salesReturnForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <!-- card: company and branch context -->
      <template #card-items-0>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.company.code }}
                <br />
                {{ selectedUserLocation.company.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="salesReturnForm.company_id" />
            </div>
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="salesReturnForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <!-- card: sales return header information -->
      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 md:col-span-6 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': salesReturnForm.invalid('code') }">
                {{ t('views.sales_return.fields.code') }}
              </FormLabel>
              <FormInputCode v-model="salesReturnForm.code"
                :class="{ 'border-danger': salesReturnForm.invalid('code') }"
                :placeholder="t('views.sales_return.fields.code')" @set-auto="setCode"
                @change="salesReturnForm.validate('code')" />
              <FormErrorMessages :messages="salesReturnForm.errors.code" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': salesReturnForm.invalid('date') }">
                {{ t('views.sales_return.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto v-model="salesReturnForm.date"
                :class="{ 'border-danger': salesReturnForm.invalid('date') }"
                :placeholder="t('views.sales_return.fields.date')" @change="salesReturnForm.validate('date')" />
              <FormErrorMessages :messages="salesReturnForm.errors.date" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-5 flex flex-col justify-center">
              <FormLabel :class="{ 'text-danger': salesReturnForm.invalid('is_posted') }">
                {{ t('views.sales_return.fields.is_posted') }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input v-model="salesReturnForm.is_posted" type="checkbox" />
              </FormSwitch>
              <FormErrorMessages :messages="salesReturnForm.errors.is_posted" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': salesReturnForm.invalid('customer_id') }">
                {{ t('views.sales_return.fields.customer_id') }}
              </FormLabel>
              <FormSelectSearch v-model="salesReturnForm.customer_id" v-model:search="customerSearch"
                :options="customerOptions" :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': salesReturnForm.invalid('customer_id') }"
                @change="handleCustomerChanged" @search="loadCustomerDDL" @clear="clearCustomer" />
              <FormErrorMessages :messages="salesReturnForm.errors.customer_id" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': salesReturnForm.invalid('warehouse_id') }">
                {{ t('views.sales_return.fields.warehouse_id') }}
              </FormLabel>
              <FormSelectSearch v-model="salesReturnForm.warehouse_id" v-model:search="warehouseSearch"
                :options="warehouseOptions" :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': salesReturnForm.invalid('warehouse_id') }"
                @change="salesReturnForm.validate('warehouse_id')" @search="loadWarehouseDDL"
                @clear="clearWarehouse" />
              <FormErrorMessages :messages="salesReturnForm.errors.warehouse_id" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': salesReturnForm.invalid('sales_invoice_id') }">
                {{ t('views.sales_return.fields.sales_invoice_id') }}
              </FormLabel>
              <FormSelectSearch v-model="salesReturnForm.sales_invoice_id" v-model:search="salesInvoiceSearch"
                :options="salesInvoiceOptions" :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': salesReturnForm.invalid('sales_invoice_id') }"
                @change="handleSalesInvoiceChanged" @search="loadSalesInvoiceDDL" @clear="clearSalesInvoice" />
              <FormErrorMessages :messages="salesReturnForm.errors.sales_invoice_id" />
            </div>
            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': salesReturnForm.invalid('remarks') }">
                {{ t('views.sales_return.fields.remarks') }}
              </FormLabel>
              <FormTextarea v-model="salesReturnForm.remarks"
                :class="{ 'border-danger': salesReturnForm.invalid('remarks') }"
                @change="salesReturnForm.validate('remarks')" />
              <FormErrorMessages :messages="salesReturnForm.errors.remarks" />
            </div>
          </div>

          <div v-if="isVatDisabled"
            class="mt-4 rounded-md border border-warning/30 bg-warning/10 px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
            {{ t('views.sales_return.fields.no_invoice_vat_hint') }}
          </div>
        </div>
      </template>

      <!-- card: sales return item list -->
      <template #card-items-2>
        <div class="p-5 space-y-4">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="text-sm text-slate-500">
              {{ t('views.sales_return.fields.item_count') }}: {{ salesReturnItemsForm.length }}
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <Button type="button" variant="outline-secondary" :disabled="!salesReturnForm.customer_id"
                @click="openDeliveryItemPicker">
                <Lucide icon="Truck" class="mr-2 h-4 w-4" />
                {{ t('views.sales_return.fields.sales_order_delivery_item_id') }}
              </Button>
              <Button type="button" variant="outline-primary" @click="openAddProductUnit">
                <Lucide icon="Plus" class="mr-2 h-4 w-4" />
                {{ t('components.buttons.create_new') }}
              </Button>
            </div>
          </div>

          <FormErrorMessages :messages="salesReturnForm.errors.items" />

          <div v-if="!salesReturnForm.customer_id"
            class="rounded-md border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500 dark:border-darkmode-400">
            {{ t('views.sales_return.fields.customer_required_hint') }}
          </div>
          <div v-else-if="salesReturnItemsForm.length === 0" class="text-slate-500 text-sm">
            {{ t('views.sales_return.fields.items_empty') }}
          </div>

          <div v-else>
            <div v-for="(item, index) in salesReturnItemsForm" :key="`${item.product_unit_id}-${index}`"
              class="mt-3 border-t border-slate-200/60 pt-5 first:mt-0 first:border-t-0 first:pt-0 dark:border-darkmode-400">
              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div :class="currentItemLayout === 'lg' ? 'col-span-12 lg:col-span-1' : (currentItemLayout === 'md' ? 'col-span-12 md:col-span-3' : 'col-span-12')">
                  <div class="form-control border rounded-md px-3">
                    <div class="flex justify-center">
                      <ProductImagePreview :image-url="item.product_unit_product_image_url"
                        wrapper-class="w-16 h-16 rounded-md overflow-hidden bg-slate-100 dark:bg-darkmode-600 flex items-center justify-center cursor-zoom-in shrink-0"
                        icon-class="w-5 h-5 text-slate-400"
                        :preview-title="item.product_unit_product_name || t('views.sales_return.fields.product_unit_id')" />
                    </div>
                  </div>
                </div>
                <div :class="currentItemLayout === 'lg' ? 'col-span-12 lg:col-span-4' : (currentItemLayout === 'md' ? 'col-span-12 md:col-span-9' : 'col-span-12')">
                  <FormLabel :class="{ 'text-danger': invalidSalesReturnField(`items.${index}.product_unit_id`) }">
                    <span>{{ t('views.sales_return.fields.product_unit_id') }}</span>
                    <span class="ml-2 text-xs font-normal text-slate-500 dark:text-slate-400">
                      #{{ index + 1 }}
                    </span>
                    <span class="ml-2 text-xs font-normal text-slate-500 dark:text-slate-400">
                      {{ item.product_unit_product_code || '-' }}
                    </span>
                  </FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <div
                        class="form-control border rounded-md px-3 py-2 bg-slate-50 dark:bg-darkmode-800 text-slate-700 dark:text-slate-300">
                        {{ item.product_unit_product_name || '-' }}
                      </div>
                    </div>
                    <div class="shrink-0">
                      <Button type="button" variant="outline-secondary" tabindex="-1"
                        class="flex items-center justify-center border-slate-500 text-slate-500 hover:text-primary hover:border-primary h-[38px] w-[38px]"
                        @click="openChangeProductUnit(index)">
                        <Lucide icon="Search" class="w-4 h-4" />
                      </Button>
                    </div>
                  </div>
                  <div v-if="item.sales_order_delivery_item_label"
                    class="mt-1 flex items-center gap-2 text-xs text-primary break-words">
                    <span>{{ item.sales_order_delivery_item_label }}</span>
                    <button type="button" class="text-slate-500 hover:text-danger" @click="unlinkDeliveryItem(index)">
                      <Lucide icon="X" class="h-3 w-3" />
                    </button>
                  </div>
                  <FormErrorMessages :messages="getSalesReturnFieldErrors(`items.${index}.product_unit_id`)" />
                  <FormErrorMessages
                    :messages="getSalesReturnFieldErrors(`items.${index}.sales_order_delivery_item_id`)" />
                </div>
                <div :class="currentItemLayout === 'lg' ? 'col-span-12 lg:col-span-2' : (currentItemLayout === 'md' ? 'col-span-12 md:col-span-4' : 'col-span-12')">
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <FormLabel :class="{ 'text-danger': invalidSalesReturnField(`items.${index}.qty`) }">
                        {{ t('views.sales_return.fields.qty') }}
                      </FormLabel>
                      <FormInputCurrency :id="`sales-return-item-qty-${index}`" v-model="item.qty"
                        :allow-negative="false"
                        :class="{ 'border-danger': invalidSalesReturnField(`items.${index}.qty`) }"
                        @change="validateSalesReturnField(`items.${index}.qty`)" />
                      <FormErrorMessages :messages="getSalesReturnFieldErrors(`items.${index}.qty`)" />
                    </div>
                    <div>
                      <FormLabel>
                        {{ t('views.product.table.cols.unit') }}
                      </FormLabel>
                      <FormInput :model-value="item.product_unit_unit_name || '-'" readonly />
                    </div>
                  </div>
                  <div v-if="Number(item.product_unit_conversion_value || 1) > 1" class="mt-1 text-right">
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                      {{ `${t('views.product.fields.conversion_value')}
                      ${formatCompactNumberValue(item.product_unit_conversion_value)}
                      ${item.product_unit_base_unit_name || ''}`.trim() }}
                    </div>
                  </div>
                  <div v-if="item.sales_order_delivery_item_qty_base !== null" class="mt-1 text-right">
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                      {{ t('views.sales_return.fields.delivered_qty_base') }}
                      {{ formatCompactNumberValue(item.sales_order_delivery_item_qty_base ?? 0) }}
                    </div>
                  </div>
                </div>
                <div :class="currentItemLayout === 'lg' ? 'col-span-12 lg:col-span-2' : (currentItemLayout === 'md' ? 'col-span-12 md:col-span-4' : 'col-span-12')">
                  <FormLabel :class="{ 'text-danger': invalidSalesReturnField(`items.${index}.product_unit_price`) }">
                    {{ t('views.sales_return.fields.product_unit_price') }}
                  </FormLabel>
                  <FormInputCurrency v-model="item.product_unit_price" :allow-negative="false"
                    :class="{ 'border-danger': invalidSalesReturnField(`items.${index}.product_unit_price`) }"
                    @change="validateSalesReturnField(`items.${index}.product_unit_price`)" />
                  <FormErrorMessages :messages="getSalesReturnFieldErrors(`items.${index}.product_unit_price`)" />
                </div>
                <div :class="currentItemLayout === 'lg' ? 'col-span-12 lg:col-span-3' : (currentItemLayout === 'md' ? 'col-span-12 md:col-span-4' : 'col-span-12')">
                  <FormLabel>{{ t('views.sales_return.fields.subtotal_after_discount') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency :model-value="getItemSubtotalAfterDiscountPreview(item)" readonly />
                    </div>
                    <div class="shrink-0">
                      <Button type="button" variant="outline-secondary"
                        class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                        @click="toggleSalesReturnItemDetails(index)">
                        {{ salesReturnItemDetailsExpanded[index] ? '▲' : '▼' }}
                      </Button>
                    </div>
                    <div class="shrink-0">
                      <Button type="button" variant="outline-secondary"
                        class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                        @click="removeProductUnit(index)">
                        <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                      </Button>
                    </div>
                  </div>
                </div>

                <!-- serial numbers for serial-tracked products -->
                <div v-if="item.is_use_serial_number" class="col-span-12">
                  <div class="mb-2 flex items-center justify-between">
                    <FormLabel :class="{ 'text-danger': invalidSalesReturnField(`items.${index}.serials`) }">
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
                    <div v-for="(serial, serialIndex) in item.serials" :key="`${index}-${serialIndex}`"
                      class="flex gap-2">
                      <FormInput v-model="serial.serial" :placeholder="t('views.product.fields.serial_number')"
                        :class="{ 'border-danger': invalidSalesReturnField(`items.${index}.serials.${serialIndex}.serial`) }"
                        @change="
                          salesReturnForm.validate(`items.${index}.serials.${serialIndex}.serial` as any);
                          salesReturnForm.validate(`items.${index}.serials` as any);
                        " />
                      <Button type="button" variant="outline-secondary" @click="removeSerial(index, serialIndex)">
                        <Lucide icon="Trash2" class="h-4 w-4 text-danger" />
                      </Button>
                    </div>
                  </div>

                  <FormErrorMessages :messages="getSalesReturnFieldErrors(`items.${index}.serials`)" />
                  <FormErrorMessages v-for="(_, serialIndex) in item.serials"
                    :key="`serial-error-${index}-${serialIndex}`"
                    :messages="getSalesReturnFieldErrors(`items.${index}.serials.${serialIndex}.serial`)" />
                </div>
              </div>

              <!-- item details: price breakdown + additional details -->
              <div v-if="salesReturnItemDetailsExpanded[index]" class="mt-4 grid grid-cols-12 gap-4">
                <div class="col-span-12 lg:col-span-6">
                  <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-4">
                    <div class="font-medium text-sm">{{ t('views.sales_return.fields.item_price_breakdown') }}</div>

                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-5 flex items-center text-sm font-medium">
                        {{ t('views.sales_return.fields.price_discount') }}
                      </div>
                      <div class="col-span-12 md:col-span-7">
                        <FormInputCurrency v-model="item.price_discount" :allow-negative="false"
                          :class="{ 'border-danger': invalidSalesReturnField(`items.${index}.price_discount`) }"
                          @change="validateSalesReturnField(`items.${index}.price_discount`)" />
                        <FormErrorMessages :messages="getSalesReturnFieldErrors(`items.${index}.price_discount`)" />
                      </div>

                      <div class="col-span-12 md:col-span-5 flex items-center text-sm font-medium">
                        {{ t('views.sales_return.fields.price_after_discount') }}
                      </div>
                      <div class="col-span-12 md:col-span-7">
                        <FormInputCurrency :model-value="getItemUnitPriceAfterDiscountPreview(item)" readonly />
                      </div>

                      <div class="col-span-12 md:col-span-5 flex items-center text-sm font-medium">
                        {{ t('views.sales_return.fields.subtotal') }}
                      </div>
                      <div class="col-span-12 md:col-span-7">
                        <FormInputCurrency :model-value="getItemUnitPriceSubtotalAfterDiscountPreview(item)" readonly />
                      </div>

                      <div class="col-span-12 md:col-span-5 flex items-center text-sm font-medium">
                        {{ t('views.sales_return.fields.subtotal_discount') }}
                      </div>
                      <div class="col-span-12 md:col-span-7">
                        <FormInputCurrency v-model="item.subtotal_discount" :allow-negative="false"
                          :class="{ 'border-danger': invalidSalesReturnField(`items.${index}.subtotal_discount`) }"
                          @change="validateSalesReturnField(`items.${index}.subtotal_discount`)" />
                        <FormErrorMessages :messages="getSalesReturnFieldErrors(`items.${index}.subtotal_discount`)" />
                      </div>

                      <div class="col-span-12 md:col-span-5 flex items-center text-sm font-medium">
                        {{ t('views.sales_return.fields.subtotal_after_discount') }}
                      </div>
                      <div class="col-span-12 md:col-span-7">
                        <FormInputCurrency :model-value="getItemSubtotalAfterDiscountPreview(item)" readonly />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-span-12 lg:col-span-6">
                  <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-4">
                    <div class="font-medium text-sm">{{ t('views.sales_return.fields.item_additional_details') }}</div>

                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-5">
                        <FormLabel>{{ t('views.sales_return.fields.product_unit_is_price_include_vat') }}</FormLabel>
                        <FormSwitch>
                          <FormSwitch.Input v-model="item.product_unit_is_price_include_vat" type="checkbox"
                            :disabled="isVatDisabled" />
                        </FormSwitch>
                      </div>
                      <div class="col-span-12 md:col-span-7">
                        <FormLabel :class="{ 'text-danger': invalidSalesReturnField(`items.${index}.vat_profile_id`) }">
                          {{ t('views.sales_return.fields.vat_profile_id') }}
                        </FormLabel>
                        <FormSelectSearch v-model="item.vat_profile_id" v-model:search="vatProfileSearch"
                          :options="vatProfileOptions" :placeholder="t('components.dropdown.placeholder')"
                          :disabled="isVatDisabled"
                          :class="{ 'border-danger': invalidSalesReturnField(`items.${index}.vat_profile_id`) }"
                          @change="syncVatProfile(index)" @search="loadVatProfileDDL" @clear="clearVatProfile(index)" />
                        <FormErrorMessages :messages="getSalesReturnFieldErrors(`items.${index}.vat_profile_id`)" />
                      </div>
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel :class="{ 'text-danger': invalidSalesReturnField(`items.${index}.vat_rate`) }">
                          {{ t('views.sales_return.fields.vat_rate') }}
                        </FormLabel>
                        <FormInputCurrency v-model="item.vat_rate" :allow-negative="false" :readonly="isVatDisabled"
                          :class="{ 'border-danger': invalidSalesReturnField(`items.${index}.vat_rate`) }"
                          @change="validateSalesReturnField(`items.${index}.vat_rate`)" />
                        <FormErrorMessages :messages="getSalesReturnFieldErrors(`items.${index}.vat_rate`)" />
                      </div>
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel :class="{ 'text-danger': invalidSalesReturnField(`items.${index}.vat_base_numerator`) }">
                          {{ t('views.sales_return.fields.vat_base_numerator') }}
                        </FormLabel>
                        <FormInput v-model="item.vat_base_numerator" type="number" min="1" :readonly="isVatDisabled"
                          :class="{ 'border-danger': invalidSalesReturnField(`items.${index}.vat_base_numerator`) }"
                          @change="validateSalesReturnField(`items.${index}.vat_base_numerator`)" />
                        <FormErrorMessages :messages="getSalesReturnFieldErrors(`items.${index}.vat_base_numerator`)" />
                      </div>
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel :class="{ 'text-danger': invalidSalesReturnField(`items.${index}.vat_base_denominator`) }">
                          {{ t('views.sales_return.fields.vat_base_denominator') }}
                        </FormLabel>
                        <FormInput v-model="item.vat_base_denominator" type="number" min="1" :readonly="isVatDisabled"
                          :class="{ 'border-danger': invalidSalesReturnField(`items.${index}.vat_base_denominator`) }"
                          @change="validateSalesReturnField(`items.${index}.vat_base_denominator`)" />
                        <FormErrorMessages
                          :messages="getSalesReturnFieldErrors(`items.${index}.vat_base_denominator`)" />
                      </div>
                      <!-- cost snapshot: resolved by the server, never sent -->
                      <div class="col-span-12 md:col-span-6">
                        <FormLabel>{{ t('views.sales_return.fields.base_unit_cogs') }}</FormLabel>
                        <FormInputCurrency :model-value="Number(item.base_unit_cogs ?? 0)" readonly />
                      </div>
                      <div class="col-span-12 md:col-span-6">
                        <FormLabel>{{ t('views.sales_return.fields.total_cogs') }}</FormLabel>
                        <FormInputCurrency :model-value="Number(item.total_cogs ?? 0)" readonly />
                      </div>
                      <div class="col-span-12 text-xs text-slate-500">
                        {{ t('views.sales_return.fields.cost_snapshot_hint') }}
                      </div>
                      <div class="col-span-12">
                        <FormLabel :class="{ 'text-danger': invalidSalesReturnField(`items.${index}.remarks`) }">
                          {{ t('views.sales_return.fields.remarks') }}
                        </FormLabel>
                        <FormTextarea v-model="item.remarks"
                          :class="{ 'border-danger': invalidSalesReturnField(`items.${index}.remarks`) }"
                          @change="validateSalesReturnField(`items.${index}.remarks`)" />
                        <FormErrorMessages :messages="getSalesReturnFieldErrors(`items.${index}.remarks`)" />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- card: financial summary -->
      <template #card-items-3>
        <div class="p-5 space-y-4">
          <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-4">
            <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
              <div class="col-span-12 lg:col-span-9"></div>
              <div class="col-span-12 lg:col-span-3">
                <FormLabel>{{ t('views.sales_return.fields.items_subtotal_after_discount') }}</FormLabel>
                <FormInputCurrency :model-value="getItemsSubtotalAfterDiscountPreview()" readonly />
              </div>
            </div>

            <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
              <div class="col-span-12 lg:col-span-9"></div>
              <div class="col-span-12 lg:col-span-3">
                <FormLabel :class="{ 'text-danger': salesReturnForm.invalid('global_discount') }">
                  {{ t('views.sales_return.fields.global_discount') }}
                </FormLabel>
                <FormInputCurrency v-model="salesReturnForm.global_discount" :allow-negative="false"
                  :class="{ 'border-danger': salesReturnForm.invalid('global_discount') }"
                  @change="salesReturnForm.validate('global_discount')" />
                <FormErrorMessages :messages="salesReturnForm.errors.global_discount" />
              </div>
            </div>

            <template v-if="isTotalsBreakdownExpanded">
              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.sales_return.fields.item_total_after_global_discount') }}</FormLabel>
                  <FormInputCurrency :model-value="getSalesReturnItemTotalAfterGlobalDiscountPreview()" readonly />
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.sales_return.fields.vat_base') }}</FormLabel>
                  <FormInputCurrency :model-value="formatCurrencyPreviewValue(getSalesReturnVatBasePreview())"
                    readonly />
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.sales_return.fields.vat') }}</FormLabel>
                  <FormInputCurrency :model-value="formatCurrencyPreviewValue(getSalesReturnVatPreview())" readonly />
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': salesReturnForm.invalid('rounding') }">
                    {{ t('views.sales_return.fields.rounding') }}
                  </FormLabel>
                  <FormInputCurrency v-model="salesReturnForm.rounding"
                    :class="{ 'border-danger': salesReturnForm.invalid('rounding') }"
                    @change="salesReturnForm.validate('rounding')" />
                  <FormErrorMessages :messages="salesReturnForm.errors.rounding" />
                </div>
              </div>
            </template>

            <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
              <div class="col-span-12 lg:col-span-9"></div>
              <div class="col-span-12 lg:col-span-3">
                <FormLabel>{{ t('views.sales_return.fields.amount_payable') }}</FormLabel>
                <div class="flex items-start gap-2">
                  <div class="shrink-0">
                    <Button type="button" variant="outline-secondary"
                      class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                      @click="isTotalsBreakdownExpanded = !isTotalsBreakdownExpanded">
                      {{ isTotalsBreakdownExpanded ? '▲' : '▼' }}
                    </Button>
                  </div>
                  <div class="flex-1 min-w-0">
                    <FormInputCurrency :model-value="getSalesReturnAmountPayablePreview()" readonly />
                  </div>
                </div>
              </div>
            </div>

            <!-- summary: refund editor and aggregates -->
            <div class="space-y-4">
              <div v-if="isRefundEditorExpanded" class="space-y-4">
                <FormErrorMessages :messages="salesReturnForm.errors.refunds" />

                <div v-if="salesReturnRefundsForm.length === 0" class="text-right text-slate-500 text-sm">
                  {{ t('views.sales_return.fields.refunds_empty') }}
                </div>

                <div v-else class="space-y-4">
                  <div v-for="(refund, index) in salesReturnRefundsForm" :key="`refund-${index}`"
                    class="space-y-3 rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4">
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-6 lg:col-span-3">
                        <FormLabel :class="{ 'text-danger': invalidSalesReturnField(`refunds.${index}.code`) }">
                          {{ t('views.sales_return.fields.code') }}
                        </FormLabel>
                        <FormInputCode v-model="refund.code"
                          :class="{ 'border-danger': invalidSalesReturnField(`refunds.${index}.code`) }"
                          :placeholder="t('views.sales_return.fields.code')" @set-auto="setRefundCode(index)"
                          @change="validateSalesReturnField(`refunds.${index}.code`)" />
                        <FormErrorMessages :messages="getSalesReturnFieldErrors(`refunds.${index}.code`)" />
                      </div>
                      <div class="col-span-12 md:col-span-6 lg:col-span-4">
                        <FormLabel :class="{ 'text-danger': invalidSalesReturnField(`refunds.${index}.date`) }">
                          {{ t('views.sales_return.fields.date') }}
                        </FormLabel>
                        <FormInputDateTimeAuto v-model="refund.date"
                          :class="{ 'border-danger': invalidSalesReturnField(`refunds.${index}.date`) }"
                          :placeholder="t('views.sales_return.fields.date')"
                          @change="validateSalesReturnField(`refunds.${index}.date`)" />
                        <FormErrorMessages :messages="getSalesReturnFieldErrors(`refunds.${index}.date`)" />
                      </div>
                      <div class="col-span-12 md:col-span-6 lg:col-span-3">
                        <FormLabel :class="{ 'text-danger': invalidSalesReturnField(`refunds.${index}.cash_account_id`) }">
                          {{ t('views.sales_return.fields.cash_account_id') }}
                        </FormLabel>
                        <FormSelectSearch v-model="refund.cash_account_id" v-model:search="cashAccountSearch"
                          :options="cashAccountOptions" :placeholder="t('components.dropdown.placeholder')"
                          :class="{ 'border-danger': invalidSalesReturnField(`refunds.${index}.cash_account_id`) }"
                          @change="validateSalesReturnField(`refunds.${index}.cash_account_id`)"
                          @search="loadCashAccountDDL" @clear="clearRefundCashAccount(index)" />
                        <FormErrorMessages :messages="getSalesReturnFieldErrors(`refunds.${index}.cash_account_id`)" />
                      </div>
                      <div class="col-span-12 md:col-span-6 lg:col-span-2">
                        <FormLabel :class="{ 'text-danger': invalidSalesReturnField(`refunds.${index}.amount`) }">
                          {{ t('views.sales_return.fields.amount') }}
                        </FormLabel>
                        <div class="flex items-start gap-2">
                          <div class="flex-1 min-w-0">
                            <FormInputCurrency v-model="refund.amount" :allow-negative="false"
                              :class="{ 'border-danger': invalidSalesReturnField(`refunds.${index}.amount`) }"
                              @change="clampRefundAmount(index)" />
                          </div>
                          <div class="shrink-0">
                            <Button type="button" variant="outline-secondary"
                              class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                              @click="removeRefund(index)">
                              <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                            </Button>
                          </div>
                        </div>
                        <FormErrorMessages :messages="getSalesReturnFieldErrors(`refunds.${index}.amount`)" />
                      </div>
                      <div class="col-span-12">
                        <FormLabel :class="{ 'text-danger': invalidSalesReturnField(`refunds.${index}.remarks`) }">
                          {{ t('views.sales_return.fields.remarks') }}
                        </FormLabel>
                        <FormTextarea v-model="refund.remarks"
                          :class="{ 'border-danger': invalidSalesReturnField(`refunds.${index}.remarks`) }"
                          @change="validateSalesReturnField(`refunds.${index}.remarks`)" />
                        <FormErrorMessages :messages="getSalesReturnFieldErrors(`refunds.${index}.remarks`)" />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="flex justify-end">
                  <Button type="button" variant="outline-primary" @click="addRefund">
                    <Lucide icon="Plus" class="w-4 h-4 mr-1" />
                    {{ t('components.buttons.create_new') }}
                  </Button>
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.sales_return.fields.amount_received_total') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="shrink-0">
                      <Button type="button" variant="outline-secondary"
                        class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                        @click="isRefundEditorExpanded = !isRefundEditorExpanded">
                        {{ isRefundEditorExpanded ? '▲' : '▼' }}
                      </Button>
                    </div>
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency :model-value="getRefundsTotalPreview()" readonly />
                    </div>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.sales_return.fields.amount_available') }}</FormLabel>
                  <FormInputCurrency :model-value="getAmountAvailablePreview()" readonly />
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- form actions: final submit -->
      <template #card-items-button>
        <div class="flex justify-end gap-2 p-5">
          <Button type="submit" variant="primary" class="w-32 shadow-md"
            :disabled="salesReturnForm.validating || salesReturnForm.hasErrors">
            <Lucide v-if="salesReturnForm.validating" icon="Loader" class="w-4 h-4 mr-2 animate-spin" />
            <template v-else>
              <Lucide icon="Save" class="w-4 h-4 mr-2" />
            </template>
            {{ t('components.buttons.save') }}
          </Button>
        </div>
      </template>
    </TwoColumnsLayout>
  </form>

  <!-- dialog: product unit picker for unlinked return lines -->
  <ProductUnitPickerDialog size="xl" panel-class="max-w-5xl" :open="showProductUnitModal"
    :title="t('views.sales_return.fields.product_unit_id')" :search-text="productSearchText"
    :is-searching="isSearchingProductUnit" :options="productUnitOptions" :columns="productUnitDialogColumns"
    @update:search-text="productSearchText = $event" @search="searchProductUnits"
    @select="selectProductUnit($event as ProductUnitOption)" @close="showProductUnitModal = false"
    @after-leave="handleItemPickerAfterLeave" />

  <!-- dialog: delivery item picker (delivered lines of this customer / invoice) -->
  <ProductUnitPickerDialog size="xl" panel-class="max-w-5xl" :open="showDeliveryItemModal"
    :title="t('views.sales_return.fields.sales_order_delivery_item_id')" :search-text="deliveryItemSearchText"
    :is-searching="isSearchingDeliveryItem" :options="deliveryItemOptions" :columns="deliveryItemDialogColumns"
    search-input-id="sales-return-delivery-item-search-input"
    @update:search-text="deliveryItemSearchText = $event" @search="searchDeliveryItems"
    @select="selectDeliveryItem($event as DeliveryItemOption)" @close="showDeliveryItemModal = false"
    @after-leave="handleItemPickerAfterLeave" />
</template>
