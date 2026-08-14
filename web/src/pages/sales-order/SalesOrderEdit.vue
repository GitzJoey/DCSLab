<script setup lang="ts">
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
  FormSelectSearch,
  FormSwitch,
  FormTextarea,
} from '@/components/Base/Form';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import ProductImagePreview from '@/components/Product/ProductImagePreview.vue';
import ProductUnitPickerDialog from '@/components/Product/ProductUnitPickerDialog.vue';
import CashAccountService from '@/services/CashAccountService';
import CustomerService from '@/services/CustomerService';
import ProductService from '@/services/ProductService';
import SalesOrderService from '@/services/SalesOrderService';
import VatProfileService from '@/services/VatProfileService';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { DropDownOption } from '@/types/models/DropDownOption';
import { NotificationData } from '@/types/models/NotificationData';
import { SalesOrder } from '@/types/models/SalesOrder';
import {
  SalesOrderItemNestedUpdateRequest,
  SalesOrderPaymentNestedUpdateRequest,
  SalesOrderPaymentRefundNestedUpdateRequest,
} from '@/types/services/sales-order/SalesOrderRequest';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { convertErrorTypeToAlertListType, formatCurrency, formatDate } from '@/utils/helper';

type SalesOrderItemFormItem = SalesOrderItemNestedUpdateRequest & {
  product_unit_product_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_product_image_url?: string | null;
  product_unit_unit_name?: string | null;
  product_unit_base_unit_name?: string | null;
  vat_profile_name?: string | null;
};

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

const salesOrderService = new SalesOrderService();
const customerService = new CustomerService();
const vatProfileService = new VatProfileService();
const cashAccountService = new CashAccountService();
const productService = new ProductService();

const salesOrderForm = salesOrderService.useSalesOrderEditForm(route.params.ulid as string);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const salesOrderData = ref<SalesOrder | null>(null);

const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'views.sales_order.field_groups.company_info', state: CardState.Expanded },
  { title: 'views.sales_order.field_groups.sales_order_data', state: CardState.Expanded },
  { title: 'views.sales_order.field_groups.items', state: CardState.Expanded },
  { title: 'views.sales_order.field_groups.summary', state: CardState.Expanded },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const customerDDL = ref<Array<DropDownOption> | null>(null);
const customerSearch = ref<string>('');
const customerOptions = computed(() =>
  (customerDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

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

const showProductUnitModal = ref<boolean>(false);
const productSearchText = ref<string>('');
const isSearchingProductUnit = ref<boolean>(false);
const productUnitOptions = ref<Array<ProductUnitOption>>([]);
const editingProductUnitIndex = ref<number | null>(null);
const productUnitQtyToFocus = ref<number | null>(null);
const salesOrderItemDetailsExpanded = ref<boolean[]>([]);
const viewportWidth = ref<number>(window.innerWidth);

const productUnitDialogColumns = [
  {
    key: 'unit_name',
    label: t('views.product.table.cols.unit'),
  },
  {
    key: 'conversion_value',
    label: t('views.sales_order.fields.product_unit_conversion_value'),
    align: 'right' as const,
    formatter: 'number' as const,
  },
  {
    key: 'price',
    label: t('views.sales_order.fields.product_unit_price'),
    align: 'right' as const,
    formatter: 'number' as const,
  },
];

const currentItemLayout = computed<'sm' | 'md' | 'lg'>(() => {
  if (viewportWidth.value >= 1024) return 'lg';
  if (viewportWidth.value >= 768) return 'md';
  return 'sm';
});

const salesOrderItemsForm = computed<SalesOrderItemFormItem[]>(
  () => salesOrderForm.items as SalesOrderItemFormItem[],
);
const salesOrderPaymentsForm = computed<SalesOrderPaymentNestedUpdateRequest[]>(
  () => salesOrderForm.payments as SalesOrderPaymentNestedUpdateRequest[],
);
const salesOrderRefundedPaymentsForm = computed<SalesOrderPaymentRefundNestedUpdateRequest[]>(
  () => salesOrderForm.refunded_payments as SalesOrderPaymentRefundNestedUpdateRequest[],
);
const isTotalsBreakdownExpanded = ref(false);
const isPaymentEditorExpanded = ref(false);
const isRefundedPaymentEditorExpanded = ref(false);

const invalidSalesOrderField = (field: string) => salesOrderForm.invalid(field as any);
const validateSalesOrderField = (field: string) => salesOrderForm.validate(field as any);
const getSalesOrderFieldErrors = (field: string) =>
  (salesOrderForm.errors as Record<string, string | undefined>)[field];

const syncViewportWidth = () => {
  viewportWidth.value = window.innerWidth;
};

const handleExpandCard = (index: number) => {
  cards.value[index].state =
    cards.value[index].state === CardState.Collapsed ? CardState.Expanded : CardState.Collapsed;
};

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
    await Promise.all([loadCustomerDDL(), loadVatProfileDDL(), loadCashAccountDDL()]);
    await loadData();
    await Promise.all([loadCustomerDDL(), loadVatProfileDDL(), loadCashAccountDDL()]);
  } finally {
    emits('loading-state', false);
  }
});

onUnmounted(() => {
  window.removeEventListener('resize', syncViewportWidth);
});

const setCode = () => {
  salesOrderForm.forgetError('code');
  salesOrderForm.setData({
    code: salesOrderForm.code === '_AUTO_' ? '' : '_AUTO_',
  });
};

const setPaymentCode = (index: number) => {
  salesOrderForm.forgetError(`payments.${index}.code` as any);
  salesOrderPaymentsForm.value[index].code =
    salesOrderPaymentsForm.value[index].code === '_AUTO_' ? '' : '_AUTO_';
};

const setRefundedPaymentCode = (index: number) => {
  salesOrderForm.forgetError(`refunded_payments.${index}.code` as any);
  salesOrderRefundedPaymentsForm.value[index].code =
    salesOrderRefundedPaymentsForm.value[index].code === '_AUTO_' ? '' : '_AUTO_';
};

const appendDDL = (
  target: typeof customerDDL | typeof cashAccountDDL,
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

const loadCustomerDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await customerService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    status: undefined,
    include_id: salesOrderData.value?.customer?.id,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    customerDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }

  appendDDL(customerDDL, salesOrderData.value?.customer);
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

  (salesOrderData.value?.items ?? []).forEach((item) => appendVatProfileOption({
    id: item.vat_profile?.id,
    name: item.vat_profile?.name,
    vat_rate: item.vat_rate,
    vat_base_numerator: item.vat_base_numerator,
    vat_base_denominator: item.vat_base_denominator,
  }));
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

  (salesOrderData.value?.payments ?? []).forEach((item) => appendDDL(cashAccountDDL, item.cash_account));
  (salesOrderData.value?.refunded_payments ?? []).forEach((item) => appendDDL(cashAccountDDL, item.cash_account));
};

const clearCustomer = () => {
  salesOrderForm.setData({ customer_id: null });
  salesOrderForm.forgetError('customer_id');
  salesOrderForm.validate('customer_id');
};

const applyVatProfileToItem = (
  item: SalesOrderItemFormItem,
  vatProfileId: string | null,
) => {
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
  const item = salesOrderItemsForm.value[index];
  if (!item) return;
  applyVatProfileToItem(item, null);
  salesOrderForm.validate(`items.${index}.vat_profile_id` as any);
};

const syncVatProfile = (index: number) => {
  const item = salesOrderItemsForm.value[index];
  if (!item) return;

  applyVatProfileToItem(item, item.vat_profile_id ?? null);
  salesOrderForm.validate(`items.${index}.vat_profile_id` as any);
};

const clearCashAccount = (index: number) => {
  const payment = salesOrderPaymentsForm.value[index];
  if (!payment) return;
  payment.cash_account_id = '';
  salesOrderForm.validate(`payments.${index}.cash_account_id` as any);
};

const clearRefundedCashAccount = (index: number) => {
  const refundedPayment = salesOrderRefundedPaymentsForm.value[index];
  if (!refundedPayment) return;
  refundedPayment.cash_account_id = '';
  salesOrderForm.validate(`refunded_payments.${index}.cash_account_id` as any);
};

const loadData = async () => {
  const ulid = route.params.ulid as string | undefined;
  if (!ulid) return;

  const result = await salesOrderService.read(ulid);

  if (result.success && result.data) {
    const data = result.data;
    salesOrderData.value = data;

    const items: SalesOrderItemFormItem[] = (data.items || []).map((item: any) => ({
      id: item.id ?? null,
      qty: item.qty,
      product_unit_id: item.product_unit?.id ?? '',
      product_unit_product_code: item.product_unit?.code ?? '',
      product_unit_product_name: item.product_unit?.product?.name ?? '',
      product_unit_product_image_url: item.product_unit?.product?.main_product_image?.url ?? null,
      product_unit_unit_name: item.product_unit?.unit?.name ?? '',
      product_unit_base_unit_name: Number(item.product_unit_conversion_value ?? 1) !== 1
        ? item.product_unit?.product?.base_product_unit?.unit?.name ?? ''
        : '',
      product_unit_conversion_value: item.product_unit_conversion_value,
      product_unit_price: item.product_unit_price,
      product_unit_is_price_include_vat: item.product_unit_is_price_include_vat,
      price_discount: Number(item.price_discount ?? 0),
      subtotal_discount: Number(item.subtotal_discount ?? 0),
      vat_profile_id: item.vat_profile?.id ?? null,
      vat_profile_name: item.vat_profile?.name ?? null,
      vat_rate: Number(item.vat_rate ?? 0),
      vat_base_numerator: item.vat_base_numerator,
      vat_base_denominator: item.vat_base_denominator,
      remarks: item.remarks ?? '',
    }));

    salesOrderForm.setData({
      company_id: data.company.id,
      branch_id: data.branch.id,
      code: data.code,
      date: formatDate(data.date, 'YYYY-MM-DD HH:mm:ss'),
      due_days: data.due_days,
      customer_id: data.customer?.id ?? null,
      remarks: data.remarks ?? '',
      global_discount: data.global_discount ?? 0,
      rounding: data.rounding ?? 0,
      delete_item_ids: [],
      items: items as any,
      delete_payment_ids: [],
      payments: (data.payments || []).map((payment: any) => ({
        id: payment.id ?? null,
        code: payment.code,
        date: formatDate(payment.date, 'YYYY-MM-DD HH:mm:ss'),
        cash_account_id: payment.cash_account?.id ?? '',
        amount: payment.amount,
        amount_allocated: payment.amount_allocated ?? 0,
        remarks: payment.remarks ?? '',
      })) as any,
      delete_refunded_payment_ids: [],
      refunded_payments: (data.refunded_payments || []).map((refundedPayment: any) => ({
        id: refundedPayment.id ?? null,
        code: refundedPayment.code,
        date: formatDate(refundedPayment.date, 'YYYY-MM-DD HH:mm:ss'),
        cash_account_id: refundedPayment.cash_account?.id ?? '',
        amount: refundedPayment.amount,
        remarks: refundedPayment.remarks ?? '',
      })) as any,
    } as any);
    salesOrderItemDetailsExpanded.value = items.map(() => false);
  }
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
      const baseUnitName =
        units.find((unit: any) => Number(unit.conversion_value ?? 1) === 1)?.unit?.name ?? '';
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

  const itemData: Partial<SalesOrderItemFormItem> = {
    product_unit_id: option.product_unit_id,
    product_unit_product_code: option.product_unit_code,
    product_unit_product_name: option.product_name,
    product_unit_product_image_url: option.product_image_url,
    product_unit_unit_name: option.unit_name,
    product_unit_base_unit_name: option.conversion_value != 1 ? option.base_unit_name : '',
    product_unit_conversion_value: option.conversion_value,
    product_unit_price: option.price,
    product_unit_is_price_include_vat: option.product_unit_is_price_include_vat,
    vat_profile_id: vatProfileId,
    vat_profile_name: vatProfileName,
    vat_rate: vatRate,
    vat_base_numerator: vatBaseNumerator,
    vat_base_denominator: vatBaseDenominator,
  };

  let targetIndex: number;

  if (editingProductUnitIndex.value === null) {
    salesOrderForm.items.push({
      id: null,
      qty: 1,
      product_unit_id: option.product_unit_id,
      product_unit_product_code: option.product_unit_code,
      product_unit_product_name: option.product_name,
      product_unit_product_image_url: option.product_image_url,
      product_unit_unit_name: option.unit_name,
      product_unit_base_unit_name: option.conversion_value != 1 ? option.base_unit_name : '',
      product_unit_conversion_value: option.conversion_value,
      product_unit_price: option.price,
      product_unit_is_price_include_vat: option.product_unit_is_price_include_vat,
      price_discount: 0,
      subtotal_discount: 0,
      vat_profile_id: vatProfileId,
      vat_profile_name: vatProfileName,
      vat_rate: vatRate,
      vat_base_numerator: vatBaseNumerator,
      vat_base_denominator: vatBaseDenominator,
      remarks: '',
    } as any);
    targetIndex = salesOrderForm.items.length - 1;
    salesOrderItemDetailsExpanded.value[targetIndex] = true;
  } else {
    salesOrderItemsForm.value[editingProductUnitIndex.value] = {
      ...salesOrderItemsForm.value[editingProductUnitIndex.value],
      ...itemData,
    } as SalesOrderItemFormItem;
    targetIndex = editingProductUnitIndex.value;
  }

  showProductUnitModal.value = false;
  editingProductUnitIndex.value = null;
  productUnitQtyToFocus.value = targetIndex;

  Object.keys(salesOrderForm.errors).forEach((key) => {
    if (key.startsWith('items.')) {
      salesOrderForm.forgetError(key as any);
    }
  });
};

const handleProductUnitModalAfterLeave = () => {
  const index = productUnitQtyToFocus.value;
  productUnitQtyToFocus.value = null;
  if (index === null) return;

  nextTick(() => {
    const el = document.getElementById(`sales-order-item-qty-${index}`) as HTMLInputElement | null;
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el?.focus();
    el?.select();
  });
};

const removeProductUnit = (index: number) => {
  const item = salesOrderItemsForm.value[index];
  if (item?.id) {
    salesOrderForm.delete_item_ids.push(item.id);
  }
  salesOrderItemsForm.value.splice(index, 1);
  salesOrderItemDetailsExpanded.value.splice(index, 1);
  Object.keys(salesOrderForm.errors).forEach((key) => {
    if (key.startsWith('items.')) {
      salesOrderForm.forgetError(key as any);
    }
  });
};

const toggleSalesOrderItemDetails = (index: number) => {
  salesOrderItemDetailsExpanded.value[index] = !salesOrderItemDetailsExpanded.value[index];
};

const addPayment = () => {
  salesOrderPaymentsForm.value.push({
    id: null,
    code: '_AUTO_',
    date: '_AUTO_',
    cash_account_id: '',
    amount: 0,
    amount_allocated: 0,
    remarks: '',
  });
};

const removePayment = (index: number) => {
  const payment = salesOrderPaymentsForm.value[index];
  if (payment?.id) {
    salesOrderForm.delete_payment_ids.push(payment.id);
  }
  salesOrderPaymentsForm.value.splice(index, 1);
  Object.keys(salesOrderForm.errors).forEach((key) => {
    if (key.startsWith('payments.')) {
      salesOrderForm.forgetError(key as any);
    }
  });
};

const addRefundedPayment = () => {
  salesOrderRefundedPaymentsForm.value.push({
    id: null,
    code: '_AUTO_',
    date: '_AUTO_',
    cash_account_id: '',
    amount: 0,
    remarks: '',
  });
};

const removeRefundedPayment = (index: number) => {
  const refundedPayment = salesOrderRefundedPaymentsForm.value[index];
  if (refundedPayment?.id) {
    salesOrderForm.delete_refunded_payment_ids.push(refundedPayment.id);
  }
  salesOrderRefundedPaymentsForm.value.splice(index, 1);
  Object.keys(salesOrderForm.errors).forEach((key) => {
    if (key.startsWith('refunded_payments.')) {
      salesOrderForm.forgetError(key as any);
    }
  });
};

const getItemUnitPriceAfterDiscountPreview = (item: SalesOrderItemFormItem) =>
  Math.max(Number(item.product_unit_price || 0) - Math.max(Number(item.price_discount || 0), 0), 0);

const getItemUnitPriceSubtotalAfterDiscountPreview = (item: SalesOrderItemFormItem) =>
  Number(item.qty || 0) * getItemUnitPriceAfterDiscountPreview(item);

const getItemSubtotalAfterDiscountPreview = (item: SalesOrderItemFormItem) =>
  Math.max(
    getItemUnitPriceSubtotalAfterDiscountPreview(item) - Math.max(Number(item.subtotal_discount || 0), 0),
    0,
  );

const getItemsSubtotalAfterDiscountPreview = () =>
  salesOrderItemsForm.value.reduce((total, item) => total + getItemSubtotalAfterDiscountPreview(item), 0);

const getSalesOrderGlobalDiscountPreview = () =>
  Math.min(
    Math.max(Number(salesOrderForm.global_discount || 0), 0),
    getItemsSubtotalAfterDiscountPreview(),
  );

const getItemGlobalDiscountPreview = (item: SalesOrderItemFormItem, itemIndex: number) => {
  const totalBeforeGlobalDiscount = getItemsSubtotalAfterDiscountPreview();
  const totalGlobalDiscount = getSalesOrderGlobalDiscountPreview();

  if (totalBeforeGlobalDiscount <= 0 || totalGlobalDiscount <= 0) {
    return 0;
  }

  const allocations = salesOrderItemsForm.value.map((currentItem, index) => {
    if (index === salesOrderItemsForm.value.length - 1) {
      return 0;
    }

    return totalGlobalDiscount * (getItemSubtotalAfterDiscountPreview(currentItem) / totalBeforeGlobalDiscount);
  });

  const allocatedBeforeCurrent = allocations
    .slice(0, itemIndex)
    .reduce((total, allocation) => total + allocation, 0);

  if (itemIndex === salesOrderItemsForm.value.length - 1) {
    return Math.max(totalGlobalDiscount - allocatedBeforeCurrent, 0);
  }

  return Math.min(Math.max(allocations[itemIndex] || 0, 0), getItemSubtotalAfterDiscountPreview(item));
};

const getItemSubtotalAfterGlobalDiscountPreview = (item: SalesOrderItemFormItem, itemIndex: number) =>
  Math.max(
    getItemSubtotalAfterDiscountPreview(item) - getItemGlobalDiscountPreview(item, itemIndex),
    0,
  );

const getItemVatBasePreview = (item: SalesOrderItemFormItem, itemIndex: number) => {
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

const getItemVatPreview = (item: SalesOrderItemFormItem, itemIndex: number) => {
  const vatBase = getItemVatBasePreview(item, itemIndex);
  const vatRate = Number(item.vat_rate || 0);

  if (vatBase <= 0 || vatRate <= 0) {
    return 0;
  }

  return vatBase * (vatRate / 100);
};

const getItemTotalBeforeRoundingPreview = (item: SalesOrderItemFormItem, itemIndex: number) => {
  const subtotalAfterGlobalDiscount = getItemSubtotalAfterGlobalDiscountPreview(item, itemIndex);

  if (item.product_unit_is_price_include_vat) {
    return subtotalAfterGlobalDiscount;
  }

  return subtotalAfterGlobalDiscount + getItemVatPreview(item, itemIndex);
};

const getSalesOrderItemTotalAfterGlobalDiscountPreview = () =>
  salesOrderItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemSubtotalAfterGlobalDiscountPreview(item, itemIndex),
    0,
  );

const getSalesOrderVatBasePreview = () =>
  salesOrderItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemVatBasePreview(item, itemIndex),
    0,
  );

const getSalesOrderVatPreview = () =>
  salesOrderItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemVatPreview(item, itemIndex),
    0,
  );

const formatCurrencyPreviewValue = (value: number) => Number(value.toFixed(2));
const formatCompactNumberValue = (value: number | string, precision = 4) =>
  formatCurrency(Number(Number(value ?? 0).toFixed(precision)));

const getTotalAmountPayableBeforeRoundingPreview = () =>
  salesOrderItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemTotalBeforeRoundingPreview(item, itemIndex),
    0,
  );

const getSalesOrderAmountPayablePreview = () =>
  getTotalAmountPayableBeforeRoundingPreview() + Number(salesOrderForm.rounding || 0);

const getPaymentsTotalPreview = () =>
  salesOrderPaymentsForm.value.reduce(
    (total, payment) => total + Math.max(Number(payment.amount || 0), 0),
    0,
  );

const getRefundedPaymentsTotalPreview = () =>
  salesOrderRefundedPaymentsForm.value.reduce(
    (total, refundedPayment) => total + Math.max(Number(refundedPayment.amount || 0), 0),
    0,
  );

const getAllocatedPaymentsTotalPreview = () =>
  salesOrderPaymentsForm.value.reduce(
    (total, payment) => total + Math.max(Number(payment.amount_allocated ?? 0), 0),
    0,
  );

const getAvailablePaymentsTotalPreview = () =>
  getPaymentsTotalPreview()
  - getAllocatedPaymentsTotalPreview()
  - getRefundedPaymentsTotalPreview();

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

const onSubmit = async () => {
  if (salesOrderForm.hasErrors) {
    const firstErrorKey = Object.keys(salesOrderForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  const backupItems = [...salesOrderItemsForm.value];
  const backupPayments = [...salesOrderPaymentsForm.value];
  const backupRefundedPayments = [...salesOrderRefundedPaymentsForm.value];

  const cleanedItems: SalesOrderItemNestedUpdateRequest[] = salesOrderItemsForm.value.map((item) => ({
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
    remarks: item.remarks,
  }));

  salesOrderForm.items = cleanedItems as any;
  salesOrderForm.payments = salesOrderPaymentsForm.value.map((payment) => ({
    id: payment.id,
    code: payment.code,
    date: payment.date,
    cash_account_id: payment.cash_account_id,
    amount: payment.amount,
    remarks: payment.remarks,
  })) as any;
  salesOrderForm.refunded_payments = salesOrderRefundedPaymentsForm.value.map((refundedPayment) => ({
    id: refundedPayment.id,
    code: refundedPayment.code,
    date: refundedPayment.date,
    cash_account_id: refundedPayment.cash_account_id,
    amount: refundedPayment.amount,
    remarks: refundedPayment.remarks,
  })) as any;

  emits('loading-state', true);

  try {
    await salesOrderForm.submit();
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(t('views.sales_order.alert.update.title'), t('views.sales_order.alert.update.message'));
    router.push({ name: 'side-menu-sales-order-list' });
  } catch (error) {
    salesOrderForm.items = backupItems as any;
    salesOrderForm.payments = backupPayments as any;
    salesOrderForm.refunded_payments = backupRefundedPayments as any;
    showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
  } finally {
    emits('loading-state', false);
  }
};
</script>

<template>
  <form id="salesOrderForm" @submit.prevent="onSubmit">
    <TwoColumnsLayout :cards="cards" :using-side-tab="false" @handle-expand-card="handleExpandCard">
      <!-- card: company and branch context -->
      <template #card-items-0>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <!-- company info -->
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.company.code }}
                <br />
                {{ selectedUserLocation.company.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="salesOrderForm.company_id" />
            </div>
            <!-- branch info -->
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="salesOrderForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <!-- card: sales order header information -->
      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <!-- sales order code -->
            <div class="col-span-12 md:col-span-6 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': salesOrderForm.invalid('code') }">
                {{ t('views.sales_order.fields.code') }}
              </FormLabel>
              <FormInputCode v-model="salesOrderForm.code"
                :class="{ 'border-danger': salesOrderForm.invalid('code') }"
                :placeholder="t('views.sales_order.fields.code')" @set-auto="setCode"
                @change="salesOrderForm.validate('code')" />
              <FormErrorMessages :messages="salesOrderForm.errors.code" />
            </div>
            <!-- sales order date -->
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': salesOrderForm.invalid('date') }">
                {{ t('views.sales_order.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto v-model="salesOrderForm.date"
                :class="{ 'border-danger': salesOrderForm.invalid('date') }"
                :placeholder="t('views.sales_order.fields.date')" @change="salesOrderForm.validate('date')" />
              <FormErrorMessages :messages="salesOrderForm.errors.date" />
            </div>
            <!-- sales order due days -->
            <div class="col-span-12 md:col-span-6 lg:col-span-1">
              <FormLabel :class="{ 'text-danger': salesOrderForm.invalid('due_days') }">
                {{ t('views.sales_order.fields.due_days') }}
              </FormLabel>
              <FormInput v-model="salesOrderForm.due_days" type="number" min="0"
                :class="{ 'border-danger': salesOrderForm.invalid('due_days') }"
                @change="salesOrderForm.validate('due_days')" />
              <FormErrorMessages :messages="salesOrderForm.errors.due_days" />
            </div>
            <!-- sales order customer -->
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': salesOrderForm.invalid('customer_id') }">
                {{ t('views.sales_order.fields.customer_id') }}
              </FormLabel>
              <FormSelectSearch v-model="salesOrderForm.customer_id" v-model:search="customerSearch"
                :options="customerOptions" :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': salesOrderForm.invalid('customer_id') }"
                @change="salesOrderForm.validate('customer_id')" @search="loadCustomerDDL" @clear="clearCustomer" />
              <FormErrorMessages :messages="salesOrderForm.errors.customer_id" />
            </div>
            <!-- sales order remarks -->
            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': salesOrderForm.invalid('remarks') }">
                {{ t('views.sales_order.fields.remarks') }}
              </FormLabel>
              <FormTextarea v-model="salesOrderForm.remarks"
                :class="{ 'border-danger': salesOrderForm.invalid('remarks') }"
                @change="salesOrderForm.validate('remarks')" />
              <FormErrorMessages :messages="salesOrderForm.errors.remarks" />
            </div>
          </div>
        </div>
      </template>

      <!-- card: sales order item list and per-item breakdown -->
      <template #card-items-2>
        <div class="p-5 space-y-4">
          <FormErrorMessages :messages="salesOrderForm.errors.items" />

          <!-- items: empty state -->
          <div v-if="salesOrderItemsForm.length === 0" class="text-slate-500 text-sm">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <!-- items: repeating item blocks -->
          <div v-else>
            <div v-for="(item, index) in salesOrderItemsForm" :key="`${item.product_unit_id}-${index}`"
              class="mt-3 border-t border-slate-200/60 pt-5 first:mt-0 first:border-t-0 first:pt-0 dark:border-darkmode-400">
              <!-- item content: stacked mobile layout -->
              <div v-if="currentItemLayout === 'sm'" class="grid grid-cols-12 gap-4 gap-y-3">
                <!-- item image -->
                <div class="col-span-12">
                  <div class="form-control border rounded-md px-3">
                    <div class="flex justify-center">
                      <ProductImagePreview :image-url="item.product_unit_product_image_url"
                        wrapper-class="w-16 h-16 rounded-md overflow-hidden bg-slate-100 dark:bg-darkmode-600 flex items-center justify-center cursor-zoom-in shrink-0"
                        icon-class="w-5 h-5 text-slate-400"
                        :preview-title="item.product_unit_product_name || t('views.sales_order.fields.product_unit_id')" />
                    </div>
                  </div>
                </div>
                <!-- item product -->
                <div class="col-span-12">
                  <FormLabel :class="{ 'text-danger': invalidSalesOrderField(`items.${index}.product_unit_id`) }">
                    <span>{{ t('views.sales_order.fields.product_unit_id') }}</span>
                    <span class="ml-2 text-xs font-normal text-slate-500 dark:text-slate-400">
                      #{{ index + 1 }}
                    </span>
                    <span class="ml-2 text-xs font-normal text-slate-500 dark:text-slate-400">
                      {{ item.product_unit_product_code || '-' }}
                    </span>
                  </FormLabel>
                  <div class="flex items-center gap-2">
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
                  <FormErrorMessages :messages="getSalesOrderFieldErrors(`items.${index}.product_unit_id`)" />
                </div>
                <!-- item qty and unit -->
                <div class="col-span-12">
                  <div class="grid grid-cols-2 gap-4">
                    <!-- item qty -->
                    <div>
                      <FormLabel :class="{ 'text-danger': invalidSalesOrderField(`items.${index}.qty`) }">
                        {{ t('views.sales_order.fields.qty') }}
                      </FormLabel>
                      <FormInputCurrency :id="`sales-order-item-qty-${index}`" v-model="item.qty"
                        :allow-negative="false"
                        :class="{ 'border-danger': invalidSalesOrderField(`items.${index}.qty`) }"
                        @change="validateSalesOrderField(`items.${index}.qty`)" />
                      <FormErrorMessages :messages="getSalesOrderFieldErrors(`items.${index}.qty`)" />
                    </div>
                    <!-- item unit -->
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
                </div>
                <!-- item price -->
                <div class="col-span-12">
                  <FormLabel :class="{ 'text-danger': invalidSalesOrderField(`items.${index}.product_unit_price`) }">
                    {{ t('views.sales_order.fields.product_unit_price') }}
                  </FormLabel>
                  <FormInputCurrency v-model="item.product_unit_price" :allow-negative="false"
                    :class="{ 'border-danger': invalidSalesOrderField(`items.${index}.product_unit_price`) }"
                    @change="validateSalesOrderField(`items.${index}.product_unit_price`)" />
                  <FormErrorMessages :messages="getSalesOrderFieldErrors(`items.${index}.product_unit_price`)" />
                </div>
                <!-- item subtotal after discount -->
                <div class="col-span-12">
                  <FormLabel>{{ t('views.sales_order.fields.subtotal_after_discount') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency :model-value="getItemSubtotalAfterDiscountPreview(item)" readonly />
                    </div>
                    <div class="shrink-0">
                      <Button type="button" variant="outline-secondary"
                        class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                        @click="toggleSalesOrderItemDetails(index)">
                        {{ salesOrderItemDetailsExpanded[index] ? '▲' : '▼' }}
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
              <!-- item content: tablet layout -->
              <div v-else-if="currentItemLayout === 'md'" class="grid grid-cols-12 gap-4 gap-y-3">
                <!-- item image -->
                <div class="col-span-12 md:col-span-3">
                  <div class="form-control border rounded-md px-3">
                    <div class="flex justify-center">
                      <ProductImagePreview :image-url="item.product_unit_product_image_url"
                        wrapper-class="w-16 h-16 rounded-md overflow-hidden bg-slate-100 dark:bg-darkmode-600 flex items-center justify-center cursor-zoom-in shrink-0"
                        icon-class="w-5 h-5 text-slate-400"
                        :preview-title="item.product_unit_product_name || t('views.sales_order.fields.product_unit_id')" />
                    </div>
                  </div>
                </div>
                <!-- item product -->
                <div class="col-span-12 md:col-span-9">
                  <FormLabel :class="{ 'text-danger': invalidSalesOrderField(`items.${index}.product_unit_id`) }">
                    <span>{{ t('views.sales_order.fields.product_unit_id') }}</span>
                    <span class="ml-2 text-xs font-normal text-slate-500 dark:text-slate-400">
                      #{{ index + 1 }}
                    </span>
                    <span class="ml-2 text-xs font-normal text-slate-500 dark:text-slate-400">
                      {{ item.product_unit_product_code || '-' }}
                    </span>
                  </FormLabel>
                  <div class="flex items-center gap-2">
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
                  <FormErrorMessages :messages="getSalesOrderFieldErrors(`items.${index}.product_unit_id`)" />
                </div>
                <!-- item qty and unit -->
                <div class="col-span-12 md:col-span-4">
                  <div class="grid grid-cols-2 gap-4">
                    <!-- item qty -->
                    <div>
                      <FormLabel :class="{ 'text-danger': invalidSalesOrderField(`items.${index}.qty`) }">
                        {{ t('views.sales_order.fields.qty') }}
                      </FormLabel>
                      <FormInputCurrency :id="`sales-order-item-qty-${index}`" v-model="item.qty"
                        :allow-negative="false"
                        :class="{ 'border-danger': invalidSalesOrderField(`items.${index}.qty`) }"
                        @change="validateSalesOrderField(`items.${index}.qty`)" />
                      <FormErrorMessages :messages="getSalesOrderFieldErrors(`items.${index}.qty`)" />
                    </div>
                    <!-- item unit -->
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
                </div>
                <!-- item price -->
                <div class="col-span-12 md:col-span-4">
                  <FormLabel :class="{ 'text-danger': invalidSalesOrderField(`items.${index}.product_unit_price`) }">
                    {{ t('views.sales_order.fields.product_unit_price') }}
                  </FormLabel>
                  <FormInputCurrency v-model="item.product_unit_price" :allow-negative="false"
                    :class="{ 'border-danger': invalidSalesOrderField(`items.${index}.product_unit_price`) }"
                    @change="validateSalesOrderField(`items.${index}.product_unit_price`)" />
                  <FormErrorMessages :messages="getSalesOrderFieldErrors(`items.${index}.product_unit_price`)" />
                </div>
                <!-- item subtotal after discount -->
                <div class="col-span-12 md:col-span-4">
                  <FormLabel>{{ t('views.sales_order.fields.subtotal_after_discount') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency :model-value="getItemSubtotalAfterDiscountPreview(item)" readonly />
                    </div>
                    <div class="shrink-0">
                      <Button type="button" variant="outline-secondary"
                        class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                        @click="toggleSalesOrderItemDetails(index)">
                        {{ salesOrderItemDetailsExpanded[index] ? '▲' : '▼' }}
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
              <!-- item content: desktop summary row -->
              <div v-else class="grid grid-cols-12 gap-4 gap-y-3">
                <!-- item image -->
                <div class="col-span-12 lg:col-span-1">
                  <div class="form-control border rounded-md px-3">
                    <div class="flex justify-center">
                      <ProductImagePreview :image-url="item.product_unit_product_image_url"
                        wrapper-class="w-16 h-16 rounded-md overflow-hidden bg-slate-100 dark:bg-darkmode-600 flex items-center justify-center cursor-zoom-in shrink-0"
                        icon-class="w-5 h-5 text-slate-400"
                        :preview-title="item.product_unit_product_name || t('views.sales_order.fields.product_unit_id')" />
                    </div>
                  </div>
                </div>
                <!-- item product -->
                <div class="col-span-12 lg:col-span-4">
                  <FormLabel :class="{ 'text-danger': invalidSalesOrderField(`items.${index}.product_unit_id`) }">
                    <span>{{ t('views.sales_order.fields.product_unit_id') }}</span>
                    <span class="ml-2 text-xs font-normal text-slate-500 dark:text-slate-400">
                      #{{ index + 1 }}
                    </span>
                    <span class="ml-2 text-xs font-normal text-slate-500 dark:text-slate-400">
                      {{ item.product_unit_product_code || '-' }}
                    </span>
                  </FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1">
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
                  <FormErrorMessages :messages="getSalesOrderFieldErrors(`items.${index}.product_unit_id`)" />
                </div>
                <!-- item qty and unit -->
                <div class="col-span-12 lg:col-span-2">
                  <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <!-- item qty -->
                    <div>
                      <FormLabel :class="{ 'text-danger': invalidSalesOrderField(`items.${index}.qty`) }">
                        {{ t('views.sales_order.fields.qty') }}
                      </FormLabel>
                      <FormInputCurrency :id="`sales-order-item-qty-${index}`" v-model="item.qty"
                        :allow-negative="false"
                        :class="{ 'border-danger': invalidSalesOrderField(`items.${index}.qty`) }"
                        @change="validateSalesOrderField(`items.${index}.qty`)" />
                      <FormErrorMessages :messages="getSalesOrderFieldErrors(`items.${index}.qty`)" />
                    </div>
                    <!-- item unit -->
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
                </div>
                <!-- item price -->
                <div class="col-span-12 lg:col-span-2">
                  <FormLabel :class="{ 'text-danger': invalidSalesOrderField(`items.${index}.product_unit_price`) }">
                    {{ t('views.sales_order.fields.product_unit_price') }}
                  </FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency v-model="item.product_unit_price" :allow-negative="false"
                        :class="{ 'border-danger': invalidSalesOrderField(`items.${index}.product_unit_price`) }"
                        @change="validateSalesOrderField(`items.${index}.product_unit_price`)" />
                    </div>
                  </div>
                  <FormErrorMessages :messages="getSalesOrderFieldErrors(`items.${index}.product_unit_price`)" />
                </div>
                <!-- item subtotal after discount -->
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.sales_order.fields.subtotal_after_discount') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency :model-value="getItemSubtotalAfterDiscountPreview(item)" readonly />
                    </div>
                    <div class="shrink-0">
                      <Button type="button" variant="outline-secondary"
                        class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                        @click="toggleSalesOrderItemDetails(index)">
                        {{ salesOrderItemDetailsExpanded[index] ? '▲' : '▼' }}
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

              <!-- item details: breakdown panels -->
              <div v-if="salesOrderItemDetailsExpanded[index]" class="mt-4 grid grid-cols-12 gap-4">
                <div class="col-span-12 lg:col-span-6">
                  <!-- panel: tax and additional item metadata -->
                  <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-4">
                    <div class="font-medium text-sm">{{ t('views.sales_order.fields.item_additional_details') }}</div>

                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-5">
                        <FormLabel>{{
                          t('views.sales_order.fields.product_unit_is_price_include_vat') }}</FormLabel>
                        <FormSwitch>
                          <FormSwitch.Input v-model="item.product_unit_is_price_include_vat" type="checkbox" />
                        </FormSwitch>
                      </div>
                      <div class="col-span-12 md:col-span-7">
                        <FormLabel :class="{ 'text-danger': invalidSalesOrderField(`items.${index}.vat_profile_id`) }">
                          {{ t('views.sales_order.fields.vat_profile_id') }}
                        </FormLabel>
                        <FormSelectSearch v-model="item.vat_profile_id" v-model:search="vatProfileSearch"
                          :options="vatProfileOptions" :placeholder="t('components.dropdown.placeholder')"
                          :class="{ 'border-danger': invalidSalesOrderField(`items.${index}.vat_profile_id`) }"
                          @change="syncVatProfile(index)" @search="loadVatProfileDDL" @clear="clearVatProfile(index)" />
                        <FormErrorMessages :messages="getSalesOrderFieldErrors(`items.${index}.vat_profile_id`)" />
                      </div>
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel :class="{ 'text-danger': invalidSalesOrderField(`items.${index}.vat_rate`) }">
                          {{ t('views.sales_order.fields.vat_rate') }}
                        </FormLabel>
                        <FormInputCurrency v-model="item.vat_rate" :allow-negative="false"
                          :class="{ 'border-danger': invalidSalesOrderField(`items.${index}.vat_rate`) }"
                          @change="validateSalesOrderField(`items.${index}.vat_rate`)" />
                        <FormErrorMessages :messages="getSalesOrderFieldErrors(`items.${index}.vat_rate`)" />
                      </div>
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel
                          :class="{ 'text-danger': invalidSalesOrderField(`items.${index}.vat_base_numerator`) }">
                          {{ t('views.sales_order.fields.vat_base_numerator') }}
                        </FormLabel>
                        <FormInput v-model="item.vat_base_numerator" type="number" min="1"
                          :class="{ 'border-danger': invalidSalesOrderField(`items.${index}.vat_base_numerator`) }"
                          @change="validateSalesOrderField(`items.${index}.vat_base_numerator`)" />
                        <FormErrorMessages :messages="getSalesOrderFieldErrors(`items.${index}.vat_base_numerator`)" />
                      </div>
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel
                          :class="{ 'text-danger': invalidSalesOrderField(`items.${index}.vat_base_denominator`) }">
                          {{ t('views.sales_order.fields.vat_base_denominator') }}
                        </FormLabel>
                        <FormInput v-model="item.vat_base_denominator" type="number" min="1"
                          :class="{ 'border-danger': invalidSalesOrderField(`items.${index}.vat_base_denominator`) }"
                          @change="validateSalesOrderField(`items.${index}.vat_base_denominator`)" />
                        <FormErrorMessages
                          :messages="getSalesOrderFieldErrors(`items.${index}.vat_base_denominator`)" />
                      </div>
                      <div class="col-span-12">
                        <FormLabel :class="{ 'text-danger': invalidSalesOrderField(`items.${index}.remarks`) }">
                          {{ t('views.sales_order.fields.remarks') }}
                        </FormLabel>
                        <FormTextarea v-model="item.remarks"
                          :class="{ 'border-danger': invalidSalesOrderField(`items.${index}.remarks`) }"
                          @change="validateSalesOrderField(`items.${index}.remarks`)" />
                        <FormErrorMessages :messages="getSalesOrderFieldErrors(`items.${index}.remarks`)" />
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-span-12 lg:col-span-6">
                  <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-5">
                    <!-- panel: price and discount breakdown -->
                    <div class="font-medium text-sm">{{ t('views.sales_order.fields.item_price_breakdown') }}</div>

                    <div class="space-y-3">
                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                          {{ t('views.sales_order.fields.price_discount') }}
                        </div>
                        <div class="col-span-12 md:col-span-8">
                          <FormInputCurrency v-model="item.price_discount" :allow-negative="false"
                            :class="{ 'border-danger': invalidSalesOrderField(`items.${index}.price_discount`) }"
                            @change="validateSalesOrderField(`items.${index}.price_discount`)" />
                          <FormErrorMessages :messages="getSalesOrderFieldErrors(`items.${index}.price_discount`)" />
                        </div>
                      </div>

                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                          {{ t('views.sales_order.fields.price_after_discount') }}
                        </div>
                        <div class="col-span-12 md:col-span-8">
                          <FormInputCurrency :model-value="getItemUnitPriceAfterDiscountPreview(item)" readonly />
                        </div>
                      </div>

                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                          {{ t('views.sales_order.fields.subtotal') }}
                        </div>
                        <div class="col-span-12 md:col-span-8">
                          <FormInputCurrency :model-value="getItemUnitPriceSubtotalAfterDiscountPreview(item)"
                            readonly />
                        </div>
                      </div>
                    </div>

                    <div class="space-y-3">
                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                          {{ t('views.sales_order.fields.subtotal_discount') }}
                        </div>
                        <div class="col-span-12 md:col-span-8">
                          <FormInputCurrency v-model="item.subtotal_discount" :allow-negative="false"
                            :class="{ 'border-danger': invalidSalesOrderField(`items.${index}.subtotal_discount`) }"
                            @change="validateSalesOrderField(`items.${index}.subtotal_discount`)" />
                          <FormErrorMessages :messages="getSalesOrderFieldErrors(`items.${index}.subtotal_discount`)" />
                        </div>
                      </div>

                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                          {{ t('views.sales_order.fields.subtotal_after_discount') }}
                        </div>
                        <div class="col-span-12 md:col-span-8">
                          <FormInputCurrency :model-value="getItemSubtotalAfterDiscountPreview(item)" readonly />
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="flex justify-end">
            <Button type="button" variant="outline-primary" @click="openAddProductUnit">
              <Lucide icon="Plus" class="w-4 h-4 mr-1" />
              {{ t('components.buttons.create_new') }}
            </Button>
          </div>
        </div>
      </template>

      <!-- card: financial summary -->
      <template #card-items-3>
        <div class="p-5 space-y-4">
          <!-- summary card: item totals, global discount, and down payments -->
          <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-4">
            <!-- summary: total of all items after item-level discounts -->
            <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
              <!-- summary spacer: keeps totals aligned to the right on desktop -->
              <div class="col-span-12 lg:col-span-9"></div>
              <div class="col-span-12 lg:col-span-3">
                <FormLabel>{{ t('views.sales_order.fields.items_subtotal_after_discount') }}</FormLabel>
                <FormInputCurrency :model-value="getItemsSubtotalAfterDiscountPreview()" readonly />
              </div>
            </div>

            <!-- summary: adjustments, totals, and payment flow -->
            <div class="space-y-4">
              <!-- global discount: single nominal input -->
              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': salesOrderForm.invalid('global_discount') }">
                    {{ t('views.sales_order.fields.global_discount') }}
                  </FormLabel>
                  <FormInputCurrency v-model="salesOrderForm.global_discount" :allow-negative="false"
                    :class="{ 'border-danger': salesOrderForm.invalid('global_discount') }"
                    @change="salesOrderForm.validate('global_discount')" />
                  <FormErrorMessages :messages="salesOrderForm.errors.global_discount" />
                </div>
              </div>

              <template v-if="isTotalsBreakdownExpanded">
                <!-- summary: total after global discount -->
                <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                  <div class="col-span-12 lg:col-span-9"></div>
                  <div class="col-span-12 lg:col-span-3">
                    <FormLabel>{{ t('views.sales_order.fields.item_total_after_global_discount') }}</FormLabel>
                    <FormInputCurrency :model-value="getSalesOrderItemTotalAfterGlobalDiscountPreview()" readonly />
                  </div>
                </div>

                <!-- summary: vat base -->
                <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                  <div class="col-span-12 lg:col-span-9"></div>
                  <div class="col-span-12 lg:col-span-3">
                    <FormLabel>{{ t('views.sales_order.fields.vat_base') }}</FormLabel>
                    <FormInputCurrency :model-value="formatCurrencyPreviewValue(getSalesOrderVatBasePreview())"
                      readonly />
                  </div>
                </div>

                <!-- summary: vat -->
                <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                  <div class="col-span-12 lg:col-span-9"></div>
                  <div class="col-span-12 lg:col-span-3">
                    <FormLabel>{{ t('views.sales_order.fields.vat') }}</FormLabel>
                    <FormInputCurrency :model-value="formatCurrencyPreviewValue(getSalesOrderVatPreview())"
                      readonly />
                  </div>
                </div>

                <!-- summary: rounding adjustment before amount payable -->
                <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                  <div class="col-span-12 lg:col-span-9"></div>
                  <div class="col-span-12 lg:col-span-3">
                    <FormLabel :class="{ 'text-danger': salesOrderForm.invalid('rounding') }">
                      {{ t('views.sales_order.fields.rounding') }}
                    </FormLabel>
                    <FormInputCurrency v-model="salesOrderForm.rounding"
                      :class="{ 'border-danger': salesOrderForm.invalid('rounding') }"
                      @change="salesOrderForm.validate('rounding')" />
                    <FormErrorMessages :messages="salesOrderForm.errors.rounding" />
                  </div>
                </div>
              </template>

              <!-- summary: amount payable after applying global discount and rounding -->
              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.sales_order.fields.amount_payable') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="shrink-0">
                      <Button type="button" variant="outline-secondary"
                        class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                        @click="isTotalsBreakdownExpanded = !isTotalsBreakdownExpanded">
                        {{ isTotalsBreakdownExpanded ? '▲' : '▼' }}
                      </Button>
                    </div>
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency :model-value="getSalesOrderAmountPayablePreview()" readonly />
                    </div>
                  </div>
                </div>
              </div>

              <!-- summary: down payment editor and aggregates -->
              <div class="space-y-4">
                <!-- payments: editor -->
                <div v-if="isPaymentEditorExpanded" class="space-y-4">
                  <FormErrorMessages :messages="salesOrderForm.errors.payments" />

                  <!-- payments: empty state -->
                  <div v-if="salesOrderPaymentsForm.length === 0" class="text-right text-slate-500 text-sm">
                    {{ t('components.data-list.data_not_found') }}
                  </div>

                  <!-- payments: list -->
                  <div v-else class="space-y-4">
                    <div v-for="(payment, index) in salesOrderPaymentsForm" :key="`payment-${index}`"
                      class="space-y-3">
                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <!-- payment spacer: keeps editor fields aligned to the right side -->
                        <div class="col-span-12 md:col-span-6 lg:col-span-2"></div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-2">
                          <FormLabel :class="{ 'text-danger': invalidSalesOrderField(`payments.${index}.code`) }">
                            {{ t('views.sales_order.fields.code') }}
                          </FormLabel>
                          <FormInputCode v-model="payment.code"
                            :class="{ 'border-danger': invalidSalesOrderField(`payments.${index}.code`) }"
                            :placeholder="t('views.sales_order.fields.code')" @set-auto="setPaymentCode(index)"
                            @change="validateSalesOrderField(`payments.${index}.code`)" />
                          <FormErrorMessages :messages="getSalesOrderFieldErrors(`payments.${index}.code`)" />
                        </div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-4">
                          <FormLabel :class="{ 'text-danger': invalidSalesOrderField(`payments.${index}.date`) }">
                            {{ t('views.sales_order.fields.date') }}
                          </FormLabel>
                          <FormInputDateTimeAuto v-model="payment.date"
                            :class="{ 'border-danger': invalidSalesOrderField(`payments.${index}.date`) }"
                            :placeholder="t('views.sales_order.fields.date')"
                            @change="validateSalesOrderField(`payments.${index}.date`)" />
                          <FormErrorMessages :messages="getSalesOrderFieldErrors(`payments.${index}.date`)" />
                        </div>
                        <div class="col-span-12 md:col-span-8 lg:col-span-2">
                          <FormLabel
                            :class="{ 'text-danger': invalidSalesOrderField(`payments.${index}.cash_account_id`) }">
                            {{ t('views.sales_order.fields.cash_account_id') }}
                          </FormLabel>
                          <FormSelectSearch v-model="payment.cash_account_id" v-model:search="cashAccountSearch"
                            :options="cashAccountOptions" :placeholder="t('components.dropdown.placeholder')"
                            :class="{ 'border-danger': invalidSalesOrderField(`payments.${index}.cash_account_id`) }"
                            @change="validateSalesOrderField(`payments.${index}.cash_account_id`)"
                            @search="loadCashAccountDDL" @clear="clearCashAccount(index)" />
                          <FormErrorMessages
                            :messages="getSalesOrderFieldErrors(`payments.${index}.cash_account_id`)" />
                        </div>
                        <div class="col-span-12 md:col-span-4 lg:col-span-2">
                          <FormLabel :class="{ 'text-danger': invalidSalesOrderField(`payments.${index}.amount`) }">
                            {{ t('views.sales_order.fields.amount') }}
                          </FormLabel>
                          <div class="flex items-start gap-2">
                            <div class="flex-1 min-w-0">
                              <FormInputCurrency v-model="payment.amount" :allow-negative="false"
                                :class="{ 'border-danger': invalidSalesOrderField(`payments.${index}.amount`) }"
                                @change="validateSalesOrderField(`payments.${index}.amount`)" />
                            </div>
                            <div class="shrink-0">
                              <Button type="button" variant="outline-secondary"
                                class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                                @click="removePayment(index)">
                                <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                              </Button>
                            </div>
                          </div>
                          <FormErrorMessages :messages="getSalesOrderFieldErrors(`payments.${index}.amount`)" />
                        </div>
                      </div>
                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <!-- payment spacer: keeps remarks width consistent with fields above -->
                        <div class="col-span-12 lg:col-span-2"></div>
                        <div class="col-span-12 lg:col-span-7">
                          <FormLabel :class="{ 'text-danger': invalidSalesOrderField(`payments.${index}.remarks`) }">
                            {{ t('views.sales_order.fields.remarks') }}
                          </FormLabel>
                          <FormTextarea v-model="payment.remarks"
                            :class="{ 'border-danger': invalidSalesOrderField(`payments.${index}.remarks`) }"
                            @change="validateSalesOrderField(`payments.${index}.remarks`)" />
                          <FormErrorMessages :messages="getSalesOrderFieldErrors(`payments.${index}.remarks`)" />
                        </div>
                        <div class="col-span-12 lg:col-span-3">
                          <FormLabel>{{ t('views.sales_order.fields.amount_allocated') }}</FormLabel>
                          <FormInputCurrency :model-value="Number(payment.amount_allocated ?? 0)" readonly />
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- payments: add action -->
                  <div class="flex justify-end">
                    <Button type="button" variant="outline-primary" @click="addPayment">
                      <Lucide icon="Plus" class="w-4 h-4 mr-1" />
                      {{ t('components.buttons.create_new') }}
                    </Button>
                  </div>
                </div>

                <!-- payments: collapsed summary row and expand trigger -->
                <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                  <div class="col-span-12 lg:col-span-9"></div>
                  <div class="col-span-12 lg:col-span-3">
                    <FormLabel>{{ t('views.sales_order.fields.amount_paid_down_payment') }}</FormLabel>
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

                <div v-if="isRefundedPaymentEditorExpanded" class="space-y-4">
                  <FormErrorMessages :messages="salesOrderForm.errors.refunded_payments" />

                  <div v-if="salesOrderRefundedPaymentsForm.length === 0" class="text-right text-slate-500 text-sm">
                    {{ t('components.data-list.data_not_found') }}
                  </div>

                  <div v-else class="space-y-4">
                    <div v-for="(refundedPayment, index) in salesOrderRefundedPaymentsForm"
                      :key="`refunded-payment-${index}`" class="space-y-3">
                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 md:col-span-6 lg:col-span-2"></div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-2">
                          <FormLabel
                            :class="{ 'text-danger': invalidSalesOrderField(`refunded_payments.${index}.code`) }">
                            {{ t('views.sales_order.fields.code') }}
                          </FormLabel>
                          <FormInputCode v-model="refundedPayment.code"
                            :class="{ 'border-danger': invalidSalesOrderField(`refunded_payments.${index}.code`) }"
                            :placeholder="t('views.sales_order.fields.code')"
                            @set-auto="setRefundedPaymentCode(index)"
                            @change="validateSalesOrderField(`refunded_payments.${index}.code`)" />
                          <FormErrorMessages
                            :messages="getSalesOrderFieldErrors(`refunded_payments.${index}.code`)" />
                        </div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-4">
                          <FormLabel
                            :class="{ 'text-danger': invalidSalesOrderField(`refunded_payments.${index}.date`) }">
                            {{ t('views.sales_order.fields.date') }}
                          </FormLabel>
                          <FormInputDateTimeAuto v-model="refundedPayment.date"
                            :class="{ 'border-danger': invalidSalesOrderField(`refunded_payments.${index}.date`) }"
                            :placeholder="t('views.sales_order.fields.date')"
                            @change="validateSalesOrderField(`refunded_payments.${index}.date`)" />
                          <FormErrorMessages
                            :messages="getSalesOrderFieldErrors(`refunded_payments.${index}.date`)" />
                        </div>
                        <div class="col-span-12 md:col-span-8 lg:col-span-2">
                          <FormLabel
                            :class="{ 'text-danger': invalidSalesOrderField(`refunded_payments.${index}.cash_account_id`) }">
                            {{ t('views.sales_order.fields.cash_account_id') }}
                          </FormLabel>
                          <FormSelectSearch v-model="refundedPayment.cash_account_id"
                            v-model:search="cashAccountSearch" :options="cashAccountOptions"
                            :placeholder="t('components.dropdown.placeholder')"
                            :class="{ 'border-danger': invalidSalesOrderField(`refunded_payments.${index}.cash_account_id`) }"
                            @change="validateSalesOrderField(`refunded_payments.${index}.cash_account_id`)"
                            @search="loadCashAccountDDL" @clear="clearRefundedCashAccount(index)" />
                          <FormErrorMessages
                            :messages="getSalesOrderFieldErrors(`refunded_payments.${index}.cash_account_id`)" />
                        </div>
                        <div class="col-span-12 md:col-span-4 lg:col-span-2">
                          <FormLabel
                            :class="{ 'text-danger': invalidSalesOrderField(`refunded_payments.${index}.amount`) }">
                            {{ t('views.sales_order.fields.amount') }}
                          </FormLabel>
                          <div class="flex items-start gap-2">
                            <div class="flex-1 min-w-0">
                              <FormInputCurrency v-model="refundedPayment.amount" :allow-negative="false"
                                :class="{ 'border-danger': invalidSalesOrderField(`refunded_payments.${index}.amount`) }"
                                @change="validateSalesOrderField(`refunded_payments.${index}.amount`)" />
                            </div>
                            <div class="shrink-0">
                              <Button type="button" variant="outline-secondary"
                                class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                                @click="removeRefundedPayment(index)">
                                <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                              </Button>
                            </div>
                          </div>
                          <FormErrorMessages
                            :messages="getSalesOrderFieldErrors(`refunded_payments.${index}.amount`)" />
                        </div>
                      </div>
                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 lg:col-span-2"></div>
                        <div class="col-span-12 lg:col-span-10">
                          <FormLabel
                            :class="{ 'text-danger': invalidSalesOrderField(`refunded_payments.${index}.remarks`) }">
                            {{ t('views.sales_order.fields.remarks') }}
                          </FormLabel>
                          <FormTextarea v-model="refundedPayment.remarks"
                            :class="{ 'border-danger': invalidSalesOrderField(`refunded_payments.${index}.remarks`) }"
                            @change="validateSalesOrderField(`refunded_payments.${index}.remarks`)" />
                          <FormErrorMessages
                            :messages="getSalesOrderFieldErrors(`refunded_payments.${index}.remarks`)" />
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="flex justify-end">
                    <Button type="button" variant="outline-primary" @click="addRefundedPayment">
                      <Lucide icon="Plus" class="w-4 h-4 mr-1" />
                      {{ t('components.buttons.create_new') }}
                    </Button>
                  </div>
                </div>

                <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                  <div class="col-span-12 lg:col-span-9"></div>
                  <div class="col-span-12 lg:col-span-3">
                    <FormLabel>{{ t('views.sales_order.fields.amount_refunded_down_payment') }}</FormLabel>
                    <div class="flex items-start gap-2">
                      <div class="shrink-0">
                        <Button type="button" variant="outline-secondary"
                          class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                          @click="isRefundedPaymentEditorExpanded = !isRefundedPaymentEditorExpanded">
                          {{ isRefundedPaymentEditorExpanded ? '▲' : '▼' }}
                        </Button>
                      </div>
                      <div class="flex-1 min-w-0">
                        <FormInputCurrency :model-value="getRefundedPaymentsTotalPreview()" readonly />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                  <div class="col-span-12 lg:col-span-9"></div>
                  <div class="col-span-12 lg:col-span-3">
                    <FormLabel>{{ t('views.sales_order.fields.amount_allocated_down_payment') }}</FormLabel>
                    <FormInputCurrency :model-value="getAllocatedPaymentsTotalPreview()" readonly />
                  </div>
                </div>

                <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                  <div class="col-span-12 lg:col-span-9"></div>
                  <div class="col-span-12 lg:col-span-3">
                    <FormLabel>{{ t('views.sales_order.fields.amount_available_down_payment') }}</FormLabel>
                    <FormInputCurrency :model-value="getAvailablePaymentsTotalPreview()" readonly />
                  </div>
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
            :disabled="salesOrderForm.validating || salesOrderForm.hasErrors">
            <Lucide v-if="salesOrderForm.validating" icon="Loader" class="w-4 h-4 mr-2 animate-spin" />
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
    :title="t('views.sales_order.fields.product_unit_id')" :search-text="productSearchText"
    :is-searching="isSearchingProductUnit" :options="productUnitOptions" :columns="productUnitDialogColumns"
    @update:search-text="productSearchText = $event" @search="searchProductUnits"
    @select="selectProductUnit($event as ProductUnitOption)" @close="showProductUnitModal = false"
    @after-leave="handleProductUnitModalAfterLeave" />
</template>
