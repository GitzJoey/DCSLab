<script setup lang="ts">
// #region Imports
import { computed, nextTick, onMounted, onUnmounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
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
  FormSelect,
  FormSelectSearch,
  FormSwitch,
  FormTextarea,
} from '@/components/Base/Form';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import ProductImagePreview from '@/components/Product/ProductImagePreview.vue';
import ProductUnitPickerDialog from '@/components/Product/ProductUnitPickerDialog.vue';
import CashAccountService from '@/services/CashAccountService';
import ProductService from '@/services/ProductService';
import SalesInvoiceService from '@/services/SalesInvoiceService';
import SalesOrderService from '@/services/SalesOrderService';
import SalesReturnService from '@/services/SalesReturnService';
import VatProfileService from '@/services/VatProfileService';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { DropDownOption } from '@/types/models/DropDownOption';
import { NotificationData } from '@/types/models/NotificationData';
import { SalesInvoice } from '@/types/models/SalesInvoice';
import { SalesOrder } from '@/types/models/SalesOrder';
import {
  SalesInvoiceItemNestedUpdateRequest,
  SalesInvoicePaymentNestedUpdateRequest,
} from '@/types/services/sales-invoice/SalesInvoiceRequest';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { convertErrorTypeToAlertListType, formatCurrency, formatDate } from '@/utils/helper';
// #endregion

// #region Declarations
type SalesInvoiceItemFormItem = SalesInvoiceItemNestedUpdateRequest & {
  product_unit_product_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_product_image_url?: string | null;
  product_unit_unit_name?: string | null;
  product_unit_base_unit_name?: string | null;
  vat_profile_name?: string | null;
  sales_order_item_label?: string | null;
  sales_order_item_qty_remaining_base?: number | null;
  sales_order_item_qty_invoiced_base?: number | null;
};

type SalesInvoicePaymentFormItem = SalesInvoicePaymentNestedUpdateRequest;

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

type SalesOrderOption = DropDownOption & {
  ulid: string;
  customer_id: string | null;
  customer_name: string | null;
};

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits([
  'mode-state',
  'loading-state',
  'update-profile',
  'show-alertplaceholder',
  'show-notification',
]);

const salesInvoiceService = new SalesInvoiceService();
const salesReturnService = new SalesReturnService();
const vatProfileService = new VatProfileService();
const cashAccountService = new CashAccountService();
const productService = new ProductService();
const salesOrderService = new SalesOrderService();

const salesInvoiceForm = salesInvoiceService.useSalesInvoiceEditForm(route.params.ulid as string);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const salesInvoiceData = ref<SalesInvoice | null>(null);

const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'views.sales_invoice.field_groups.company_info', state: CardState.Expanded },
  { title: 'views.sales_invoice.field_groups.sales_invoice_data', state: CardState.Expanded },
  { title: 'views.sales_invoice.field_groups.items', state: CardState.Expanded },
  { title: 'views.sales_invoice.field_groups.summary', state: CardState.Expanded },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const salesOrderDDL = ref<Array<SalesOrderOption>>([]);
const salesOrderSearch = ref<string>('');
const salesOrderOptions = computed(() =>
  salesOrderDDL.value.map((item) => ({
    value: item.code,
    label: item.name,
  })),
);
const selectedSalesOrderData = ref<SalesOrder | null>(null);

const vatProfileDDL = ref<Array<VatProfileOption> | null>(null);
const vatProfileSearch = ref<string>('');
const vatProfileOptions = computed(() =>
  (vatProfileDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const cashAccountDDL = ref<Array<DropDownOption> | null>(null);
const cashAccountSearch = ref<string>('');
const cashAccountOptions = computed(() =>
  (cashAccountDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const salesReturnDDL = ref<Array<DropDownOption>>([]);
const salesReturnSearch = ref<string>('');
const salesReturnOptions = computed(() =>
  salesReturnDDL.value.map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const salesOrderPaymentOptions = computed(() =>
  ((selectedSalesOrderData.value?.payments ?? []) as Array<any>).map((payment: any) => {
    const available = Math.max(Number(payment.amount ?? 0) - Number(payment.amount_allocated ?? 0), 0);
    return {
      value: payment.id as string,
      label: `${payment.code} - ${t('views.sales_invoice.fields.available_amount')} ${formatCurrency(available)}`,
    };
  }),
);

const paymentTypeOptions = computed(() => [
  { value: 'cash', label: t('views.sales_invoice.filters.payment_type_cash') },
  { value: 'down_payment', label: t('views.sales_invoice.filters.payment_type_down_payment') },
  { value: 'return', label: t('views.sales_invoice.filters.payment_type_return') },
]);

const showProductUnitModal = ref<boolean>(false);
const productSearchText = ref<string>('');
const isSearchingProductUnit = ref<boolean>(false);
const productUnitOptions = ref<Array<ProductUnitOption>>([]);
const editingProductUnitIndex = ref<number | null>(null);
const productUnitQtyToFocus = ref<number | null>(null);
const salesInvoiceItemDetailsExpanded = ref<boolean[]>([]);
const viewportWidth = ref<number>(window.innerWidth);

const isTotalsBreakdownExpanded = ref(false);
const isPaymentEditorExpanded = ref(false);

const productUnitDialogColumns = [
  {
    key: 'unit_name',
    label: t('views.product.table.cols.unit'),
  },
  {
    key: 'conversion_value',
    label: t('views.sales_invoice.fields.product_unit_conversion_value'),
    align: 'right' as const,
    formatter: 'number' as const,
  },
  {
    key: 'price',
    label: t('views.sales_invoice.fields.product_unit_price'),
    align: 'right' as const,
    formatter: 'number' as const,
  },
];

const currentItemLayout = computed<'sm' | 'md' | 'lg'>(() => {
  if (viewportWidth.value >= 1024) return 'lg';
  if (viewportWidth.value >= 768) return 'md';
  return 'sm';
});

const salesInvoiceItemsForm = computed<SalesInvoiceItemFormItem[]>(
  () => salesInvoiceForm.items as SalesInvoiceItemFormItem[],
);
const salesInvoicePaymentsForm = computed<SalesInvoicePaymentFormItem[]>(
  () => salesInvoiceForm.payments as SalesInvoicePaymentFormItem[],
);

const invalidSalesInvoiceField = (field: string) => salesInvoiceForm.invalid(field as any);
const validateSalesInvoiceField = (field: string) => salesInvoiceForm.validate(field as any);
const getSalesInvoiceFieldErrors = (field: string) =>
  (salesInvoiceForm.errors as Record<string, string | undefined>)[field];

const handleExpandCard = (index: number) => {
  cards.value[index].state =
    cards.value[index].state === CardState.Collapsed ? CardState.Expanded : CardState.Collapsed;
};

const syncViewportWidth = () => {
  viewportWidth.value = window.innerWidth;
};
// #endregion

// #region Vue Core
onMounted(async () => {
  syncViewportWidth();
  window.addEventListener('resize', syncViewportWidth);
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
    await Promise.all([loadSalesOrderDDL(), loadVatProfileDDL(), loadCashAccountDDL()]);
    await loadData();
    await Promise.all([
      loadSalesOrderDDL(),
      loadVatProfileDDL(),
      loadCashAccountDDL(),
      loadSalesReturnDDL(),
    ]);
  } finally {
    emits('loading-state', false);
  }
});

onUnmounted(() => {
  window.removeEventListener('resize', syncViewportWidth);
});
// #endregion

// #region Methods - Helpers
const appendDDL = (
  target: typeof cashAccountDDL,
  item?: { id: string; name?: string | null; code?: string | null } | null,
) => {
  if (!item?.id) return;
  const current = target.value ?? [];
  if (current.find((entry) => entry.code === item.id)) return;
  current.push({
    code: item.id,
    name: item.name ?? item.code ?? item.id,
  });
  target.value = [...current];
};

const setCode = () => {
  salesInvoiceForm.forgetError('code');
  salesInvoiceForm.setData({
    code: salesInvoiceForm.code === '_AUTO_' ? '' : '_AUTO_',
  });
};

const setPaymentCode = (index: number) => {
  salesInvoiceForm.forgetError(`payments.${index}.code` as any);
  salesInvoicePaymentsForm.value[index].code =
    salesInvoicePaymentsForm.value[index].code === '_AUTO_' ? '' : '_AUTO_';
};

const clearItemErrors = () => {
  Object.keys(salesInvoiceForm.errors).forEach((key) => {
    if (key.startsWith('items.')) {
      salesInvoiceForm.forgetError(key as any);
    }
  });
};

const clearPaymentErrors = () => {
  Object.keys(salesInvoiceForm.errors).forEach((key) => {
    if (key.startsWith('payments')) {
      salesInvoiceForm.forgetError(key as any);
    }
  });
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

const applyVatProfileToItem = (item: SalesInvoiceItemFormItem, vatProfileId: string | null) => {
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
  const item = salesInvoiceItemsForm.value[index];
  if (!item) return;
  applyVatProfileToItem(item, null);
  salesInvoiceForm.validate(`items.${index}.vat_profile_id` as any);
};

const syncVatProfile = (index: number) => {
  const item = salesInvoiceItemsForm.value[index];
  if (!item) return;
  applyVatProfileToItem(item, item.vat_profile_id ?? null);
  salesInvoiceForm.validate(`items.${index}.vat_profile_id` as any);
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
const loadSalesOrderDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await salesOrderService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: null,
    end_date: null,
    customer_id: null,
    progress_status: null,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    // only sales orders that already have a customer may be invoiced
    salesOrderDDL.value = (result.data.data as Array<any>)
      .filter((item: any) => Boolean(item.customer?.id))
      .map((item: any) => ({
        code: item.id,
        name: `${item.code} - ${item.customer?.name ?? '-'}`,
        ulid: item.ulid,
        customer_id: item.customer?.id ?? null,
        customer_name: item.customer?.name ?? null,
      }));
  } else {
    salesOrderDDL.value = [];
  }

  appendSalesOrderOption(selectedSalesOrderData.value ?? salesInvoiceData.value?.sales_order);
};

const appendSalesOrderOption = (salesOrder: any | null | undefined) => {
  if (!salesOrder?.id || !salesOrder.ulid) return;
  if (salesOrderDDL.value.some((item) => item.code === salesOrder.id)) return;

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

  (salesInvoiceData.value?.items ?? []).forEach((item) => appendVatProfileOption({
    id: item.vat_profile?.id ?? null,
    name: item.vat_profile?.name ?? null,
    vat_rate: Number(item.vat_rate ?? 0),
    vat_base_numerator: Number(item.vat_base_numerator ?? 1),
    vat_base_denominator: Number(item.vat_base_denominator ?? 1),
  }));

  salesInvoiceItemsForm.value.forEach((item) => {
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

  (salesInvoiceData.value?.payments ?? []).forEach((payment) => appendDDL(cashAccountDDL, payment.cash_account));
};

const loadSalesReturnDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await salesReturnService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    customer_id: (salesInvoiceForm.customer_id as string | null) ?? undefined,
    refresh: false,
    limit: 100,
  });

  const options: Array<DropDownOption> = [];

  if (result.success && result.data) {
    // only invoiced returns can settle an invoice, and only when something is still available
    (result.data.data as Array<any>)
      .filter((item: any) => Boolean(item.sales_invoice?.id) && Number(item.amount_available ?? 0) > 0)
      .forEach((item: any) => {
        options.push({
          code: item.id,
          name: `${item.code} - ${t('views.sales_invoice.fields.available_amount')} ${formatCurrency(Number(item.amount_available ?? 0))}`,
        });
      });
  }

  // keep returns already referenced by this invoice resolvable in the dropdown
  (salesInvoiceData.value?.payments ?? []).forEach((payment) => {
    const salesReturn = payment.sales_return;
    if (!salesReturn?.id) return;
    if (options.some((option) => option.code === salesReturn.id)) return;
    options.push({ code: salesReturn.id, name: salesReturn.code });
  });

  salesReturnDDL.value = options;
};
// #endregion

// #region Methods - Load data
const loadData = async () => {
  const ulid = route.params.ulid as string | undefined;
  if (!ulid) return;

  const result = await salesInvoiceService.read(ulid);
  if (!result.success || !result.data) return;

  const data = result.data;
  salesInvoiceData.value = data;

  if (data.sales_order?.ulid) {
    const salesOrderResult = await salesOrderService.read(data.sales_order.ulid);
    if (salesOrderResult.success && salesOrderResult.data) {
      selectedSalesOrderData.value = salesOrderResult.data;
      appendSalesOrderOption(salesOrderResult.data);
    }
  }

  const salesOrderItemsById = new Map<string, any>();
  ((selectedSalesOrderData.value?.items ?? []) as Array<any>).forEach((salesOrderItem: any) => {
    if (salesOrderItem?.id) salesOrderItemsById.set(salesOrderItem.id as string, salesOrderItem);
  });

  const items: SalesInvoiceItemFormItem[] = (data.items ?? []).map((item: any) => {
    const linkedSalesOrderItem = item.sales_order_item?.id
      ? salesOrderItemsById.get(item.sales_order_item.id) ?? item.sales_order_item
      : null;
    const remainingBase = linkedSalesOrderItem
      ? Math.max(
        Number(linkedSalesOrderItem.product_unit_qty_base ?? 0) - Number(linkedSalesOrderItem.qty_invoiced_base ?? 0),
        0,
      )
      : null;

    return {
      id: item.id ?? null,
      qty: item.qty,
      product_unit_id: item.product_unit?.id ?? '',
      product_unit_conversion_value: item.product_unit_conversion_value,
      product_unit_price: item.product_unit_price,
      product_unit_is_price_include_vat: item.product_unit_is_price_include_vat,
      price_discount: Number(item.price_discount ?? 0),
      subtotal_discount: Number(item.subtotal_discount ?? 0),
      vat_profile_id: item.vat_profile?.id ?? null,
      vat_rate: Number(item.vat_rate ?? 0),
      vat_base_numerator: item.vat_base_numerator,
      vat_base_denominator: item.vat_base_denominator,
      sales_order_item_id: item.sales_order_item?.id ?? null,
      remarks: item.remarks ?? '',
      product_unit_product_code: item.product_unit?.code ?? '',
      product_unit_product_name: item.product_unit?.product?.name ?? '',
      product_unit_product_image_url: item.product_unit?.product?.main_product_image?.url ?? null,
      product_unit_unit_name: item.product_unit?.unit?.name ?? '',
      product_unit_base_unit_name: Number(item.product_unit_conversion_value ?? 1) !== 1
        ? item.product_unit?.product?.base_product_unit?.unit?.name ?? ''
        : '',
      vat_profile_name: item.vat_profile?.name ?? null,
      sales_order_item_label: item.sales_order_item?.id
        ? `${item.product_unit?.code ? `[${item.product_unit.code}] ` : ''}${item.product_unit?.product?.name ?? '-'}`
        : null,
      sales_order_item_qty_remaining_base: remainingBase,
      sales_order_item_qty_invoiced_base: linkedSalesOrderItem
        ? Number(linkedSalesOrderItem.qty_invoiced_base ?? 0)
        : null,
    };
  });

  const payments: SalesInvoicePaymentFormItem[] = (data.payments ?? []).map((payment: any) => ({
    id: payment.id ?? null,
    code: payment.code,
    date: formatDate(payment.date, 'YYYY-MM-DD HH:mm:ss'),
    payment_type: payment.payment_type ?? 'cash',
    cash_account_id: payment.cash_account?.id ?? null,
    sales_order_payment_id: payment.sales_order_payment?.id ?? null,
    sales_return_id: payment.sales_return?.id ?? null,
    amount: payment.amount,
    remarks: payment.remarks ?? '',
  }));

  salesInvoiceForm.setData({
    company_id: data.company?.id ?? selectedUserLocation.value.company.id,
    branch_id: data.branch?.id ?? selectedUserLocation.value.branch.id,
    code: data.code,
    date: formatDate(data.date, 'YYYY-MM-DD HH:mm:ss'),
    due_days: data.due_days,
    customer_id: data.customer?.id ?? null,
    sales_order_id: data.sales_order?.id ?? null,
    tax_invoice_number: data.tax_invoice_number ?? '',
    tax_invoice_vat_base: data.tax_invoice_vat_base ?? 0,
    tax_invoice_vat: data.tax_invoice_vat ?? 0,
    remarks: data.remarks ?? '',
    is_posted: data.is_posted,
    global_discount: data.global_discount ?? 0,
    rounding: data.rounding ?? 0,
    delete_item_ids: [],
    items: items as any,
    delete_payment_ids: [],
    payments: payments as any,
  } as any);

  salesInvoiceItemDetailsExpanded.value = items.map(() => false);
};
// #endregion

// #region Methods - Sales Order linkage
const buildItemFromSalesOrderItem = (salesOrderItem: any): SalesInvoiceItemFormItem => {
  const productUnit = salesOrderItem.product_unit;
  const product = productUnit?.product;
  const conversionValue = Number(salesOrderItem.product_unit_conversion_value ?? productUnit?.conversion_value ?? 1) || 1;
  const qtyBase = Number(salesOrderItem.product_unit_qty_base ?? 0);
  const qtyInvoicedBase = Number(salesOrderItem.qty_invoiced_base ?? 0);
  // prefill from what is still un-invoiced, NOT from the delivery-driven outstanding column
  const remainingBase = Math.max(qtyBase - qtyInvoicedBase, 0);
  const defaultQty = remainingBase > 0
    ? remainingBase / conversionValue
    : Number(salesOrderItem.qty ?? 0);

  appendVatProfileOption({
    id: salesOrderItem.vat_profile?.id ?? null,
    name: salesOrderItem.vat_profile?.name ?? null,
    vat_rate: Number(salesOrderItem.vat_rate ?? 0),
    vat_base_numerator: Number(salesOrderItem.vat_base_numerator ?? 1),
    vat_base_denominator: Number(salesOrderItem.vat_base_denominator ?? 1),
  });

  return {
    id: null,
    qty: defaultQty > 0 ? defaultQty : 1,
    product_unit_id: productUnit?.id ?? '',
    product_unit_conversion_value: conversionValue,
    product_unit_price: Number(salesOrderItem.product_unit_price ?? productUnit?.price ?? 0),
    product_unit_is_price_include_vat: Boolean(salesOrderItem.product_unit_is_price_include_vat),
    price_discount: Number(salesOrderItem.price_discount ?? 0),
    subtotal_discount: Number(salesOrderItem.subtotal_discount ?? 0),
    vat_profile_id: salesOrderItem.vat_profile?.id ?? null,
    vat_rate: Number(salesOrderItem.vat_rate ?? 0),
    vat_base_numerator: Number(salesOrderItem.vat_base_numerator ?? 1),
    vat_base_denominator: Number(salesOrderItem.vat_base_denominator ?? 1),
    sales_order_item_id: salesOrderItem.id ?? null,
    remarks: salesOrderItem.remarks ?? '',
    product_unit_product_code: productUnit?.code ?? '',
    product_unit_product_name: product?.name ?? '-',
    product_unit_product_image_url: product?.main_product_image?.url ?? null,
    product_unit_unit_name: productUnit?.unit?.name ?? '',
    product_unit_base_unit_name: conversionValue !== 1
      ? product?.base_product_unit?.unit?.name ?? ''
      : '',
    vat_profile_name: salesOrderItem.vat_profile?.name ?? null,
    sales_order_item_label: productUnit?.code
      ? `[${productUnit.code}] ${product?.name ?? '-'}`
      : product?.name ?? '-',
    sales_order_item_qty_remaining_base: remainingBase,
    sales_order_item_qty_invoiced_base: qtyInvoicedBase,
  };
};

const syncItemsFromSalesOrder = (salesOrder: any) => {
  // replacing the item set means every persisted row must be flagged for deletion
  salesInvoiceItemsForm.value.forEach((item) => {
    if (item.id) salesInvoiceForm.delete_item_ids.push(item.id);
  });

  const items = ((salesOrder.items ?? []) as Array<any>).map((item: any) => buildItemFromSalesOrderItem(item));

  salesInvoiceForm.setData({
    customer_id: salesOrder.customer?.id ?? null,
    items: items as any,
  });

  salesInvoiceItemDetailsExpanded.value = items.map(() => false);
  clearItemErrors();
};

const loadSalesOrderDetail = async (salesOrderUlid: string) => {
  const result = await salesOrderService.read(salesOrderUlid);

  if (result.success && result.data) {
    selectedSalesOrderData.value = result.data;
    appendSalesOrderOption(result.data);
    return result.data;
  }

  showAlertPlaceholder('danger', '', (result.errors as Record<string, Array<string>>) ?? null);
  return null;
};

const handleSalesOrderChanged = async (salesOrderId: string | number | null) => {
  if (!salesOrderId) {
    await clearSalesOrder();
    return;
  }

  const option = salesOrderDDL.value.find((item) => item.code === salesOrderId);
  if (!option) {
    salesInvoiceForm.validate('sales_order_id');
    return;
  }

  const salesOrder = await loadSalesOrderDetail(option.ulid);
  if (!salesOrder) {
    salesInvoiceForm.validate('sales_order_id');
    return;
  }

  syncItemsFromSalesOrder(salesOrder);
  salesInvoiceForm.forgetError('customer_id');
  salesInvoiceForm.forgetError('branch_id');
  salesInvoiceForm.validate('sales_order_id');
  await loadSalesReturnDDL();
};

const clearSalesOrder = async () => {
  selectedSalesOrderData.value = null;
  salesInvoiceItemsForm.value.forEach((item) => {
    if (item.id) salesInvoiceForm.delete_item_ids.push(item.id);
  });
  salesInvoiceForm.setData({
    sales_order_id: null,
    customer_id: null,
    items: [] as any,
  });
  salesInvoiceItemDetailsExpanded.value = [];
  salesInvoiceForm.forgetError('sales_order_id');
  salesInvoiceForm.forgetError('customer_id');
  clearItemErrors();
  await Promise.all([loadSalesOrderDDL(), loadSalesReturnDDL()]);
};

const reloadItemsFromSalesOrder = async () => {
  if (!salesInvoiceForm.sales_order_id) return;

  const option = salesOrderDDL.value.find((item) => item.code === salesInvoiceForm.sales_order_id);
  if (!option) return;

  const salesOrder = await loadSalesOrderDetail(option.ulid);
  if (!salesOrder) return;

  syncItemsFromSalesOrder(salesOrder);
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

const selectProductUnit = (option: ProductUnitOption) => {
  let vatProfileId: string | null = null;
  let vatProfileName: string | null = null;
  let vatRate = 0;
  let vatBaseNumerator = 1;
  let vatBaseDenominator = 1;

  if (option.vat_profile_id) {
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

  const itemData: SalesInvoiceItemFormItem = {
    id: null,
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
    sales_order_item_id: null,
    remarks: '',
    product_unit_product_code: option.product_unit_code,
    product_unit_product_name: option.product_name,
    product_unit_product_image_url: option.product_image_url,
    product_unit_unit_name: option.unit_name,
    product_unit_base_unit_name: option.base_unit_name,
    vat_profile_name: vatProfileName,
    sales_order_item_label: null,
    sales_order_item_qty_remaining_base: null,
    sales_order_item_qty_invoiced_base: null,
  };

  let targetIndex: number;

  if (editingProductUnitIndex.value === null) {
    salesInvoiceForm.items.push(itemData as any);
    targetIndex = salesInvoiceForm.items.length - 1;
    salesInvoiceItemDetailsExpanded.value[targetIndex] = false;
  } else {
    const currentItem = salesInvoiceItemsForm.value[editingProductUnitIndex.value];
    salesInvoiceItemsForm.value[editingProductUnitIndex.value] = {
      ...currentItem,
      ...itemData,
      id: currentItem?.id ?? null,
      qty: currentItem?.qty ?? 1,
      price_discount: currentItem?.price_discount ?? 0,
      subtotal_discount: currentItem?.subtotal_discount ?? 0,
      sales_order_item_id: currentItem?.sales_order_item_id ?? null,
      sales_order_item_label: currentItem?.sales_order_item_label ?? null,
      remarks: currentItem?.remarks ?? '',
    };
    targetIndex = editingProductUnitIndex.value;
  }

  showProductUnitModal.value = false;
  editingProductUnitIndex.value = null;
  productUnitQtyToFocus.value = targetIndex;

  clearItemErrors();
};

const handleProductUnitModalAfterLeave = () => {
  const index = productUnitQtyToFocus.value;
  productUnitQtyToFocus.value = null;

  if (index === null) return;

  nextTick(() => {
    const el = document.getElementById(`sales-invoice-item-qty-${index}`) as HTMLInputElement | null;
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el?.focus();
    el?.select();
  });
};

const removeProductUnit = (index: number) => {
  const item = salesInvoiceItemsForm.value[index];
  if (item?.id) {
    salesInvoiceForm.delete_item_ids.push(item.id);
  }
  salesInvoiceItemsForm.value.splice(index, 1);
  salesInvoiceItemDetailsExpanded.value.splice(index, 1);
  clearItemErrors();
};

const toggleSalesInvoiceItemDetails = (index: number) => {
  salesInvoiceItemDetailsExpanded.value[index] = !salesInvoiceItemDetailsExpanded.value[index];
};
// #endregion

// #region Methods - Payments
const addPayment = () => {
  salesInvoicePaymentsForm.value.push({
    id: null,
    code: '_AUTO_',
    date: '_AUTO_',
    payment_type: 'cash',
    cash_account_id: null,
    sales_order_payment_id: null,
    sales_return_id: null,
    amount: 0,
    remarks: '',
  });
};

const removePayment = (index: number) => {
  const payment = salesInvoicePaymentsForm.value[index];
  if (payment?.id) {
    salesInvoiceForm.delete_payment_ids.push(payment.id);
  }
  salesInvoicePaymentsForm.value.splice(index, 1);
  clearPaymentErrors();
};

const handlePaymentTypeChanged = (index: number) => {
  const payment = salesInvoicePaymentsForm.value[index];
  if (!payment) return;

  // only the FK matching the chosen type may be sent; the backend rejects the others
  payment.cash_account_id = null;
  payment.sales_order_payment_id = null;
  payment.sales_return_id = null;

  clearPaymentErrors();
  validateSalesInvoiceField(`payments.${index}.payment_type`);
};

const clearPaymentCashAccount = (index: number) => {
  const payment = salesInvoicePaymentsForm.value[index];
  if (!payment) return;
  payment.cash_account_id = null;
  validateSalesInvoiceField(`payments.${index}.cash_account_id`);
};

const clearPaymentSalesOrderPayment = (index: number) => {
  const payment = salesInvoicePaymentsForm.value[index];
  if (!payment) return;
  payment.sales_order_payment_id = null;
  validateSalesInvoiceField(`payments.${index}.sales_order_payment_id`);
};

const clearPaymentSalesReturn = (index: number) => {
  const payment = salesInvoicePaymentsForm.value[index];
  if (!payment) return;
  payment.sales_return_id = null;
  validateSalesInvoiceField(`payments.${index}.sales_return_id`);
};
// #endregion

// #region Methods - Money previews
const getItemUnitPriceAfterDiscountPreview = (item: SalesInvoiceItemFormItem) => {
  const price = Math.max(Number(item.product_unit_price || 0), 0);
  const priceDiscount = Math.min(Math.max(Number(item.price_discount || 0), 0), price);
  return Math.max(price - priceDiscount, 0);
};

const getItemUnitPriceSubtotalAfterDiscountPreview = (item: SalesInvoiceItemFormItem) =>
  Number(item.qty || 0) * getItemUnitPriceAfterDiscountPreview(item);

const getItemSubtotalAfterDiscountPreview = (item: SalesInvoiceItemFormItem) => {
  const subtotal = getItemUnitPriceSubtotalAfterDiscountPreview(item);
  const subtotalDiscount = Math.min(Math.max(Number(item.subtotal_discount || 0), 0), subtotal);
  return Math.max(subtotal - subtotalDiscount, 0);
};

const getItemsSubtotalAfterDiscountPreview = () =>
  salesInvoiceItemsForm.value.reduce((total, item) => total + getItemSubtotalAfterDiscountPreview(item), 0);

const getSalesInvoiceGlobalDiscountPreview = () =>
  Math.min(
    Math.max(Number(salesInvoiceForm.global_discount || 0), 0),
    getItemsSubtotalAfterDiscountPreview(),
  );

const getItemGlobalDiscountPreview = (item: SalesInvoiceItemFormItem, itemIndex: number) => {
  const totalBeforeGlobalDiscount = getItemsSubtotalAfterDiscountPreview();
  const totalGlobalDiscount = getSalesInvoiceGlobalDiscountPreview();

  if (totalBeforeGlobalDiscount <= 0 || totalGlobalDiscount <= 0) {
    return 0;
  }

  const allocations = salesInvoiceItemsForm.value.map((currentItem, index) => {
    if (index === salesInvoiceItemsForm.value.length - 1) {
      return 0;
    }

    return totalGlobalDiscount * (getItemSubtotalAfterDiscountPreview(currentItem) / totalBeforeGlobalDiscount);
  });

  const allocatedBeforeCurrent = allocations
    .slice(0, itemIndex)
    .reduce((total, allocation) => total + allocation, 0);

  if (itemIndex === salesInvoiceItemsForm.value.length - 1) {
    return Math.max(totalGlobalDiscount - allocatedBeforeCurrent, 0);
  }

  return Math.min(Math.max(allocations[itemIndex] || 0, 0), getItemSubtotalAfterDiscountPreview(item));
};

const getItemSubtotalAfterGlobalDiscountPreview = (item: SalesInvoiceItemFormItem, itemIndex: number) =>
  Math.max(getItemSubtotalAfterDiscountPreview(item) - getItemGlobalDiscountPreview(item, itemIndex), 0);

const getItemVatBasePreview = (item: SalesInvoiceItemFormItem, itemIndex: number) => {
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

const getItemVatPreview = (item: SalesInvoiceItemFormItem, itemIndex: number) => {
  const vatBase = getItemVatBasePreview(item, itemIndex);
  const vatRate = Number(item.vat_rate || 0);

  if (vatBase <= 0 || vatRate <= 0) {
    return 0;
  }

  return vatBase * (vatRate / 100);
};

const getItemTotalBeforeRoundingPreview = (item: SalesInvoiceItemFormItem, itemIndex: number) => {
  const subtotalAfterGlobalDiscount = getItemSubtotalAfterGlobalDiscountPreview(item, itemIndex);

  if (item.product_unit_is_price_include_vat) {
    return subtotalAfterGlobalDiscount;
  }

  return subtotalAfterGlobalDiscount + getItemVatPreview(item, itemIndex);
};

const getSalesInvoiceItemTotalAfterGlobalDiscountPreview = () =>
  salesInvoiceItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemSubtotalAfterGlobalDiscountPreview(item, itemIndex),
    0,
  );

const getSalesInvoiceVatBasePreview = () =>
  salesInvoiceItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemVatBasePreview(item, itemIndex),
    0,
  );

const getSalesInvoiceVatPreview = () =>
  salesInvoiceItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemVatPreview(item, itemIndex),
    0,
  );

const getTotalAmountPayableBeforeRoundingPreview = () =>
  salesInvoiceItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemTotalBeforeRoundingPreview(item, itemIndex),
    0,
  );

const getSalesInvoiceAmountPayablePreview = () =>
  getTotalAmountPayableBeforeRoundingPreview() + Number(salesInvoiceForm.rounding || 0);

const getPaymentsTotalPreview = () =>
  salesInvoicePaymentsForm.value.reduce(
    (total, payment) => total + Math.max(Number(payment.amount || 0), 0),
    0,
  );

const getAmountDuePreview = () =>
  getSalesInvoiceAmountPayablePreview() - getPaymentsTotalPreview();
// #endregion

// #region Actions
const onSubmit = async () => {
  if (salesInvoiceForm.hasErrors) {
    const firstErrorKey = Object.keys(salesInvoiceForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  const backupItems = [...salesInvoiceItemsForm.value];
  const backupPayments = [...salesInvoicePaymentsForm.value];

  const cleanedItems: SalesInvoiceItemNestedUpdateRequest[] = salesInvoiceItemsForm.value.map((item) => ({
    id: item.id,
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
    sales_order_item_id: item.sales_order_item_id,
    remarks: item.remarks,
  }));

  const cleanedPayments: SalesInvoicePaymentNestedUpdateRequest[] = salesInvoicePaymentsForm.value.map((payment) => ({
    id: payment.id,
    code: payment.code,
    date: payment.date,
    payment_type: payment.payment_type,
    cash_account_id: payment.payment_type === 'cash' ? payment.cash_account_id : null,
    sales_order_payment_id: payment.payment_type === 'down_payment' ? payment.sales_order_payment_id : null,
    sales_return_id: payment.payment_type === 'return' ? payment.sales_return_id : null,
    amount: payment.amount,
    remarks: payment.remarks,
  }));

  salesInvoiceForm.items = cleanedItems as any;
  salesInvoiceForm.payments = cleanedPayments as any;

  emits('loading-state', true);

  try {
    await salesInvoiceForm.submit();
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(t('views.sales_invoice.alert.update.title'), t('views.sales_invoice.alert.update.message'));
    router.push({ name: 'side-menu-sales-invoice-list' });
  } catch (error) {
    salesInvoiceForm.items = backupItems as any;
    salesInvoiceForm.payments = backupPayments as any;
    showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
  } finally {
    emits('loading-state', false);
  }
};
// #endregion
</script>

<template>
  <form id="salesInvoiceForm" @submit.prevent="onSubmit">
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
              <FormInput type="hidden" v-model="salesInvoiceForm.company_id" />
            </div>
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="salesInvoiceForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <!-- card: sales invoice header information -->
      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 md:col-span-6 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': salesInvoiceForm.invalid('code') }">
                {{ t('views.sales_invoice.fields.code') }}
              </FormLabel>
              <FormInputCode v-model="salesInvoiceForm.code"
                :class="{ 'border-danger': salesInvoiceForm.invalid('code') }"
                :placeholder="t('views.sales_invoice.fields.code')" @set-auto="setCode"
                @change="salesInvoiceForm.validate('code')" />
              <FormErrorMessages :messages="salesInvoiceForm.errors.code" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': salesInvoiceForm.invalid('date') }">
                {{ t('views.sales_invoice.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto v-model="salesInvoiceForm.date"
                :class="{ 'border-danger': salesInvoiceForm.invalid('date') }"
                :placeholder="t('views.sales_invoice.fields.date')" @change="salesInvoiceForm.validate('date')" />
              <FormErrorMessages :messages="salesInvoiceForm.errors.date" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': salesInvoiceForm.invalid('due_days') }">
                {{ t('views.sales_invoice.fields.due_days') }}
              </FormLabel>
              <FormInput v-model="salesInvoiceForm.due_days" type="number" min="0"
                :class="{ 'border-danger': salesInvoiceForm.invalid('due_days') }"
                @change="salesInvoiceForm.validate('due_days')" />
              <FormErrorMessages :messages="salesInvoiceForm.errors.due_days" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-3 flex flex-col justify-center">
              <FormLabel :class="{ 'text-danger': salesInvoiceForm.invalid('is_posted') }">
                {{ t('views.sales_invoice.fields.is_posted') }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input v-model="salesInvoiceForm.is_posted" type="checkbox" />
              </FormSwitch>
              <FormErrorMessages :messages="salesInvoiceForm.errors.is_posted" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': salesInvoiceForm.invalid('sales_order_id') }">
                {{ t('views.sales_invoice.fields.sales_order_id') }}
              </FormLabel>
              <FormSelectSearch v-model="salesInvoiceForm.sales_order_id" v-model:search="salesOrderSearch"
                :options="salesOrderOptions" :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': salesInvoiceForm.invalid('sales_order_id') }"
                @change="(value) => handleSalesOrderChanged(value as string | number | null)"
                @search="loadSalesOrderDDL" @clear="clearSalesOrder" />
              <FormErrorMessages :messages="salesInvoiceForm.errors.sales_order_id" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-6">
              <FormLabel :class="{ 'text-danger': salesInvoiceForm.invalid('customer_id') }">
                {{ t('views.sales_invoice.fields.customer_id') }}
              </FormLabel>
              <FormInput
                :model-value="selectedSalesOrderData?.customer?.name ?? salesInvoiceData?.customer?.name ?? '-'"
                readonly />
              <FormErrorMessages :messages="salesInvoiceForm.errors.customer_id" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': salesInvoiceForm.invalid('tax_invoice_number') }">
                {{ t('views.sales_invoice.fields.tax_invoice_number') }}
              </FormLabel>
              <FormInput v-model="salesInvoiceForm.tax_invoice_number"
                :class="{ 'border-danger': salesInvoiceForm.invalid('tax_invoice_number') }"
                :placeholder="t('views.sales_invoice.fields.tax_invoice_number')"
                @change="salesInvoiceForm.validate('tax_invoice_number')" />
              <FormErrorMessages :messages="salesInvoiceForm.errors.tax_invoice_number" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': salesInvoiceForm.invalid('tax_invoice_vat_base') }">
                {{ t('views.sales_invoice.fields.tax_invoice_vat_base') }}
              </FormLabel>
              <FormInputCurrency v-model="salesInvoiceForm.tax_invoice_vat_base" :allow-negative="false"
                :class="{ 'border-danger': salesInvoiceForm.invalid('tax_invoice_vat_base') }"
                @change="salesInvoiceForm.validate('tax_invoice_vat_base')" />
              <FormErrorMessages :messages="salesInvoiceForm.errors.tax_invoice_vat_base" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': salesInvoiceForm.invalid('tax_invoice_vat') }">
                {{ t('views.sales_invoice.fields.tax_invoice_vat') }}
              </FormLabel>
              <FormInputCurrency v-model="salesInvoiceForm.tax_invoice_vat" :allow-negative="false"
                :class="{ 'border-danger': salesInvoiceForm.invalid('tax_invoice_vat') }"
                @change="salesInvoiceForm.validate('tax_invoice_vat')" />
              <FormErrorMessages :messages="salesInvoiceForm.errors.tax_invoice_vat" />
            </div>
            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': salesInvoiceForm.invalid('remarks') }">
                {{ t('views.sales_invoice.fields.remarks') }}
              </FormLabel>
              <FormTextarea v-model="salesInvoiceForm.remarks"
                :class="{ 'border-danger': salesInvoiceForm.invalid('remarks') }"
                @change="salesInvoiceForm.validate('remarks')" />
              <FormErrorMessages :messages="salesInvoiceForm.errors.remarks" />
            </div>
          </div>

          <div class="mt-4 rounded-md border border-primary/20 bg-primary/5 px-4 py-3 text-sm text-slate-700 dark:text-slate-200">
            {{ t('views.sales_invoice.fields.sales_order_hint') }}
          </div>
        </div>
      </template>

      <!-- card: sales invoice item list and per-item breakdown -->
      <template #card-items-2>
        <div class="p-5 space-y-4">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="text-sm text-slate-500">
              {{ t('views.sales_invoice.fields.item_count') }}: {{ salesInvoiceItemsForm.length }}
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <Button v-if="salesInvoiceForm.sales_order_id" type="button" variant="outline-secondary"
                @click="reloadItemsFromSalesOrder">
                <Lucide icon="RefreshCw" class="mr-2 h-4 w-4" />
                {{ t('components.buttons.reload') }}
              </Button>
              <Button type="button" variant="outline-primary" @click="openAddProductUnit">
                <Lucide icon="Plus" class="mr-2 h-4 w-4" />
                {{ t('components.buttons.create_new') }}
              </Button>
            </div>
          </div>

          <FormErrorMessages :messages="salesInvoiceForm.errors.items" />

          <div v-if="salesInvoiceItemsForm.length === 0" class="text-slate-500 text-sm">
            {{ t('views.sales_invoice.fields.items_empty') }}
          </div>

          <div v-else>
            <div v-for="(item, index) in salesInvoiceItemsForm" :key="`${item.product_unit_id}-${index}`"
              class="mt-3 border-t border-slate-200/60 pt-5 first:mt-0 first:border-t-0 first:pt-0 dark:border-darkmode-400">
              <div class="grid grid-cols-12 gap-4 gap-y-3">
                <div :class="currentItemLayout === 'lg' ? 'col-span-12 lg:col-span-1' : (currentItemLayout === 'md' ? 'col-span-12 md:col-span-3' : 'col-span-12')">
                  <div class="form-control border rounded-md px-3">
                    <div class="flex justify-center">
                      <ProductImagePreview :image-url="item.product_unit_product_image_url"
                        wrapper-class="w-16 h-16 rounded-md overflow-hidden bg-slate-100 dark:bg-darkmode-600 flex items-center justify-center cursor-zoom-in shrink-0"
                        icon-class="w-5 h-5 text-slate-400"
                        :preview-title="item.product_unit_product_name || t('views.sales_invoice.fields.product_unit_id')" />
                    </div>
                  </div>
                </div>
                <div :class="currentItemLayout === 'lg' ? 'col-span-12 lg:col-span-4' : (currentItemLayout === 'md' ? 'col-span-12 md:col-span-9' : 'col-span-12')">
                  <FormLabel :class="{ 'text-danger': invalidSalesInvoiceField(`items.${index}.product_unit_id`) }">
                    <span>{{ t('views.sales_invoice.fields.product_unit_id') }}</span>
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
                  <div v-if="item.sales_order_item_label" class="mt-1 text-xs text-primary break-words">
                    {{ item.sales_order_item_label }}
                  </div>
                  <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`items.${index}.product_unit_id`)" />
                  <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`items.${index}.sales_order_item_id`)" />
                </div>
                <div :class="currentItemLayout === 'lg' ? 'col-span-12 lg:col-span-2' : (currentItemLayout === 'md' ? 'col-span-12 md:col-span-4' : 'col-span-12')">
                  <div class="grid grid-cols-2 gap-4">
                    <div>
                      <FormLabel :class="{ 'text-danger': invalidSalesInvoiceField(`items.${index}.qty`) }">
                        {{ t('views.sales_invoice.fields.qty') }}
                      </FormLabel>
                      <FormInputCurrency :id="`sales-invoice-item-qty-${index}`" v-model="item.qty"
                        :allow-negative="false"
                        :class="{ 'border-danger': invalidSalesInvoiceField(`items.${index}.qty`) }"
                        @change="validateSalesInvoiceField(`items.${index}.qty`)" />
                      <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`items.${index}.qty`)" />
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
                  <div v-if="item.sales_order_item_qty_remaining_base !== null" class="mt-1 text-right">
                    <div class="text-xs text-slate-500 dark:text-slate-400">
                      {{ t('views.sales_invoice.fields.qty_remaining_to_invoice_base') }}
                      {{ formatCompactNumberValue(item.sales_order_item_qty_remaining_base ?? 0) }}
                    </div>
                  </div>
                </div>
                <div :class="currentItemLayout === 'lg' ? 'col-span-12 lg:col-span-2' : (currentItemLayout === 'md' ? 'col-span-12 md:col-span-4' : 'col-span-12')">
                  <FormLabel :class="{ 'text-danger': invalidSalesInvoiceField(`items.${index}.product_unit_price`) }">
                    {{ t('views.sales_invoice.fields.product_unit_price') }}
                  </FormLabel>
                  <FormInputCurrency v-model="item.product_unit_price" :allow-negative="false"
                    :class="{ 'border-danger': invalidSalesInvoiceField(`items.${index}.product_unit_price`) }"
                    @change="validateSalesInvoiceField(`items.${index}.product_unit_price`)" />
                  <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`items.${index}.product_unit_price`)" />
                </div>
                <div :class="currentItemLayout === 'lg' ? 'col-span-12 lg:col-span-3' : (currentItemLayout === 'md' ? 'col-span-12 md:col-span-4' : 'col-span-12')">
                  <FormLabel>{{ t('views.sales_invoice.fields.subtotal_after_discount') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency :model-value="getItemSubtotalAfterDiscountPreview(item)" readonly />
                    </div>
                    <div class="shrink-0">
                      <Button type="button" variant="outline-secondary"
                        class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                        @click="toggleSalesInvoiceItemDetails(index)">
                        {{ salesInvoiceItemDetailsExpanded[index] ? '▲' : '▼' }}
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
              </div>

              <div v-if="salesInvoiceItemDetailsExpanded[index]" class="mt-4 grid grid-cols-12 gap-4">
                <div class="col-span-12 lg:col-span-6">
                  <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-4">
                    <div class="font-medium text-sm">{{ t('views.sales_invoice.fields.item_price_breakdown') }}</div>

                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-5 flex items-center text-sm font-medium">
                        {{ t('views.sales_invoice.fields.price_discount') }}
                      </div>
                      <div class="col-span-12 md:col-span-7">
                        <FormInputCurrency v-model="item.price_discount" :allow-negative="false"
                          :class="{ 'border-danger': invalidSalesInvoiceField(`items.${index}.price_discount`) }"
                          @change="validateSalesInvoiceField(`items.${index}.price_discount`)" />
                        <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`items.${index}.price_discount`)" />
                      </div>

                      <div class="col-span-12 md:col-span-5 flex items-center text-sm font-medium">
                        {{ t('views.sales_invoice.fields.price_after_discount') }}
                      </div>
                      <div class="col-span-12 md:col-span-7">
                        <FormInputCurrency :model-value="getItemUnitPriceAfterDiscountPreview(item)" readonly />
                      </div>

                      <div class="col-span-12 md:col-span-5 flex items-center text-sm font-medium">
                        {{ t('views.sales_invoice.fields.subtotal') }}
                      </div>
                      <div class="col-span-12 md:col-span-7">
                        <FormInputCurrency :model-value="getItemUnitPriceSubtotalAfterDiscountPreview(item)" readonly />
                      </div>

                      <div class="col-span-12 md:col-span-5 flex items-center text-sm font-medium">
                        {{ t('views.sales_invoice.fields.subtotal_discount') }}
                      </div>
                      <div class="col-span-12 md:col-span-7">
                        <FormInputCurrency v-model="item.subtotal_discount" :allow-negative="false"
                          :class="{ 'border-danger': invalidSalesInvoiceField(`items.${index}.subtotal_discount`) }"
                          @change="validateSalesInvoiceField(`items.${index}.subtotal_discount`)" />
                        <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`items.${index}.subtotal_discount`)" />
                      </div>

                      <div class="col-span-12 md:col-span-5 flex items-center text-sm font-medium">
                        {{ t('views.sales_invoice.fields.subtotal_after_discount') }}
                      </div>
                      <div class="col-span-12 md:col-span-7">
                        <FormInputCurrency :model-value="getItemSubtotalAfterDiscountPreview(item)" readonly />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="col-span-12 lg:col-span-6">
                  <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-4">
                    <div class="font-medium text-sm">{{ t('views.sales_invoice.fields.item_additional_details') }}</div>

                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-5">
                        <FormLabel>{{ t('views.sales_invoice.fields.product_unit_is_price_include_vat') }}</FormLabel>
                        <FormSwitch>
                          <FormSwitch.Input v-model="item.product_unit_is_price_include_vat" type="checkbox" />
                        </FormSwitch>
                      </div>
                      <div class="col-span-12 md:col-span-7">
                        <FormLabel :class="{ 'text-danger': invalidSalesInvoiceField(`items.${index}.vat_profile_id`) }">
                          {{ t('views.sales_invoice.fields.vat_profile_id') }}
                        </FormLabel>
                        <FormSelectSearch v-model="item.vat_profile_id" v-model:search="vatProfileSearch"
                          :options="vatProfileOptions" :placeholder="t('components.dropdown.placeholder')"
                          :class="{ 'border-danger': invalidSalesInvoiceField(`items.${index}.vat_profile_id`) }"
                          @change="syncVatProfile(index)" @search="loadVatProfileDDL" @clear="clearVatProfile(index)" />
                        <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`items.${index}.vat_profile_id`)" />
                      </div>
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel :class="{ 'text-danger': invalidSalesInvoiceField(`items.${index}.vat_rate`) }">
                          {{ t('views.sales_invoice.fields.vat_rate') }}
                        </FormLabel>
                        <FormInputCurrency v-model="item.vat_rate" :allow-negative="false"
                          :class="{ 'border-danger': invalidSalesInvoiceField(`items.${index}.vat_rate`) }"
                          @change="validateSalesInvoiceField(`items.${index}.vat_rate`)" />
                        <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`items.${index}.vat_rate`)" />
                      </div>
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel :class="{ 'text-danger': invalidSalesInvoiceField(`items.${index}.vat_base_numerator`) }">
                          {{ t('views.sales_invoice.fields.vat_base_numerator') }}
                        </FormLabel>
                        <FormInput v-model="item.vat_base_numerator" type="number" min="1"
                          :class="{ 'border-danger': invalidSalesInvoiceField(`items.${index}.vat_base_numerator`) }"
                          @change="validateSalesInvoiceField(`items.${index}.vat_base_numerator`)" />
                        <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`items.${index}.vat_base_numerator`)" />
                      </div>
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel :class="{ 'text-danger': invalidSalesInvoiceField(`items.${index}.vat_base_denominator`) }">
                          {{ t('views.sales_invoice.fields.vat_base_denominator') }}
                        </FormLabel>
                        <FormInput v-model="item.vat_base_denominator" type="number" min="1"
                          :class="{ 'border-danger': invalidSalesInvoiceField(`items.${index}.vat_base_denominator`) }"
                          @change="validateSalesInvoiceField(`items.${index}.vat_base_denominator`)" />
                        <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`items.${index}.vat_base_denominator`)" />
                      </div>
                      <div class="col-span-12">
                        <FormLabel :class="{ 'text-danger': invalidSalesInvoiceField(`items.${index}.remarks`) }">
                          {{ t('views.sales_invoice.fields.remarks') }}
                        </FormLabel>
                        <FormTextarea v-model="item.remarks"
                          :class="{ 'border-danger': invalidSalesInvoiceField(`items.${index}.remarks`) }"
                          @change="validateSalesInvoiceField(`items.${index}.remarks`)" />
                        <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`items.${index}.remarks`)" />
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
                <FormLabel>{{ t('views.sales_invoice.fields.items_subtotal_after_discount') }}</FormLabel>
                <FormInputCurrency :model-value="getItemsSubtotalAfterDiscountPreview()" readonly />
              </div>
            </div>

            <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
              <div class="col-span-12 lg:col-span-9"></div>
              <div class="col-span-12 lg:col-span-3">
                <FormLabel :class="{ 'text-danger': salesInvoiceForm.invalid('global_discount') }">
                  {{ t('views.sales_invoice.fields.global_discount') }}
                </FormLabel>
                <FormInputCurrency v-model="salesInvoiceForm.global_discount" :allow-negative="false"
                  :class="{ 'border-danger': salesInvoiceForm.invalid('global_discount') }"
                  @change="salesInvoiceForm.validate('global_discount')" />
                <FormErrorMessages :messages="salesInvoiceForm.errors.global_discount" />
              </div>
            </div>

            <template v-if="isTotalsBreakdownExpanded">
              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.sales_invoice.fields.item_total_after_global_discount') }}</FormLabel>
                  <FormInputCurrency :model-value="getSalesInvoiceItemTotalAfterGlobalDiscountPreview()" readonly />
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.sales_invoice.fields.vat_base') }}</FormLabel>
                  <FormInputCurrency :model-value="formatCurrencyPreviewValue(getSalesInvoiceVatBasePreview())"
                    readonly />
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.sales_invoice.fields.vat') }}</FormLabel>
                  <FormInputCurrency :model-value="formatCurrencyPreviewValue(getSalesInvoiceVatPreview())" readonly />
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': salesInvoiceForm.invalid('rounding') }">
                    {{ t('views.sales_invoice.fields.rounding') }}
                  </FormLabel>
                  <FormInputCurrency v-model="salesInvoiceForm.rounding"
                    :class="{ 'border-danger': salesInvoiceForm.invalid('rounding') }"
                    @change="salesInvoiceForm.validate('rounding')" />
                  <FormErrorMessages :messages="salesInvoiceForm.errors.rounding" />
                </div>
              </div>
            </template>

            <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
              <div class="col-span-12 lg:col-span-9"></div>
              <div class="col-span-12 lg:col-span-3">
                <FormLabel>{{ t('views.sales_invoice.fields.amount_payable') }}</FormLabel>
                <div class="flex items-start gap-2">
                  <div class="shrink-0">
                    <Button type="button" variant="outline-secondary"
                      class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                      @click="isTotalsBreakdownExpanded = !isTotalsBreakdownExpanded">
                      {{ isTotalsBreakdownExpanded ? '▲' : '▼' }}
                    </Button>
                  </div>
                  <div class="flex-1 min-w-0">
                    <FormInputCurrency :model-value="getSalesInvoiceAmountPayablePreview()" readonly />
                  </div>
                </div>
              </div>
            </div>

            <div class="space-y-4">
              <div v-if="isPaymentEditorExpanded" class="space-y-4">
                <FormErrorMessages :messages="salesInvoiceForm.errors.payments" />

                <div v-if="salesInvoicePaymentsForm.length === 0" class="text-right text-slate-500 text-sm">
                  {{ t('views.sales_invoice.fields.payments_empty') }}
                </div>

                <div v-else class="space-y-4">
                  <div v-for="(payment, index) in salesInvoicePaymentsForm" :key="`payment-${index}`"
                    class="space-y-3 rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4">
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-6 lg:col-span-2">
                        <FormLabel :class="{ 'text-danger': invalidSalesInvoiceField(`payments.${index}.code`) }">
                          {{ t('views.sales_invoice.fields.code') }}
                        </FormLabel>
                        <FormInputCode v-model="payment.code"
                          :class="{ 'border-danger': invalidSalesInvoiceField(`payments.${index}.code`) }"
                          :placeholder="t('views.sales_invoice.fields.code')" @set-auto="setPaymentCode(index)"
                          @change="validateSalesInvoiceField(`payments.${index}.code`)" />
                        <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`payments.${index}.code`)" />
                      </div>
                      <div class="col-span-12 md:col-span-6 lg:col-span-3">
                        <FormLabel :class="{ 'text-danger': invalidSalesInvoiceField(`payments.${index}.date`) }">
                          {{ t('views.sales_invoice.fields.date') }}
                        </FormLabel>
                        <FormInputDateTimeAuto v-model="payment.date"
                          :class="{ 'border-danger': invalidSalesInvoiceField(`payments.${index}.date`) }"
                          :placeholder="t('views.sales_invoice.fields.date')"
                          @change="validateSalesInvoiceField(`payments.${index}.date`)" />
                        <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`payments.${index}.date`)" />
                      </div>
                      <div class="col-span-12 md:col-span-6 lg:col-span-2">
                        <FormLabel :class="{ 'text-danger': invalidSalesInvoiceField(`payments.${index}.payment_type`) }">
                          {{ t('views.sales_invoice.fields.payment_type') }}
                        </FormLabel>
                        <FormSelect v-model="payment.payment_type"
                          :class="{ 'border-danger': invalidSalesInvoiceField(`payments.${index}.payment_type`) }"
                          @change="handlePaymentTypeChanged(index)">
                          <option v-for="option in paymentTypeOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                          </option>
                        </FormSelect>
                        <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`payments.${index}.payment_type`)" />
                      </div>
                      <!-- only the field belonging to the chosen payment type is shown -->
                      <div v-if="payment.payment_type === 'cash'" class="col-span-12 md:col-span-6 lg:col-span-3">
                        <FormLabel :class="{ 'text-danger': invalidSalesInvoiceField(`payments.${index}.cash_account_id`) }">
                          {{ t('views.sales_invoice.fields.cash_account_id') }}
                        </FormLabel>
                        <FormSelectSearch v-model="payment.cash_account_id" v-model:search="cashAccountSearch"
                          :options="cashAccountOptions" :placeholder="t('components.dropdown.placeholder')"
                          :class="{ 'border-danger': invalidSalesInvoiceField(`payments.${index}.cash_account_id`) }"
                          @change="validateSalesInvoiceField(`payments.${index}.cash_account_id`)"
                          @search="loadCashAccountDDL" @clear="clearPaymentCashAccount(index)" />
                        <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`payments.${index}.cash_account_id`)" />
                      </div>
                      <div v-else-if="payment.payment_type === 'down_payment'"
                        class="col-span-12 md:col-span-6 lg:col-span-3">
                        <FormLabel :class="{ 'text-danger': invalidSalesInvoiceField(`payments.${index}.sales_order_payment_id`) }">
                          {{ t('views.sales_invoice.fields.sales_order_payment_id') }}
                        </FormLabel>
                        <FormSelectSearch v-model="payment.sales_order_payment_id"
                          :options="salesOrderPaymentOptions" :placeholder="t('components.dropdown.placeholder')"
                          :class="{ 'border-danger': invalidSalesInvoiceField(`payments.${index}.sales_order_payment_id`) }"
                          @change="validateSalesInvoiceField(`payments.${index}.sales_order_payment_id`)"
                          @clear="clearPaymentSalesOrderPayment(index)" />
                        <FormErrorMessages
                          :messages="getSalesInvoiceFieldErrors(`payments.${index}.sales_order_payment_id`)" />
                      </div>
                      <div v-else class="col-span-12 md:col-span-6 lg:col-span-3">
                        <FormLabel :class="{ 'text-danger': invalidSalesInvoiceField(`payments.${index}.sales_return_id`) }">
                          {{ t('views.sales_invoice.fields.sales_return_id') }}
                        </FormLabel>
                        <FormSelectSearch v-model="payment.sales_return_id" v-model:search="salesReturnSearch"
                          :options="salesReturnOptions" :placeholder="t('components.dropdown.placeholder')"
                          :class="{ 'border-danger': invalidSalesInvoiceField(`payments.${index}.sales_return_id`) }"
                          @change="validateSalesInvoiceField(`payments.${index}.sales_return_id`)"
                          @search="loadSalesReturnDDL" @clear="clearPaymentSalesReturn(index)" />
                        <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`payments.${index}.sales_return_id`)" />
                      </div>
                      <div class="col-span-12 md:col-span-6 lg:col-span-2">
                        <FormLabel :class="{ 'text-danger': invalidSalesInvoiceField(`payments.${index}.amount`) }">
                          {{ t('views.sales_invoice.fields.amount') }}
                        </FormLabel>
                        <div class="flex items-start gap-2">
                          <div class="flex-1 min-w-0">
                            <FormInputCurrency v-model="payment.amount" :allow-negative="false"
                              :class="{ 'border-danger': invalidSalesInvoiceField(`payments.${index}.amount`) }"
                              @change="validateSalesInvoiceField(`payments.${index}.amount`)" />
                          </div>
                          <div class="shrink-0">
                            <Button type="button" variant="outline-secondary"
                              class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                              @click="removePayment(index)">
                              <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                            </Button>
                          </div>
                        </div>
                        <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`payments.${index}.amount`)" />
                      </div>
                      <div class="col-span-12">
                        <FormLabel :class="{ 'text-danger': invalidSalesInvoiceField(`payments.${index}.remarks`) }">
                          {{ t('views.sales_invoice.fields.remarks') }}
                        </FormLabel>
                        <FormTextarea v-model="payment.remarks"
                          :class="{ 'border-danger': invalidSalesInvoiceField(`payments.${index}.remarks`) }"
                          @change="validateSalesInvoiceField(`payments.${index}.remarks`)" />
                        <FormErrorMessages :messages="getSalesInvoiceFieldErrors(`payments.${index}.remarks`)" />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="flex justify-end">
                  <Button type="button" variant="outline-primary" @click="addPayment">
                    <Lucide icon="Plus" class="w-4 h-4 mr-1" />
                    {{ t('components.buttons.create_new') }}
                  </Button>
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.sales_invoice.fields.amount_paid_total') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="shrink-0">
                      <Button type="button" variant="outline-secondary"
                        class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                        @click="isPaymentEditorExpanded = !isPaymentEditorExpanded">
                        {{ isPaymentEditorExpanded ? '▲' : '▼' }}
                      </Button>
                    </div>
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency :model-value="getPaymentsTotalPreview()" readonly />
                    </div>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.sales_invoice.fields.amount_due') }}</FormLabel>
                  <FormInputCurrency :model-value="getAmountDuePreview()" readonly />
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
            :disabled="salesInvoiceForm.validating || salesInvoiceForm.hasErrors">
            <Lucide v-if="salesInvoiceForm.validating" icon="Loader" class="w-4 h-4 mr-2 animate-spin" />
            <template v-else>
              <Lucide icon="Save" class="w-4 h-4 mr-2" />
            </template>
            {{ t('components.buttons.save') }}
          </Button>
        </div>
      </template>
    </TwoColumnsLayout>
  </form>

  <!-- dialog: product unit picker for add/change item -->
  <ProductUnitPickerDialog size="xl" panel-class="max-w-5xl" :open="showProductUnitModal"
    :title="t('views.sales_invoice.fields.product_unit_id')" :search-text="productSearchText"
    :is-searching="isSearchingProductUnit" :options="productUnitOptions" :columns="productUnitDialogColumns"
    @update:search-text="productSearchText = $event" @search="searchProductUnits"
    @select="selectProductUnit($event as ProductUnitOption)" @close="showProductUnitModal = false"
    @after-leave="handleProductUnitModalAfterLeave" />
</template>
