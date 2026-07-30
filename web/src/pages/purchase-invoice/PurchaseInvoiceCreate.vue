<script setup lang="ts">
// #region Imports
import { computed, nextTick, onMounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { debounce } from 'lodash';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { type TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import {
  FormErrorMessages,
  FormInput,
  FormInputCode,
  FormInputCurrency,
  FormInputDateTimeAuto,
  FormLabel,
  FormSelect,
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
import ProductService from '@/services/ProductService';
import PurchaseInvoiceService from '@/services/PurchaseInvoiceService';
import PurchaseOrderService from '@/services/PurchaseOrderService';
import PurchaseReturnService from '@/services/PurchaseReturnService';
import VatProfileService from '@/services/VatProfileService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { CardState } from '@/types/enums/CardState';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { NotificationData } from '@/types/models/NotificationData';
import type { PurchaseInvoicePaymentType } from '@/types/models/PurchaseInvoicePayment';
import type { PurchaseOrder } from '@/types/models/PurchaseOrder';
import type { PurchaseOrderItem } from '@/types/models/PurchaseOrderItem';
import type { PurchaseReturn } from '@/types/models/PurchaseReturn';
import type {
  PurchaseInvoiceItemNestedStoreRequest,
  PurchaseInvoicePaymentNestedStoreRequest,
} from '@/types/services/purchase-invoice/PurchaseInvoiceRequest';
import { convertErrorTypeToAlertListType, formatCurrency } from '@/utils/helper';
// #endregion

// #region Declarations
type PurchaseOrderOption = DropDownOption & {
  ulid: string;
  supplier_id: string | null;
  supplier_name: string | null;
};

type PurchaseInvoiceItemFormItem = PurchaseInvoiceItemNestedStoreRequest & {
  product_unit_product_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_product_image_url?: string | null;
  product_unit_unit_name?: string | null;
  product_unit_base_unit_name?: string | null;
  vat_profile_name?: string | null;
  purchase_order_item_label?: string | null;
  purchase_order_item_qty_to_invoice_base?: number | null;
};

type PurchaseInvoicePaymentFormItem = PurchaseInvoicePaymentNestedStoreRequest;

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
  vat_profile_id: string | null;
  vat_profile_name: string | null;
  vat_rate: number;
  vat_base_numerator: number;
  vat_base_denominator: number;
};

type VatProfileOption = {
  code: string;
  name: string;
  vat_rate: number;
  vat_base_numerator: number;
  vat_base_denominator: number;
};

const emits = defineEmits([
  'mode-state',
  'loading-state',
  'update-profile',
  'show-alertplaceholder',
  'show-notification',
]);

const { t } = useI18n();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const purchaseInvoiceService = new PurchaseInvoiceService();
const purchaseOrderService = new PurchaseOrderService();
const purchaseReturnService = new PurchaseReturnService();
const cashAccountService = new CashAccountService();
const vatProfileService = new VatProfileService();
const productService = new ProductService();
const cacheService = new CacheService();

const purchaseInvoiceForm = purchaseInvoiceService.usePurchaseInvoiceCreateForm();

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'views.purchase_invoice.field_groups.company_info', state: CardState.Expanded },
  { title: 'views.purchase_invoice.field_groups.purchase_invoice_data', state: CardState.Expanded },
  { title: 'views.purchase_invoice.field_groups.tax_invoice', state: CardState.Expanded },
  { title: 'views.purchase_invoice.field_groups.items', state: CardState.Expanded },
  { title: 'views.purchase_invoice.field_groups.summary', state: CardState.Expanded },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const selectedPurchaseOrderData = ref<PurchaseOrder | null>(null);
const purchaseOrderDDL = ref<Array<PurchaseOrderOption>>([]);
const purchaseOrderSearch = ref<string>('');

const vatProfileDDL = ref<Array<VatProfileOption>>([]);
const vatProfileSearch = ref<string>('');
const vatProfileOptions = computed(() =>
  vatProfileDDL.value.map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const cashAccountDDL = ref<Array<DropDownOption>>([]);
const cashAccountSearch = ref<string>('');
const cashAccountOptions = computed(() =>
  cashAccountDDL.value.map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const purchaseReturnDDL = ref<Array<PurchaseReturn>>([]);
const purchaseReturnSearch = ref<string>('');

const showProductUnitModal = ref<boolean>(false);
const productSearchText = ref<string>('');
const isSearchingProductUnit = ref<boolean>(false);
const productUnitOptions = ref<Array<ProductUnitOption>>([]);
const editingProductUnitIndex = ref<number | null>(null);
const productUnitQtyToFocus = ref<number | null>(null);
const isTotalsBreakdownExpanded = ref<boolean>(false);
const isPaymentEditorExpanded = ref<boolean>(false);

const purchaseOrderOptions = computed(() =>
  purchaseOrderDDL.value.map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const purchaseOrderPaymentOptions = computed(() =>
  (selectedPurchaseOrderData.value?.payments ?? []).map((payment) => ({
    value: payment.id,
    label: `${payment.code} - ${t('views.purchase_invoice.fields.available_amount')}: ${formatCurrency(
      Math.max(Number(payment.amount ?? 0) - Number(payment.amount_allocated ?? 0), 0),
    )}`,
  })),
);

const purchaseReturnOptions = computed(() =>
  purchaseReturnDDL.value.map((purchaseReturn) => ({
    value: purchaseReturn.id,
    label: `${purchaseReturn.code} - ${t('views.purchase_invoice.fields.available_amount')}: ${formatCurrency(
      Number(purchaseReturn.amount_available ?? 0),
    )}`,
  })),
);

const paymentTypeOptions = computed(() => [
  { value: 'cash', label: t('views.purchase_invoice.filters.payment_type_cash') },
  { value: 'down_payment', label: t('views.purchase_invoice.filters.payment_type_down_payment') },
  { value: 'return', label: t('views.purchase_invoice.filters.payment_type_return') },
]);

const productUnitDialogColumns = computed(() => [
  { key: 'unit_name', label: t('views.product.table.cols.unit') },
  {
    key: 'conversion_value',
    label: t('views.purchase_invoice.fields.product_unit_conversion_value'),
    align: 'right' as const,
    formatter: 'number' as const,
  },
  {
    key: 'price',
    label: t('views.purchase_invoice.fields.product_unit_price'),
    align: 'right' as const,
    formatter: 'number' as const,
  },
]);

const purchaseInvoiceItemsForm = computed<PurchaseInvoiceItemFormItem[]>(
  () => purchaseInvoiceForm.items as PurchaseInvoiceItemFormItem[],
);
const purchaseInvoicePaymentsForm = computed<PurchaseInvoicePaymentFormItem[]>(
  () => purchaseInvoiceForm.payments as PurchaseInvoicePaymentFormItem[],
);
// #endregion

// #region Vue Core
watch(
  purchaseInvoiceForm,
  debounce((newValue) => {
    cacheService.setLastEntity('PURCHASE_INVOICE_CREATE', newValue.data());
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

  purchaseInvoiceForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
  });

  emits('loading-state', true);
  try {
    await Promise.all([loadPurchaseOrderDDL(), loadVatProfileDDL(), loadCashAccountDDL(), loadPurchaseReturnDDL()]);

    if (purchaseInvoiceForm.purchase_order_id) {
      await loadPurchaseOrderDetailById(purchaseInvoiceForm.purchase_order_id);
    }
  } finally {
    emits('loading-state', false);
  }
});

const handleExpandCard = (index: number) => {
  cards.value[index].state =
    cards.value[index].state === CardState.Collapsed ? CardState.Expanded : CardState.Collapsed;
};
// #endregion

// #region Methods - Helpers
const invalidField = (field: string) => purchaseInvoiceForm.invalid(field as any);
const validateField = (field: string) => purchaseInvoiceForm.validate(field as any);
const getFieldErrors = (field: string) => (purchaseInvoiceForm.errors as Record<string, string | undefined>)[field];

const forgetErrorsWithPrefix = (prefix: string) => {
  Object.keys(purchaseInvoiceForm.errors).forEach((key) => {
    if (key.startsWith(prefix)) {
      purchaseInvoiceForm.forgetError(key as any);
    }
  });
};

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

const appendVatProfileOption = (vatProfile?: Partial<VatProfileOption> | null) => {
  if (!vatProfile?.code) return;
  if (vatProfileDDL.value.some((option) => option.code === vatProfile.code)) return;

  vatProfileDDL.value = [
    ...vatProfileDDL.value,
    {
      code: vatProfile.code,
      name: vatProfile.name ?? vatProfile.code,
      vat_rate: Number(vatProfile.vat_rate ?? 0),
      vat_base_numerator: Number(vatProfile.vat_base_numerator ?? 1),
      vat_base_denominator: Number(vatProfile.vat_base_denominator ?? 1),
    },
  ];
};

const loadFromCache = () => {
  const data = cacheService.getLastEntity('PURCHASE_INVOICE_CREATE') as Record<string, unknown>;
  if (!data) return;
  purchaseInvoiceForm.setData(data);
};

const selectedPurchaseOrderOption = computed(() =>
  purchaseOrderDDL.value.find((item) => item.code === purchaseInvoiceForm.purchase_order_id),
);
// #endregion

// #region Methods - DDL
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
    include_id: purchaseInvoiceForm.purchase_order_id ?? undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    // an invoice may only be created for a purchase order that HAS a supplier
    purchaseOrderDDL.value = result.data.data
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

  purchaseInvoiceItemsForm.value.forEach((item) => {
    appendVatProfileOption({
      code: item.vat_profile_id ?? undefined,
      name: item.vat_profile_name ?? undefined,
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

const loadPurchaseReturnDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await purchaseReturnService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    supplier_id: purchaseInvoiceForm.supplier_id ?? undefined,
    is_settled: false,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    // only invoiced returns may settle an invoice, and only while they still have room
    purchaseReturnDDL.value = result.data.data.filter(
      (item) => Boolean(item.purchase_invoice) && Number(item.amount_available ?? 0) > 0,
    );
  } else {
    purchaseReturnDDL.value = [];
  }
};
// #endregion

// #region Methods - Purchase Order linkage
const buildItemFromPurchaseOrderItem = (purchaseOrderItem: PurchaseOrderItem): PurchaseInvoiceItemFormItem => {
  const productUnit = purchaseOrderItem.product_unit;
  const product = productUnit?.product;
  const conversionValue = Number(purchaseOrderItem.product_unit_conversion_value ?? 1) || 1;
  // qty to invoice is derived client-side: base qty minus what has already been
  // invoiced (NOT qty_outstanding_base, which tracks receipts)
  const qtyToInvoiceBase = Math.max(
    Number(purchaseOrderItem.product_unit_qty_base ?? 0) - Number(purchaseOrderItem.qty_invoiced_base ?? 0),
    0,
  );
  const qty = qtyToInvoiceBase > 0 ? qtyToInvoiceBase / conversionValue : 0;

  appendVatProfileOption({
    code: purchaseOrderItem.vat_profile?.id,
    name: purchaseOrderItem.vat_profile?.name,
    vat_rate: Number(purchaseOrderItem.vat_rate ?? 0),
    vat_base_numerator: Number(purchaseOrderItem.vat_base_numerator ?? 1),
    vat_base_denominator: Number(purchaseOrderItem.vat_base_denominator ?? 1),
  });

  return {
    qty,
    product_unit_id: productUnit?.id ?? '',
    product_unit_conversion_value: conversionValue,
    product_unit_price: Number(purchaseOrderItem.product_unit_price ?? 0),
    product_unit_is_price_include_vat: Boolean(purchaseOrderItem.product_unit_is_price_include_vat),
    price_discount: Number(purchaseOrderItem.price_discount ?? 0),
    subtotal_discount: Number(purchaseOrderItem.subtotal_discount ?? 0),
    vat_profile_id: purchaseOrderItem.vat_profile?.id ?? null,
    vat_rate: Number(purchaseOrderItem.vat_rate ?? 0),
    vat_base_numerator: Number(purchaseOrderItem.vat_base_numerator ?? 1),
    vat_base_denominator: Number(purchaseOrderItem.vat_base_denominator ?? 1),
    purchase_order_item_id: purchaseOrderItem.id ?? null,
    remarks: purchaseOrderItem.remarks ?? '',
    product_unit_product_code: productUnit?.code ?? '',
    product_unit_product_name: product?.name ?? '-',
    product_unit_product_image_url: (product as any)?.main_product_image?.url ?? null,
    product_unit_unit_name: productUnit?.unit?.name ?? '',
    product_unit_base_unit_name: conversionValue !== 1 ? ((product as any)?.base_product_unit?.unit?.name ?? '') : '',
    vat_profile_name: purchaseOrderItem.vat_profile?.name ?? null,
    purchase_order_item_label: productUnit?.code ? `[${productUnit.code}] ${product?.name ?? '-'}` : null,
    purchase_order_item_qty_to_invoice_base: qtyToInvoiceBase,
  };
};

const syncFromPurchaseOrder = (purchaseOrder: PurchaseOrder) => {
  purchaseInvoiceForm.setData({
    supplier_id: purchaseOrder.supplier?.id ?? null,
    due_days: Number(purchaseOrder.due_days ?? 0),
    items: (purchaseOrder.items ?? []).map((item) => buildItemFromPurchaseOrderItem(item)) as any,
  });

  forgetErrorsWithPrefix('items.');
  purchaseInvoiceForm.forgetError('supplier_id');
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

const loadPurchaseOrderDetailById = async (purchaseOrderId: string) => {
  const option = purchaseOrderDDL.value.find((item) => item.code === purchaseOrderId);
  if (!option) return null;
  return loadPurchaseOrderDetail(option.ulid);
};

const clearPurchaseOrder = async () => {
  selectedPurchaseOrderData.value = null;
  purchaseInvoiceForm.setData({
    purchase_order_id: null,
    supplier_id: null,
    items: [] as any,
  });
  purchaseInvoiceForm.forgetError('purchase_order_id');
  forgetErrorsWithPrefix('items.');
  await loadPurchaseOrderDDL();
};

const handlePurchaseOrderChanged = async (purchaseOrderId: string | number | null) => {
  purchaseInvoiceForm.validate('purchase_order_id');

  if (!purchaseOrderId) {
    await clearPurchaseOrder();
    return;
  }

  const purchaseOrder = await loadPurchaseOrderDetailById(String(purchaseOrderId));
  if (!purchaseOrder) return;

  syncFromPurchaseOrder(purchaseOrder);
  await loadPurchaseReturnDDL();
};

const reloadItemsFromPurchaseOrder = async () => {
  if (!purchaseInvoiceForm.purchase_order_id) return;

  const purchaseOrder = await loadPurchaseOrderDetailById(purchaseInvoiceForm.purchase_order_id);
  if (!purchaseOrder) return;

  syncFromPurchaseOrder(purchaseOrder);
};
// #endregion

// #region Methods - Codes
const setCode = () => {
  purchaseInvoiceForm.forgetError('code');
  purchaseInvoiceForm.setData({ code: purchaseInvoiceForm.code === '_AUTO_' ? '' : '_AUTO_' });
};

const setPaymentCode = (index: number) => {
  purchaseInvoiceForm.forgetError(`payments.${index}.code` as any);
  const payment = purchaseInvoicePaymentsForm.value[index];
  if (!payment) return;
  payment.code = payment.code === '_AUTO_' ? '' : '_AUTO_';
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

const buildItemFromProductUnit = (option: ProductUnitOption): PurchaseInvoiceItemFormItem => {
  if (option.vat_profile_id) {
    appendVatProfileOption({
      code: option.vat_profile_id,
      name: option.vat_profile_name ?? option.vat_profile_id,
      vat_rate: option.vat_rate,
      vat_base_numerator: option.vat_base_numerator,
      vat_base_denominator: option.vat_base_denominator,
    });
  }

  return {
    qty: 1,
    product_unit_id: option.product_unit_id,
    product_unit_conversion_value: option.conversion_value,
    product_unit_price: option.price,
    product_unit_is_price_include_vat: option.product_unit_is_price_include_vat,
    price_discount: 0,
    subtotal_discount: 0,
    vat_profile_id: option.vat_profile_id,
    vat_rate: option.vat_profile_id ? option.vat_rate : 0,
    vat_base_numerator: option.vat_profile_id ? option.vat_base_numerator : 1,
    vat_base_denominator: option.vat_profile_id ? option.vat_base_denominator : 1,
    purchase_order_item_id: null,
    remarks: '',
    product_unit_product_code: option.product_unit_code,
    product_unit_product_name: option.product_name,
    product_unit_product_image_url: option.product_image_url,
    product_unit_unit_name: option.unit_name,
    product_unit_base_unit_name: option.base_unit_name,
    vat_profile_name: option.vat_profile_name,
    purchase_order_item_label: null,
    purchase_order_item_qty_to_invoice_base: null,
  };
};

const selectProductUnit = (option: ProductUnitOption) => {
  const itemData = buildItemFromProductUnit(option);
  let targetIndex: number;

  if (editingProductUnitIndex.value === null) {
    purchaseInvoiceForm.items.push(itemData as any);
    targetIndex = purchaseInvoiceForm.items.length - 1;
  } else {
    const currentItem = purchaseInvoiceItemsForm.value[editingProductUnitIndex.value];
    purchaseInvoiceItemsForm.value[editingProductUnitIndex.value] = {
      ...currentItem,
      ...itemData,
      qty: currentItem?.qty ?? 1,
      remarks: currentItem?.remarks ?? '',
      // keep the purchase order line link of the row being replaced
      purchase_order_item_id: currentItem?.purchase_order_item_id ?? null,
      purchase_order_item_label: currentItem?.purchase_order_item_label ?? null,
      purchase_order_item_qty_to_invoice_base: currentItem?.purchase_order_item_qty_to_invoice_base ?? null,
    };
    targetIndex = editingProductUnitIndex.value;
  }

  showProductUnitModal.value = false;
  editingProductUnitIndex.value = null;
  productUnitQtyToFocus.value = targetIndex;
  forgetErrorsWithPrefix('items.');
};

const handleProductUnitModalAfterLeave = () => {
  const index = productUnitQtyToFocus.value;
  productUnitQtyToFocus.value = null;
  if (index === null) return;

  nextTick(() => {
    const el = document.getElementById(`purchase-invoice-item-qty-${index}`) as HTMLInputElement | null;
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el?.focus();
    el?.select();
  });
};

const removeItem = (index: number) => {
  purchaseInvoiceItemsForm.value.splice(index, 1);
  forgetErrorsWithPrefix('items.');
};

const applyVatProfileToItem = (item: PurchaseInvoiceItemFormItem, vatProfileId: string | null) => {
  item.vat_profile_id = vatProfileId;

  const selectedVatProfile = vatProfileDDL.value.find((vatProfile) => vatProfile.code === vatProfileId);

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

const syncVatProfile = (index: number) => {
  const item = purchaseInvoiceItemsForm.value[index];
  if (!item) return;
  applyVatProfileToItem(item, item.vat_profile_id ?? null);
  validateField(`items.${index}.vat_profile_id`);
};

const clearVatProfile = (index: number) => {
  const item = purchaseInvoiceItemsForm.value[index];
  if (!item) return;
  applyVatProfileToItem(item, null);
  validateField(`items.${index}.vat_profile_id`);
};
// #endregion

// #region Methods - Payments
const addPayment = () => {
  isPaymentEditorExpanded.value = true;
  purchaseInvoiceForm.payments.push({
    code: '_AUTO_',
    date: '_AUTO_',
    payment_type: 'cash',
    cash_account_id: null,
    purchase_order_payment_id: null,
    purchase_return_id: null,
    amount: 0,
    remarks: '',
  } as any);
};

const removePayment = (index: number) => {
  purchaseInvoicePaymentsForm.value.splice(index, 1);
  forgetErrorsWithPrefix('payments.');
};

/**
 * Only the foreign key that belongs to the selected payment type may be sent -
 * the backend rejects the other two.
 */
const resetPaymentReferences = (index: number) => {
  const payment = purchaseInvoicePaymentsForm.value[index];
  if (!payment) return;

  payment.cash_account_id = null;
  payment.purchase_order_payment_id = null;
  payment.purchase_return_id = null;

  forgetErrorsWithPrefix(`payments.${index}.`);
  validateField(`payments.${index}.payment_type`);
};

const getPaymentAvailableAmount = (payment: PurchaseInvoicePaymentFormItem) => {
  if (payment.payment_type === 'down_payment' && payment.purchase_order_payment_id) {
    const purchaseOrderPayment = (selectedPurchaseOrderData.value?.payments ?? []).find(
      (item) => item.id === payment.purchase_order_payment_id,
    );
    if (!purchaseOrderPayment) return null;
    return Math.max(Number(purchaseOrderPayment.amount ?? 0) - Number(purchaseOrderPayment.amount_allocated ?? 0), 0);
  }

  if (payment.payment_type === 'return' && payment.purchase_return_id) {
    const purchaseReturn = purchaseReturnDDL.value.find((item) => item.id === payment.purchase_return_id);
    if (!purchaseReturn) return null;
    return Number(purchaseReturn.amount_available ?? 0);
  }

  return null;
};
// #endregion

// #region Methods - Money previews
const getItemPriceAfterDiscountPreview = (item: PurchaseInvoiceItemFormItem) => {
  const price = Math.max(Number(item.product_unit_price || 0), 0);
  const priceDiscount = Math.min(Math.max(Number(item.price_discount || 0), 0), price);
  return Math.max(price - priceDiscount, 0);
};

const getItemSubtotalPreview = (item: PurchaseInvoiceItemFormItem) =>
  Math.max(Number(item.qty || 0), 0) * getItemPriceAfterDiscountPreview(item);

const getItemSubtotalAfterDiscountPreview = (item: PurchaseInvoiceItemFormItem) => {
  const subtotal = getItemSubtotalPreview(item);
  const subtotalDiscount = Math.min(Math.max(Number(item.subtotal_discount || 0), 0), subtotal);
  return Math.max(subtotal - subtotalDiscount, 0);
};

const getItemsSubtotalAfterDiscountPreview = () =>
  purchaseInvoiceItemsForm.value.reduce((total, item) => total + getItemSubtotalAfterDiscountPreview(item), 0);

const getGlobalDiscountPreview = () =>
  Math.min(Math.max(Number(purchaseInvoiceForm.global_discount || 0), 0), getItemsSubtotalAfterDiscountPreview());

const getItemGlobalDiscountPreview = (item: PurchaseInvoiceItemFormItem, itemIndex: number) => {
  const totalBeforeGlobalDiscount = getItemsSubtotalAfterDiscountPreview();
  const totalGlobalDiscount = getGlobalDiscountPreview();

  if (totalBeforeGlobalDiscount <= 0 || totalGlobalDiscount <= 0) return 0;

  const allocations = purchaseInvoiceItemsForm.value.map((currentItem, index) => {
    if (index === purchaseInvoiceItemsForm.value.length - 1) return 0;
    return totalGlobalDiscount * (getItemSubtotalAfterDiscountPreview(currentItem) / totalBeforeGlobalDiscount);
  });

  const allocatedBeforeCurrent = allocations.slice(0, itemIndex).reduce((total, allocation) => total + allocation, 0);

  // the last item absorbs the rounding residue, exactly like the backend
  if (itemIndex === purchaseInvoiceItemsForm.value.length - 1) {
    return Math.max(totalGlobalDiscount - allocatedBeforeCurrent, 0);
  }

  return Math.min(Math.max(allocations[itemIndex] || 0, 0), getItemSubtotalAfterDiscountPreview(item));
};

const getItemSubtotalAfterGlobalDiscountPreview = (item: PurchaseInvoiceItemFormItem, itemIndex: number) =>
  Math.max(getItemSubtotalAfterDiscountPreview(item) - getItemGlobalDiscountPreview(item, itemIndex), 0);

const getItemVatBasePreview = (item: PurchaseInvoiceItemFormItem, itemIndex: number) => {
  const subtotalAfterGlobalDiscount = getItemSubtotalAfterGlobalDiscountPreview(item, itemIndex);
  const vatRate = Number(item.vat_rate || 0);
  const vatBaseFactor =
    Number(item.vat_base_denominator || 0) > 0
      ? Number(item.vat_base_numerator || 0) / Number(item.vat_base_denominator || 1)
      : 0;

  if (subtotalAfterGlobalDiscount <= 0 || vatRate <= 0 || vatBaseFactor <= 0) return 0;

  let taxableBase = subtotalAfterGlobalDiscount;
  if (item.product_unit_is_price_include_vat) {
    taxableBase = taxableBase / (1 + vatRate / 100);
  }

  return taxableBase * vatBaseFactor;
};

const getItemVatPreview = (item: PurchaseInvoiceItemFormItem, itemIndex: number) => {
  const vatBase = getItemVatBasePreview(item, itemIndex);
  const vatRate = Number(item.vat_rate || 0);
  if (vatBase <= 0 || vatRate <= 0) return 0;
  return vatBase * (vatRate / 100);
};

const getItemTotalBeforeRoundingPreview = (item: PurchaseInvoiceItemFormItem, itemIndex: number) => {
  const subtotalAfterGlobalDiscount = getItemSubtotalAfterGlobalDiscountPreview(item, itemIndex);
  if (item.product_unit_is_price_include_vat) return subtotalAfterGlobalDiscount;
  return subtotalAfterGlobalDiscount + getItemVatPreview(item, itemIndex);
};

const getItemAmountPayablePreview = (item: PurchaseInvoiceItemFormItem) => {
  const itemIndex = purchaseInvoiceItemsForm.value.indexOf(item);
  if (itemIndex < 0) return 0;
  return getItemTotalBeforeRoundingPreview(item, itemIndex);
};

const getItemTotalAfterGlobalDiscountPreview = () =>
  purchaseInvoiceItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemSubtotalAfterGlobalDiscountPreview(item, itemIndex),
    0,
  );

const getVatBasePreview = () =>
  purchaseInvoiceItemsForm.value.reduce((total, item, itemIndex) => total + getItemVatBasePreview(item, itemIndex), 0);

const getVatPreview = () =>
  purchaseInvoiceItemsForm.value.reduce((total, item, itemIndex) => total + getItemVatPreview(item, itemIndex), 0);

const getAmountPayablePreview = () =>
  purchaseInvoiceItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemTotalBeforeRoundingPreview(item, itemIndex),
    0,
  ) + Number(purchaseInvoiceForm.rounding || 0);

const getPaymentsTotalPreview = () =>
  purchaseInvoicePaymentsForm.value.reduce((total, payment) => total + Math.max(Number(payment.amount || 0), 0), 0);

const getAmountDuePreview = () => getAmountPayablePreview() - getPaymentsTotalPreview();

const formatCurrencyPreviewValue = (value: number) => Number(value.toFixed(2));
const formatQuantityValue = (value: number | string | null | undefined, precision = 4) =>
  new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: precision,
  }).format(Number(value ?? 0));
// #endregion

// #region Actions
const onSubmit = async () => {
  if (purchaseInvoiceForm.hasErrors) {
    const firstErrorKey = Object.keys(purchaseInvoiceForm.errors)[0];
    if (firstErrorKey) scrollToError(firstErrorKey);
    return;
  }

  const backupItems = [...purchaseInvoiceItemsForm.value];
  const backupPayments = [...purchaseInvoicePaymentsForm.value];

  const cleanedItems: PurchaseInvoiceItemNestedStoreRequest[] = purchaseInvoiceItemsForm.value.map((item) => ({
    qty: Number(item.qty ?? 0),
    product_unit_id: item.product_unit_id,
    product_unit_conversion_value: Number(item.product_unit_conversion_value ?? 1),
    product_unit_price: Number(item.product_unit_price ?? 0),
    product_unit_is_price_include_vat: Boolean(item.product_unit_is_price_include_vat),
    price_discount: Number(item.price_discount ?? 0),
    subtotal_discount: Number(item.subtotal_discount ?? 0),
    vat_profile_id: item.vat_profile_id ?? null,
    vat_rate: Number(item.vat_rate ?? 0),
    vat_base_numerator: Number(item.vat_base_numerator ?? 1),
    vat_base_denominator: Number(item.vat_base_denominator ?? 1),
    purchase_order_item_id: item.purchase_order_item_id ?? null,
    remarks: item.remarks ?? '',
  }));

  const cleanedPayments: PurchaseInvoicePaymentNestedStoreRequest[] = purchaseInvoicePaymentsForm.value.map(
    (payment) => ({
      code: payment.code,
      date: payment.date,
      payment_type: payment.payment_type,
      cash_account_id: payment.payment_type === 'cash' ? (payment.cash_account_id ?? null) : null,
      purchase_order_payment_id:
        payment.payment_type === 'down_payment' ? (payment.purchase_order_payment_id ?? null) : null,
      purchase_return_id: payment.payment_type === 'return' ? (payment.purchase_return_id ?? null) : null,
      amount: Number(payment.amount ?? 0),
      remarks: payment.remarks ?? '',
    }),
  );

  purchaseInvoiceForm.items = cleanedItems as any;
  purchaseInvoiceForm.payments = cleanedPayments as any;

  emits('loading-state', true);

  try {
    await purchaseInvoiceForm.submit();
    cacheService.removeLastEntity('PURCHASE_INVOICE_CREATE');
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(t('views.purchase_invoice.alert.create.title'), t('views.purchase_invoice.alert.create.message'));
    router.push({ name: 'side-menu-purchase-invoice-list' });
  } catch (error) {
    purchaseInvoiceForm.items = backupItems as any;
    purchaseInvoiceForm.payments = backupPayments as any;
    showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
  } finally {
    emits('loading-state', false);
  }
};
// #endregion
</script>

<template>
  <form v-if="selectedUserLocation" id="purchaseInvoiceForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <!-- card: company and branch context -->
      <template #card-items-0>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel>
                {{ selectedUserLocation.company.code }}
                <br />
                {{ selectedUserLocation.company.name }}
              </FormLabel>
              <FormInput v-model="purchaseInvoiceForm.company_id" type="hidden" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput v-model="purchaseInvoiceForm.branch_id" type="hidden" />
            </div>
          </div>
        </div>
      </template>

      <!-- card: invoice header -->
      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 md:col-span-6 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': invalidField('code') }">
                {{ t('views.purchase_invoice.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="purchaseInvoiceForm.code"
                :class="{ 'border-danger': invalidField('code') }"
                :placeholder="t('views.purchase_invoice.fields.code')"
                @set-auto="setCode"
                @change="validateField('code')"
              />
              <FormErrorMessages :messages="purchaseInvoiceForm.errors.code" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': invalidField('date') }">
                {{ t('views.purchase_invoice.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="purchaseInvoiceForm.date"
                :class="{ 'border-danger': invalidField('date') }"
                :placeholder="t('views.purchase_invoice.fields.date')"
                @change="validateField('date')"
              />
              <FormErrorMessages :messages="purchaseInvoiceForm.errors.date" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': invalidField('due_days') }">
                {{ t('views.purchase_invoice.fields.due_days') }}
              </FormLabel>
              <FormInput
                v-model="purchaseInvoiceForm.due_days"
                type="number"
                min="0"
                :class="{ 'border-danger': invalidField('due_days') }"
                @change="validateField('due_days')"
              />
              <FormErrorMessages :messages="purchaseInvoiceForm.errors.due_days" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('purchase_order_id') }">
                {{ t('views.purchase_invoice.fields.purchase_order_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="purchaseInvoiceForm.purchase_order_id"
                v-model:search="purchaseOrderSearch"
                :options="purchaseOrderOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': invalidField('purchase_order_id') }"
                @change="(value) => handlePurchaseOrderChanged(value as string | number | null)"
                @search="loadPurchaseOrderDDL"
                @clear="clearPurchaseOrder"
              />
              <FormErrorMessages :messages="purchaseInvoiceForm.errors.purchase_order_id" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('supplier_id') }">
                {{ t('views.purchase_invoice.fields.supplier_id') }}
              </FormLabel>
              <FormInput
                :model-value="
                  selectedPurchaseOrderData?.supplier?.name ?? selectedPurchaseOrderOption?.supplier_name ?? ''
                "
                readonly
                :class="{ 'border-danger': invalidField('supplier_id') }"
              />
              <FormErrorMessages :messages="purchaseInvoiceForm.errors.supplier_id" />
            </div>

            <div class="col-span-12 flex flex-col justify-center md:col-span-6 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': invalidField('is_posted') }">
                {{ t('views.purchase_invoice.fields.is_posted') }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input v-model="purchaseInvoiceForm.is_posted" type="checkbox" />
              </FormSwitch>
              <FormErrorMessages :messages="purchaseInvoiceForm.errors.is_posted" />
            </div>

            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': invalidField('remarks') }">
                {{ t('views.purchase_invoice.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                v-model="purchaseInvoiceForm.remarks"
                :class="{ 'border-danger': invalidField('remarks') }"
                :placeholder="t('views.purchase_invoice.fields.remarks')"
                @change="validateField('remarks')"
              />
              <FormErrorMessages :messages="purchaseInvoiceForm.errors.remarks" />
            </div>
          </div>

          <div
            v-if="!purchaseInvoiceForm.purchase_order_id"
            class="mt-4 rounded-md border border-warning/30 bg-warning/10 px-4 py-3 text-sm text-slate-700 dark:text-slate-200"
          >
            {{ t('views.purchase_invoice.fields.purchase_order_required_hint') }}
          </div>
          <div
            v-else
            class="mt-4 rounded-md border border-primary/20 bg-primary/5 px-4 py-3 text-sm text-slate-700 dark:text-slate-200"
          >
            {{ t('views.purchase_invoice.fields.purchase_order_link_hint') }}
          </div>
        </div>
      </template>

      <!-- card: tax invoice -->
      <template #card-items-2>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('tax_invoice_number') }">
                {{ t('views.purchase_invoice.fields.tax_invoice_number') }}
              </FormLabel>
              <FormInput
                v-model="purchaseInvoiceForm.tax_invoice_number"
                :class="{ 'border-danger': invalidField('tax_invoice_number') }"
                :placeholder="t('views.purchase_invoice.fields.tax_invoice_number')"
                @change="validateField('tax_invoice_number')"
              />
              <FormErrorMessages :messages="purchaseInvoiceForm.errors.tax_invoice_number" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('tax_invoice_vat_base') }">
                {{ t('views.purchase_invoice.fields.tax_invoice_vat_base') }}
              </FormLabel>
              <FormInputCurrency
                v-model="purchaseInvoiceForm.tax_invoice_vat_base"
                :allow-negative="false"
                :class="{ 'border-danger': invalidField('tax_invoice_vat_base') }"
                @change="validateField('tax_invoice_vat_base')"
              />
              <FormErrorMessages :messages="purchaseInvoiceForm.errors.tax_invoice_vat_base" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('tax_invoice_vat') }">
                {{ t('views.purchase_invoice.fields.tax_invoice_vat') }}
              </FormLabel>
              <FormInputCurrency
                v-model="purchaseInvoiceForm.tax_invoice_vat"
                :allow-negative="false"
                :class="{ 'border-danger': invalidField('tax_invoice_vat') }"
                @change="validateField('tax_invoice_vat')"
              />
              <FormErrorMessages :messages="purchaseInvoiceForm.errors.tax_invoice_vat" />
            </div>
          </div>
        </div>
      </template>

      <!-- card: items -->
      <template #card-items-3>
        <div class="space-y-4 p-5">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="text-sm text-slate-500">
              {{ t('views.purchase_invoice.fields.item_count') }}: {{ purchaseInvoiceItemsForm.length }}
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <Button
                v-if="purchaseInvoiceForm.purchase_order_id"
                type="button"
                variant="outline-secondary"
                @click="reloadItemsFromPurchaseOrder"
              >
                <Lucide icon="RefreshCw" class="mr-1 h-4 w-4" />
                {{ t('components.buttons.reload') }}
              </Button>
              <Button type="button" variant="outline-primary" @click="openAddProductUnit">
                <Lucide icon="Plus" class="mr-1 h-4 w-4" />
                {{ t('components.buttons.add') }}
              </Button>
            </div>
          </div>

          <FormErrorMessages :messages="purchaseInvoiceForm.errors.items" />

          <div
            v-if="purchaseInvoiceItemsForm.length === 0"
            class="rounded-md border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500 dark:border-darkmode-400"
          >
            {{ t('views.purchase_invoice.fields.items_empty') }}
          </div>

          <div
            v-for="(item, index) in purchaseInvoiceItemsForm"
            :key="`invoice-item-${item.product_unit_id}-${index}`"
            class="rounded-md border border-slate-200/70 p-4 dark:border-darkmode-400"
          >
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div class="flex items-start gap-3">
                <ProductImagePreview
                  :image-url="item.product_unit_product_image_url"
                  wrapper-class="w-14 h-14 rounded-md overflow-hidden bg-slate-100 dark:bg-darkmode-600 flex items-center justify-center cursor-zoom-in shrink-0"
                  icon-class="w-5 h-5 text-slate-400"
                  :preview-title="item.product_unit_product_name || t('views.purchase_invoice.fields.product_unit_id')"
                />
                <div>
                  <div class="text-sm font-medium text-slate-900 dark:text-slate-100">
                    {{ item.product_unit_product_name || '-' }}
                  </div>
                  <div class="text-xs text-slate-500">
                    <span v-if="item.product_unit_product_code">[{{ item.product_unit_product_code }}]</span>
                    <span v-if="item.product_unit_unit_name">
                      {{ item.product_unit_product_code ? ' - ' : '' }}{{ item.product_unit_unit_name }}
                    </span>
                    <span v-if="item.product_unit_base_unit_name">/ {{ item.product_unit_base_unit_name }}</span>
                  </div>
                  <div v-if="item.purchase_order_item_label" class="mt-1 text-xs text-primary">
                    {{ item.purchase_order_item_label }}
                  </div>
                  <div v-if="item.purchase_order_item_qty_to_invoice_base !== null" class="mt-1 text-xs text-slate-500">
                    {{ t('views.purchase_invoice.fields.qty_to_invoice_hint') }}:
                    {{ formatQuantityValue(item.purchase_order_item_qty_to_invoice_base) }}
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <Button type="button" size="sm" variant="outline-secondary" @click="openChangeProductUnit(index)">
                  <Lucide icon="Search" class="h-4 w-4" />
                </Button>
                <Button type="button" size="sm" variant="outline-secondary" @click="removeItem(index)">
                  <Lucide icon="Trash2" class="h-4 w-4 text-danger" />
                </Button>
              </div>
            </div>

            <div class="mt-4 grid grid-cols-12 gap-4 gap-y-3">
              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel :class="{ 'text-danger': invalidField(`items.${index}.qty`) }">
                  {{ t('views.purchase_invoice.fields.qty') }}
                </FormLabel>
                <FormInputCurrency
                  :id="`purchase-invoice-item-qty-${index}`"
                  v-model="item.qty"
                  :allow-negative="false"
                  :class="{ 'border-danger': invalidField(`items.${index}.qty`) }"
                  @change="validateField(`items.${index}.qty`)"
                />
                <FormErrorMessages :messages="getFieldErrors(`items.${index}.qty`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel>{{ t('views.purchase_invoice.fields.product_unit_conversion_value') }}</FormLabel>
                <FormInputCurrency :model-value="item.product_unit_conversion_value" readonly />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel :class="{ 'text-danger': invalidField(`items.${index}.product_unit_price`) }">
                  {{ t('views.purchase_invoice.fields.product_unit_price') }}
                </FormLabel>
                <FormInputCurrency
                  v-model="item.product_unit_price"
                  :allow-negative="false"
                  :class="{ 'border-danger': invalidField(`items.${index}.product_unit_price`) }"
                  @change="validateField(`items.${index}.product_unit_price`)"
                />
                <FormErrorMessages :messages="getFieldErrors(`items.${index}.product_unit_price`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel :class="{ 'text-danger': invalidField(`items.${index}.price_discount`) }">
                  {{ t('views.purchase_invoice.fields.price_discount') }}
                </FormLabel>
                <FormInputCurrency
                  v-model="item.price_discount"
                  :allow-negative="false"
                  :class="{ 'border-danger': invalidField(`items.${index}.price_discount`) }"
                  @change="validateField(`items.${index}.price_discount`)"
                />
                <FormErrorMessages :messages="getFieldErrors(`items.${index}.price_discount`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel :class="{ 'text-danger': invalidField(`items.${index}.subtotal_discount`) }">
                  {{ t('views.purchase_invoice.fields.subtotal_discount') }}
                </FormLabel>
                <FormInputCurrency
                  v-model="item.subtotal_discount"
                  :allow-negative="false"
                  :class="{ 'border-danger': invalidField(`items.${index}.subtotal_discount`) }"
                  @change="validateField(`items.${index}.subtotal_discount`)"
                />
                <FormErrorMessages :messages="getFieldErrors(`items.${index}.subtotal_discount`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel>{{ t('views.purchase_invoice.fields.subtotal_after_discount') }}</FormLabel>
                <FormInputCurrency
                  :model-value="formatCurrencyPreviewValue(getItemSubtotalAfterDiscountPreview(item))"
                  readonly
                />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-3">
                <FormLabel :class="{ 'text-danger': invalidField(`items.${index}.vat_profile_id`) }">
                  {{ t('views.purchase_invoice.fields.vat_profile_id') }}
                </FormLabel>
                <FormSelectSearch
                  v-model="item.vat_profile_id"
                  v-model:search="vatProfileSearch"
                  :options="vatProfileOptions"
                  :placeholder="t('components.dropdown.placeholder')"
                  :class="{ 'border-danger': invalidField(`items.${index}.vat_profile_id`) }"
                  @change="syncVatProfile(index)"
                  @search="loadVatProfileDDL"
                  @clear="clearVatProfile(index)"
                />
                <FormErrorMessages :messages="getFieldErrors(`items.${index}.vat_profile_id`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel>{{ t('views.purchase_invoice.fields.vat_rate') }}</FormLabel>
                <FormInputCurrency :model-value="item.vat_rate" readonly />
              </div>

              <div class="col-span-12 flex flex-col justify-center md:col-span-6 lg:col-span-2">
                <FormLabel>{{ t('views.purchase_invoice.fields.product_unit_is_price_include_vat') }}</FormLabel>
                <FormSwitch>
                  <FormSwitch.Input
                    v-model="item.product_unit_is_price_include_vat"
                    type="checkbox"
                    @change="validateField(`items.${index}.product_unit_is_price_include_vat`)"
                  />
                </FormSwitch>
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel>{{ t('views.purchase_invoice.fields.vat') }}</FormLabel>
                <FormInputCurrency :model-value="formatCurrencyPreviewValue(getItemVatPreview(item, index))" readonly />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-3">
                <FormLabel>{{ t('views.purchase_invoice.fields.item_amount_payable') }}</FormLabel>
                <FormInputCurrency
                  :model-value="formatCurrencyPreviewValue(getItemAmountPayablePreview(item))"
                  readonly
                />
              </div>

              <div class="col-span-12">
                <FormLabel :class="{ 'text-danger': invalidField(`items.${index}.remarks`) }">
                  {{ t('views.purchase_invoice.fields.remarks') }}
                </FormLabel>
                <FormTextarea
                  v-model="item.remarks"
                  :class="{ 'border-danger': invalidField(`items.${index}.remarks`) }"
                  :placeholder="t('views.purchase_invoice.fields.remarks')"
                  @change="validateField(`items.${index}.remarks`)"
                />
                <FormErrorMessages :messages="getFieldErrors(`items.${index}.remarks`)" />
                <FormErrorMessages :messages="getFieldErrors(`items.${index}.purchase_order_item_id`)" />
                <FormErrorMessages :messages="getFieldErrors(`items.${index}.product_unit_id`)" />
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- card: summary and payments -->
      <template #card-items-4>
        <div class="space-y-4 p-5">
          <div class="space-y-4 rounded-md border border-slate-200/60 p-4 dark:border-darkmode-400">
            <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
              <div class="col-span-12 lg:col-span-9"></div>
              <div class="col-span-12 lg:col-span-3">
                <FormLabel>{{ t('views.purchase_invoice.fields.items_subtotal_after_discount') }}</FormLabel>
                <FormInputCurrency
                  :model-value="formatCurrencyPreviewValue(getItemsSubtotalAfterDiscountPreview())"
                  readonly
                />
              </div>
            </div>

            <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
              <div class="col-span-12 lg:col-span-9"></div>
              <div class="col-span-12 lg:col-span-3">
                <FormLabel :class="{ 'text-danger': invalidField('global_discount') }">
                  {{ t('views.purchase_invoice.fields.global_discount') }}
                </FormLabel>
                <FormInputCurrency
                  v-model="purchaseInvoiceForm.global_discount"
                  :allow-negative="false"
                  :class="{ 'border-danger': invalidField('global_discount') }"
                  @change="validateField('global_discount')"
                />
                <FormErrorMessages :messages="purchaseInvoiceForm.errors.global_discount" />
              </div>
            </div>

            <template v-if="isTotalsBreakdownExpanded">
              <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.purchase_invoice.fields.item_total_after_global_discount') }}</FormLabel>
                  <FormInputCurrency
                    :model-value="formatCurrencyPreviewValue(getItemTotalAfterGlobalDiscountPreview())"
                    readonly
                  />
                </div>
              </div>

              <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.purchase_invoice.fields.vat_base') }}</FormLabel>
                  <FormInputCurrency :model-value="formatCurrencyPreviewValue(getVatBasePreview())" readonly />
                </div>
              </div>

              <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.purchase_invoice.fields.vat') }}</FormLabel>
                  <FormInputCurrency :model-value="formatCurrencyPreviewValue(getVatPreview())" readonly />
                </div>
              </div>

              <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': invalidField('rounding') }">
                    {{ t('views.purchase_invoice.fields.rounding') }}
                  </FormLabel>
                  <FormInputCurrency
                    v-model="purchaseInvoiceForm.rounding"
                    :class="{ 'border-danger': invalidField('rounding') }"
                    @change="validateField('rounding')"
                  />
                  <FormErrorMessages :messages="purchaseInvoiceForm.errors.rounding" />
                </div>
              </div>
            </template>

            <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
              <div class="col-span-12 lg:col-span-9"></div>
              <div class="col-span-12 lg:col-span-3">
                <FormLabel>{{ t('views.purchase_invoice.fields.amount_payable_preview') }}</FormLabel>
                <div class="flex items-start gap-2">
                  <div class="shrink-0">
                    <Button
                      type="button"
                      variant="outline-secondary"
                      class="flex h-[38px] w-[38px] min-w-0 items-center justify-center"
                      @click="isTotalsBreakdownExpanded = !isTotalsBreakdownExpanded"
                    >
                      {{ isTotalsBreakdownExpanded ? '▲' : '▼' }}
                    </Button>
                  </div>
                  <div class="min-w-0 flex-1">
                    <FormInputCurrency :model-value="formatCurrencyPreviewValue(getAmountPayablePreview())" readonly />
                  </div>
                </div>
              </div>
            </div>

            <!-- payments editor -->
            <div class="space-y-4">
              <div v-if="isPaymentEditorExpanded" class="space-y-4">
                <FormErrorMessages :messages="purchaseInvoiceForm.errors.payments" />

                <div v-if="purchaseInvoicePaymentsForm.length === 0" class="text-right text-sm text-slate-500">
                  {{ t('views.purchase_invoice.fields.payments_empty') }}
                </div>

                <div v-else class="space-y-4">
                  <div
                    v-for="(payment, index) in purchaseInvoicePaymentsForm"
                    :key="`invoice-payment-${index}`"
                    class="rounded-md border border-slate-200/70 p-4 dark:border-darkmode-400"
                  >
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-6 lg:col-span-2">
                        <FormLabel :class="{ 'text-danger': invalidField(`payments.${index}.code`) }">
                          {{ t('views.purchase_invoice.fields.code') }}
                        </FormLabel>
                        <FormInputCode
                          v-model="payment.code"
                          :class="{ 'border-danger': invalidField(`payments.${index}.code`) }"
                          :placeholder="t('views.purchase_invoice.fields.code')"
                          @set-auto="setPaymentCode(index)"
                          @change="validateField(`payments.${index}.code`)"
                        />
                        <FormErrorMessages :messages="getFieldErrors(`payments.${index}.code`)" />
                      </div>

                      <div class="col-span-12 md:col-span-6 lg:col-span-3">
                        <FormLabel :class="{ 'text-danger': invalidField(`payments.${index}.date`) }">
                          {{ t('views.purchase_invoice.fields.date') }}
                        </FormLabel>
                        <FormInputDateTimeAuto
                          v-model="payment.date"
                          :class="{ 'border-danger': invalidField(`payments.${index}.date`) }"
                          :placeholder="t('views.purchase_invoice.fields.date')"
                          @change="validateField(`payments.${index}.date`)"
                        />
                        <FormErrorMessages :messages="getFieldErrors(`payments.${index}.date`)" />
                      </div>

                      <div class="col-span-12 md:col-span-6 lg:col-span-3">
                        <FormLabel :class="{ 'text-danger': invalidField(`payments.${index}.payment_type`) }">
                          {{ t('views.purchase_invoice.fields.payment_type') }}
                        </FormLabel>
                        <FormSelect
                          v-model="payment.payment_type"
                          :class="{ 'border-danger': invalidField(`payments.${index}.payment_type`) }"
                          @change="resetPaymentReferences(index)"
                        >
                          <option v-for="option in paymentTypeOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                          </option>
                        </FormSelect>
                        <FormErrorMessages :messages="getFieldErrors(`payments.${index}.payment_type`)" />
                      </div>

                      <div class="col-span-12 md:col-span-6 lg:col-span-2">
                        <FormLabel :class="{ 'text-danger': invalidField(`payments.${index}.amount`) }">
                          {{ t('views.purchase_invoice.fields.amount') }}
                        </FormLabel>
                        <FormInputCurrency
                          v-model="payment.amount"
                          :allow-negative="false"
                          :class="{ 'border-danger': invalidField(`payments.${index}.amount`) }"
                          @change="validateField(`payments.${index}.amount`)"
                        />
                        <FormErrorMessages :messages="getFieldErrors(`payments.${index}.amount`)" />
                      </div>

                      <div class="col-span-12 flex items-end justify-end md:col-span-6 lg:col-span-2">
                        <Button
                          type="button"
                          variant="outline-secondary"
                          class="flex h-[38px] w-[38px] min-w-0 items-center justify-center"
                          @click="removePayment(index)"
                        >
                          <Lucide icon="Trash2" class="h-4 w-4 text-danger" />
                        </Button>
                      </div>

                      <!-- only the field of the selected payment type is shown and submitted -->
                      <div v-if="payment.payment_type === 'cash'" class="col-span-12 md:col-span-6 lg:col-span-5">
                        <FormLabel :class="{ 'text-danger': invalidField(`payments.${index}.cash_account_id`) }">
                          {{ t('views.purchase_invoice.fields.cash_account_id') }}
                        </FormLabel>
                        <FormSelectSearch
                          v-model="payment.cash_account_id"
                          v-model:search="cashAccountSearch"
                          :options="cashAccountOptions"
                          :placeholder="t('components.dropdown.placeholder')"
                          :class="{ 'border-danger': invalidField(`payments.${index}.cash_account_id`) }"
                          @change="validateField(`payments.${index}.cash_account_id`)"
                          @search="loadCashAccountDDL"
                          @clear="
                            () => {
                              payment.cash_account_id = null;
                              validateField(`payments.${index}.cash_account_id`);
                            }
                          "
                        />
                        <FormErrorMessages :messages="getFieldErrors(`payments.${index}.cash_account_id`)" />
                      </div>

                      <div
                        v-else-if="payment.payment_type === 'down_payment'"
                        class="col-span-12 md:col-span-6 lg:col-span-5"
                      >
                        <FormLabel
                          :class="{ 'text-danger': invalidField(`payments.${index}.purchase_order_payment_id`) }"
                        >
                          {{ t('views.purchase_invoice.fields.purchase_order_payment_id') }}
                        </FormLabel>
                        <FormSelectSearch
                          v-model="payment.purchase_order_payment_id"
                          :options="purchaseOrderPaymentOptions"
                          :placeholder="t('components.dropdown.placeholder')"
                          :class="{ 'border-danger': invalidField(`payments.${index}.purchase_order_payment_id`) }"
                          @change="validateField(`payments.${index}.purchase_order_payment_id`)"
                          @clear="
                            () => {
                              payment.purchase_order_payment_id = null;
                              validateField(`payments.${index}.purchase_order_payment_id`);
                            }
                          "
                        />
                        <FormErrorMessages :messages="getFieldErrors(`payments.${index}.purchase_order_payment_id`)" />
                      </div>

                      <div v-else class="col-span-12 md:col-span-6 lg:col-span-5">
                        <FormLabel :class="{ 'text-danger': invalidField(`payments.${index}.purchase_return_id`) }">
                          {{ t('views.purchase_invoice.fields.purchase_return_id') }}
                        </FormLabel>
                        <FormSelectSearch
                          v-model="payment.purchase_return_id"
                          v-model:search="purchaseReturnSearch"
                          :options="purchaseReturnOptions"
                          :placeholder="t('components.dropdown.placeholder')"
                          :class="{ 'border-danger': invalidField(`payments.${index}.purchase_return_id`) }"
                          @change="validateField(`payments.${index}.purchase_return_id`)"
                          @search="loadPurchaseReturnDDL"
                          @clear="
                            () => {
                              payment.purchase_return_id = null;
                              validateField(`payments.${index}.purchase_return_id`);
                            }
                          "
                        />
                        <FormErrorMessages :messages="getFieldErrors(`payments.${index}.purchase_return_id`)" />
                      </div>

                      <div
                        v-if="getPaymentAvailableAmount(payment) !== null"
                        class="col-span-12 md:col-span-6 lg:col-span-2"
                      >
                        <FormLabel>{{ t('views.purchase_invoice.fields.available_amount') }}</FormLabel>
                        <FormInputCurrency :model-value="getPaymentAvailableAmount(payment) ?? 0" readonly />
                      </div>

                      <div class="col-span-12 lg:col-span-5">
                        <FormLabel :class="{ 'text-danger': invalidField(`payments.${index}.remarks`) }">
                          {{ t('views.purchase_invoice.fields.remarks') }}
                        </FormLabel>
                        <FormInput
                          v-model="payment.remarks"
                          :class="{ 'border-danger': invalidField(`payments.${index}.remarks`) }"
                          :placeholder="t('views.purchase_invoice.fields.remarks')"
                          @change="validateField(`payments.${index}.remarks`)"
                        />
                        <FormErrorMessages :messages="getFieldErrors(`payments.${index}.remarks`)" />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="flex items-center justify-between gap-2">
                  <div class="text-xs text-slate-500">
                    {{ t('views.purchase_invoice.fields.payment_type_hint') }}
                  </div>
                  <Button type="button" variant="outline-primary" @click="addPayment">
                    <Lucide icon="Plus" class="mr-1 h-4 w-4" />
                    {{ t('components.buttons.create_new') }}
                  </Button>
                </div>
              </div>

              <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.purchase_invoice.fields.payments_total') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="shrink-0">
                      <Button
                        type="button"
                        variant="outline-secondary"
                        class="flex h-[38px] w-[38px] min-w-0 items-center justify-center"
                        @click="isPaymentEditorExpanded = !isPaymentEditorExpanded"
                      >
                        {{ isPaymentEditorExpanded ? '▲' : '▼' }}
                      </Button>
                    </div>
                    <div class="min-w-0 flex-1">
                      <FormInputCurrency
                        :model-value="formatCurrencyPreviewValue(getPaymentsTotalPreview())"
                        readonly
                      />
                    </div>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.purchase_invoice.fields.amount_due') }}</FormLabel>
                  <FormInputCurrency :model-value="formatCurrencyPreviewValue(getAmountDuePreview())" readonly />
                </div>
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
            :disabled="purchaseInvoiceForm.validating || purchaseInvoiceForm.hasErrors"
          >
            <Lucide v-if="purchaseInvoiceForm.validating" icon="Loader" class="mr-2 h-4 w-4 animate-spin" />
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
    size="xl"
    panel-class="max-w-5xl"
    :open="showProductUnitModal"
    :title="t('views.purchase_invoice.fields.product_unit_id')"
    :is-searching="isSearchingProductUnit"
    :options="productUnitOptions"
    :columns="productUnitDialogColumns"
    @close="showProductUnitModal = false"
    @search="searchProductUnits"
    @select="selectProductUnit($event as ProductUnitOption)"
    @after-leave="handleProductUnitModalAfterLeave"
  />
</template>
