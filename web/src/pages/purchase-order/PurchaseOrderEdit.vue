<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
import { CardState } from '@/types/enums/CardState';
import { ViewMode } from '@/types/enums/ViewMode';
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
  InputGroup,
} from '@/components/Base/Form';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import ProductImagePreview from '@/components/Product/ProductImagePreview.vue';
import ProductUnitSelectSearch, {
  type ProductUnitSelectOption,
} from '@/components/Product/ProductUnitSelectSearch.vue';
import CashAccountService from '@/services/CashAccountService';
import ProductService from '@/services/ProductService';
import PurchaseOrderService from '@/services/PurchaseOrderService';
import SupplierService from '@/services/SupplierService';
import VatProfileService from '@/services/VatProfileService';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { DropDownOption } from '@/types/models/DropDownOption';
import { NotificationData } from '@/types/models/NotificationData';
import { PurchaseOrder } from '@/types/models/PurchaseOrder';
import {
  PurchaseOrderItemNestedUpdateRequest,
  PurchaseOrderPaymentNestedUpdateRequest,
  PurchaseOrderPaymentRefundNestedUpdateRequest,
} from '@/types/services/purchase-order/PurchaseOrderRequest';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { convertErrorTypeToAlertListType, formatCurrency, formatDate } from '@/utils/helper';

type PurchaseOrderItemFormItem = PurchaseOrderItemNestedUpdateRequest & {
  product_unit_product_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_product_image_url?: string | null;
  product_unit_unit_name?: string | null;
  product_unit_base_unit_name?: string | null;
  vat_profile_name?: string | null;
};

type ProductUnitOption = ProductUnitSelectOption & {
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

const purchaseOrderService = new PurchaseOrderService();
const supplierService = new SupplierService();
const vatProfileService = new VatProfileService();
const cashAccountService = new CashAccountService();
const productService = new ProductService();

const purchaseOrderForm = purchaseOrderService.usePurchaseOrderEditForm(route.params.ulid as string);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const purchaseOrderData = ref<PurchaseOrder | null>(null);

const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'views.purchase_order.field_groups.company_info', state: CardState.Expanded },
  { title: 'views.purchase_order.field_groups.purchase_order_data', state: CardState.Expanded },
  { title: 'views.purchase_order.field_groups.items', state: CardState.Expanded },
  { title: 'views.purchase_order.field_groups.summary', state: CardState.Expanded },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const supplierDDL = ref<Array<DropDownOption> | null>(null);
const supplierSearch = ref<string>('');
const supplierOptions = computed(() =>
  (supplierDDL.value ?? []).map((item) => ({
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

const purchaseOrderItemDetailsExpanded = ref<boolean[]>([]);
const purchaseOrderItemBreakdownExpanded = ref<boolean[]>([]);
const purchaseOrderItemAdditionalExpanded = ref<boolean[]>([]);

const purchaseOrderItemsForm = computed<PurchaseOrderItemFormItem[]>(
  () => purchaseOrderForm.items as PurchaseOrderItemFormItem[],
);

const isTotalsBreakdownExpanded = ref(false);
const purchaseOrderPaymentsForm = computed<PurchaseOrderPaymentNestedUpdateRequest[]>(
  () => purchaseOrderForm.payments as PurchaseOrderPaymentNestedUpdateRequest[],
);
const isPaymentEditorExpanded = ref(false);
const purchaseOrderRefundedPaymentsForm = computed<PurchaseOrderPaymentRefundNestedUpdateRequest[]>(
  () => purchaseOrderForm.refunded_payments as PurchaseOrderPaymentRefundNestedUpdateRequest[],
);
const isRefundedPaymentEditorExpanded = ref(false);

const invalidPurchaseOrderField = (field: string) => purchaseOrderForm.invalid(field as any);
const validatePurchaseOrderField = (field: string) => purchaseOrderForm.validate(field as any);
const getPurchaseOrderFieldErrors = (field: string) =>
  (purchaseOrderForm.errors as Record<string, string | undefined>)[field];

const handleExpandCard = (index: number) => {
  cards.value[index].state =
    cards.value[index].state === CardState.Collapsed ? CardState.Expanded : CardState.Collapsed;
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
    await Promise.all([loadSupplierDDL(), loadVatProfileDDL(), loadCashAccountDDL()]);
    await loadData();
    await Promise.all([loadSupplierDDL(), loadVatProfileDDL(), loadCashAccountDDL()]);
  } finally {
    emits('loading-state', false);
  }
});

const setCode = () => {
  purchaseOrderForm.forgetError('code');
  purchaseOrderForm.setData({
    code: purchaseOrderForm.code === '_AUTO_' ? '' : '_AUTO_',
  });
};

const setPaymentCode = (index: number) => {
  purchaseOrderForm.forgetError(`payments.${index}.code` as any);
  purchaseOrderPaymentsForm.value[index].code =
    purchaseOrderPaymentsForm.value[index].code === '_AUTO_' ? '' : '_AUTO_';
};

const setRefundedPaymentCode = (index: number) => {
  purchaseOrderForm.forgetError(`refunded_payments.${index}.code` as any);
  purchaseOrderRefundedPaymentsForm.value[index].code =
    purchaseOrderRefundedPaymentsForm.value[index].code === '_AUTO_' ? '' : '_AUTO_';
};

const appendDDL = (
  target: typeof supplierDDL | typeof cashAccountDDL,
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

const loadSupplierDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await supplierService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    status: undefined,
    include_id: purchaseOrderData.value?.supplier?.id,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    supplierDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }

  appendDDL(supplierDDL, purchaseOrderData.value?.supplier);
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

  (purchaseOrderData.value?.items ?? []).forEach((item) => appendVatProfileOption({
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

  (purchaseOrderData.value?.payments ?? []).forEach((item) => appendDDL(cashAccountDDL, item.cash_account));
  (purchaseOrderData.value?.refunded_payments ?? []).forEach((item) => appendDDL(cashAccountDDL, item.cash_account));
};

const clearSupplier = () => {
  purchaseOrderForm.setData({ supplier_id: null });
  purchaseOrderForm.forgetError('supplier_id');
  purchaseOrderForm.validate('supplier_id');
};

const applyVatProfileToItem = (
  item: PurchaseOrderItemFormItem,
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
  const item = purchaseOrderItemsForm.value[index];
  if (!item) return;
  applyVatProfileToItem(item, null);
  purchaseOrderForm.validate(`items.${index}.vat_profile_id` as any);
};

const syncVatProfile = (index: number) => {
  const item = purchaseOrderItemsForm.value[index];
  if (!item) return;

  applyVatProfileToItem(item, item.vat_profile_id ?? null);
  purchaseOrderForm.validate(`items.${index}.vat_profile_id` as any);
};

const clearCashAccount = (index: number) => {
  const payment = purchaseOrderPaymentsForm.value[index];
  if (!payment) return;
  payment.cash_account_id = '';
  purchaseOrderForm.validate(`payments.${index}.cash_account_id` as any);
};

const clearRefundedCashAccount = (index: number) => {
  const refundedPayment = purchaseOrderRefundedPaymentsForm.value[index];
  if (!refundedPayment) return;
  refundedPayment.cash_account_id = '';
  purchaseOrderForm.validate(`refunded_payments.${index}.cash_account_id` as any);
};

const loadData = async () => {
  const ulid = route.params.ulid as string | undefined;
  if (!ulid) return;

  const result = await purchaseOrderService.read(ulid);

  if (result.success && result.data) {
    const data = result.data;
    purchaseOrderData.value = data;

    const items: PurchaseOrderItemFormItem[] = (data.items || []).map((item: any) => ({
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

    purchaseOrderForm.setData({
      company_id: data.company.id,
      branch_id: data.branch.id,
      code: data.code,
      date: formatDate(data.date, 'YYYY-MM-DD HH:mm:ss'),
      due_days: data.due_days,
      supplier_id: data.supplier?.id ?? null,
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
    purchaseOrderItemDetailsExpanded.value = items.map(() => false);
    purchaseOrderItemBreakdownExpanded.value = items.map(() => false);
    purchaseOrderItemAdditionalExpanded.value = items.map(() => false);
  }
};

const fetchProductUnitOptions = async (search: string): Promise<Array<ProductUnitOption>> => {
  if (!selectedUserLocation.value) return [];

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
  });

  if (!result.success || !result.data) return [];

  const products = result.data.data as any[];
  return products.flatMap((product: any) => {
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
      is_use_serial_number: Boolean(product.is_use_serial_number),
      price: Number(unit.price ?? 0),
      product_unit_is_price_include_vat: Boolean(product.is_price_include_vat),
      vat_profile_id: product.default_vat_profile?.id ?? null,
      vat_profile_name: product.default_vat_profile?.name ?? null,
      vat_rate: Number(product.default_vat_profile?.vat_rate ?? 0),
      vat_base_numerator: Number(product.default_vat_profile?.vat_base_numerator ?? 1),
      vat_base_denominator: Number(product.default_vat_profile?.vat_base_denominator ?? 1),
    }));
  });
};

const getItemEffectiveUnitPricePreview = (item: PurchaseOrderItemFormItem) => {
  const qty = Number(item.qty || 0);
  if (qty <= 0) return 0;

  return Number((getItemSubtotalAfterDiscountPreview(item) / qty).toFixed(2));
};

const getItemBaseUnitPricePreview = (item: PurchaseOrderItemFormItem) => {
  const baseQty = Number(item.qty || 0) * Number(item.product_unit_conversion_value || 1);
  if (baseQty <= 0) return 0;

  return Number((getItemSubtotalAfterDiscountPreview(item) / baseQty).toFixed(2));
};

const buildItemInitialOption = (item: PurchaseOrderItemFormItem): ProductUnitOption | null => {
  if (!item.product_unit_id) return null;

  // carry the row's current price and VAT snapshot so re-picking this very
  // option from the dropdown never wipes what the user already entered
  return {
    product_unit_id: item.product_unit_id,
    product_unit_code: item.product_unit_product_code ?? '',
    product_name: item.product_unit_product_name ?? '-',
    product_image_url: item.product_unit_product_image_url ?? null,
    unit_name: item.product_unit_unit_name ?? '',
    base_unit_name: item.product_unit_base_unit_name ?? '',
    conversion_value: Number(item.product_unit_conversion_value ?? 1),
    is_use_serial_number: false,
    price: Number(item.product_unit_price ?? 0),
    product_unit_is_price_include_vat: Boolean(item.product_unit_is_price_include_vat),
    vat_profile_id: item.vat_profile_id ?? null,
    vat_profile_name: item.vat_profile_name ?? null,
    vat_rate: Number(item.vat_rate ?? 0),
    vat_base_numerator: Number(item.vat_base_numerator ?? 1),
    vat_base_denominator: Number(item.vat_base_denominator ?? 1),
  };
};

const addItem = () => {
  purchaseOrderForm.items.push({
    id: null,
    qty: 1,
    product_unit_id: '',
    product_unit_product_code: null,
    product_unit_product_name: null,
    product_unit_product_image_url: null,
    product_unit_unit_name: null,
    product_unit_base_unit_name: null,
    product_unit_conversion_value: 1,
    product_unit_price: 0,
    price_discount: 0,
    subtotal_discount: 0,
    product_unit_is_price_include_vat: false,
    vat_profile_id: null,
    vat_profile_name: null,
    vat_rate: 0,
    vat_base_numerator: 1,
    vat_base_denominator: 1,
    remarks: '',
  } as any);
  purchaseOrderItemDetailsExpanded.value.push(false);
  purchaseOrderItemBreakdownExpanded.value.push(false);
  purchaseOrderItemAdditionalExpanded.value.push(false);
};

const handleProductUnitSelected = (index: number, option: ProductUnitOption | null) => {
  const item = purchaseOrderItemsForm.value[index];
  if (!item) return;

  if (!option) {
    item.product_unit_id = '';
    item.product_unit_product_code = null;
    item.product_unit_product_name = null;
    item.product_unit_product_image_url = null;
    item.product_unit_unit_name = null;
    item.product_unit_base_unit_name = null;
    item.product_unit_conversion_value = 1;
    item.product_unit_price = 0;
    purchaseOrderForm.forgetError(`items.${index}.product_unit_id` as any);
    return;
  }

  if (option.vat_profile_id) {
    appendVatProfileOption({
      id: option.vat_profile_id,
      name: option.vat_profile_name,
      vat_rate: option.vat_rate,
      vat_base_numerator: option.vat_base_numerator,
      vat_base_denominator: option.vat_base_denominator,
    });
  }

  item.product_unit_id = option.product_unit_id;
  item.product_unit_product_code = option.product_unit_code;
  item.product_unit_product_name = option.product_name;
  item.product_unit_product_image_url = option.product_image_url ?? null;
  item.product_unit_unit_name = option.unit_name;
  item.product_unit_base_unit_name = option.conversion_value != 1 ? option.base_unit_name : '';
  item.product_unit_conversion_value = option.conversion_value;
  item.product_unit_price = option.price ?? item.product_unit_price ?? 0;
  item.product_unit_is_price_include_vat = option.product_unit_is_price_include_vat;
  item.vat_profile_id = option.vat_profile_id ?? null;
  item.vat_profile_name = option.vat_profile_id ? option.vat_profile_name : null;
  item.vat_rate = option.vat_profile_id ? option.vat_rate : 0;
  item.vat_base_numerator = option.vat_profile_id ? option.vat_base_numerator : 1;
  item.vat_base_denominator = option.vat_profile_id ? option.vat_base_denominator : 1;

  validatePurchaseOrderField(`items.${index}.product_unit_id`);
};

const removeProductUnit = (index: number) => {
  const item = purchaseOrderItemsForm.value[index];
  if (item?.id) {
    purchaseOrderForm.delete_item_ids.push(item.id);
  }
  purchaseOrderItemsForm.value.splice(index, 1);
  purchaseOrderItemDetailsExpanded.value.splice(index, 1);
  purchaseOrderItemBreakdownExpanded.value.splice(index, 1);
  purchaseOrderItemAdditionalExpanded.value.splice(index, 1);
  Object.keys(purchaseOrderForm.errors).forEach((key) => {
    if (key.startsWith('items.')) {
      purchaseOrderForm.forgetError(key as any);
    }
  });
};

const togglePurchaseOrderItemDetails = (index: number) => {
  purchaseOrderItemDetailsExpanded.value[index] = !purchaseOrderItemDetailsExpanded.value[index];
};

const addPayment = () => {
  purchaseOrderPaymentsForm.value.push({
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
  const payment = purchaseOrderPaymentsForm.value[index];
  if (payment?.id) {
    purchaseOrderForm.delete_payment_ids.push(payment.id);
  }
  purchaseOrderPaymentsForm.value.splice(index, 1);
  Object.keys(purchaseOrderForm.errors).forEach((key) => {
    if (key.startsWith('payments.')) {
      purchaseOrderForm.forgetError(key as any);
    }
  });
};

const addRefundedPayment = () => {
  purchaseOrderRefundedPaymentsForm.value.push({
    id: null,
    code: '_AUTO_',
    date: '_AUTO_',
    cash_account_id: '',
    amount: 0,
    remarks: '',
  });
};

const removeRefundedPayment = (index: number) => {
  const refundedPayment = purchaseOrderRefundedPaymentsForm.value[index];
  if (refundedPayment?.id) {
    purchaseOrderForm.delete_refunded_payment_ids.push(refundedPayment.id);
  }
  purchaseOrderRefundedPaymentsForm.value.splice(index, 1);
  Object.keys(purchaseOrderForm.errors).forEach((key) => {
    if (key.startsWith('refunded_payments.')) {
      purchaseOrderForm.forgetError(key as any);
    }
  });
};

const getItemPriceDiscountPreview = (item: PurchaseOrderItemFormItem) =>
  Math.min(Math.max(Number(item.price_discount || 0), 0), Number(item.product_unit_price || 0));

const getItemUnitPriceAfterDiscountPreview = (item: PurchaseOrderItemFormItem) =>
  Number(item.product_unit_price || 0) - getItemPriceDiscountPreview(item);

const getItemUnitPriceSubtotalAfterDiscountPreview = (item: PurchaseOrderItemFormItem) =>
  Number(item.qty || 0) * getItemUnitPriceAfterDiscountPreview(item);

const getItemSubtotalDiscountPreview = (item: PurchaseOrderItemFormItem) =>
  Math.min(
    Math.max(Number(item.subtotal_discount || 0), 0),
    getItemUnitPriceSubtotalAfterDiscountPreview(item),
  );

const getItemSubtotalAfterDiscountPreview = (item: PurchaseOrderItemFormItem) =>
  getItemUnitPriceSubtotalAfterDiscountPreview(item) - getItemSubtotalDiscountPreview(item);

const getItemsSubtotalAfterDiscountPreview = () =>
  purchaseOrderItemsForm.value.reduce((total, item) => total + getItemSubtotalAfterDiscountPreview(item), 0);

const getPurchaseOrderGlobalDiscountPreview = () =>
  Math.min(
    Math.max(Number(purchaseOrderForm.global_discount || 0), 0),
    getItemsSubtotalAfterDiscountPreview(),
  );

const getItemGlobalDiscountPreview = (item: PurchaseOrderItemFormItem, itemIndex: number) => {
  const totalBeforeGlobalDiscount = getItemsSubtotalAfterDiscountPreview();
  const totalGlobalDiscount = getPurchaseOrderGlobalDiscountPreview();

  if (totalBeforeGlobalDiscount <= 0 || totalGlobalDiscount <= 0) {
    return 0;
  }

  const allocations = purchaseOrderItemsForm.value.map((currentItem, index) => {
    if (index === purchaseOrderItemsForm.value.length - 1) {
      return 0;
    }

    return totalGlobalDiscount * (getItemSubtotalAfterDiscountPreview(currentItem) / totalBeforeGlobalDiscount);
  });

  const allocatedBeforeCurrent = allocations
    .slice(0, itemIndex)
    .reduce((total, allocation) => total + allocation, 0);

  if (itemIndex === purchaseOrderItemsForm.value.length - 1) {
    return Math.max(totalGlobalDiscount - allocatedBeforeCurrent, 0);
  }

  return Math.min(Math.max(allocations[itemIndex] || 0, 0), getItemSubtotalAfterDiscountPreview(item));
};

const getItemSubtotalAfterGlobalDiscountPreview = (item: PurchaseOrderItemFormItem, itemIndex: number) =>
  Math.max(
    getItemSubtotalAfterDiscountPreview(item) - getItemGlobalDiscountPreview(item, itemIndex),
    0,
  );

const getItemVatBasePreview = (item: PurchaseOrderItemFormItem, itemIndex: number) => {
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

const getItemVatPreview = (item: PurchaseOrderItemFormItem, itemIndex: number) => {
  const vatBase = getItemVatBasePreview(item, itemIndex);
  const vatRate = Number(item.vat_rate || 0);

  if (vatBase <= 0 || vatRate <= 0) {
    return 0;
  }

  return vatBase * (vatRate / 100);
};

const getItemTotalBeforeRoundingPreview = (item: PurchaseOrderItemFormItem, itemIndex: number) => {
  const subtotalAfterGlobalDiscount = getItemSubtotalAfterGlobalDiscountPreview(item, itemIndex);

  if (item.product_unit_is_price_include_vat) {
    return subtotalAfterGlobalDiscount;
  }

  return subtotalAfterGlobalDiscount + getItemVatPreview(item, itemIndex);
};

const getPurchaseOrderItemTotalAfterGlobalDiscountPreview = () =>
  purchaseOrderItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemSubtotalAfterGlobalDiscountPreview(item, itemIndex),
    0,
  );

const getPurchaseOrderVatBasePreview = () =>
  purchaseOrderItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemVatBasePreview(item, itemIndex),
    0,
  );

const getPurchaseOrderVatPreview = () =>
  purchaseOrderItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemVatPreview(item, itemIndex),
    0,
  );

const formatCurrencyPreviewValue = (value: number) => Number(value.toFixed(2));

const getTotalAmountPayableBeforeRoundingPreview = () =>
  purchaseOrderItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemTotalBeforeRoundingPreview(item, itemIndex),
    0,
  );

const getPurchaseOrderAmountPayablePreview = () =>
  getTotalAmountPayableBeforeRoundingPreview() + Number(purchaseOrderForm.rounding || 0);

const getPaymentsTotalPreview = () =>
  purchaseOrderPaymentsForm.value.reduce(
    (total, payment) => total + Math.max(Number(payment.amount || 0), 0),
    0,
  );

const getRefundedPaymentsTotalPreview = () =>
  purchaseOrderRefundedPaymentsForm.value.reduce(
    (total, refundedPayment) => total + Math.max(Number(refundedPayment.amount || 0), 0),
    0,
  );

const getAllocatedPaymentsTotalPreview = () =>
  purchaseOrderPaymentsForm.value.reduce(
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
  if (purchaseOrderForm.hasErrors) {
    const firstErrorKey = Object.keys(purchaseOrderForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  const backupItems = [...purchaseOrderItemsForm.value];
  const backupPayments = [...purchaseOrderPaymentsForm.value];
  const backupRefundedPayments = [...purchaseOrderRefundedPaymentsForm.value];

  const cleanedItems: PurchaseOrderItemNestedUpdateRequest[] = purchaseOrderItemsForm.value.map((item) => ({
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

  purchaseOrderForm.items = cleanedItems as any;
  purchaseOrderForm.payments = purchaseOrderPaymentsForm.value.map((payment) => ({
    id: payment.id,
    code: payment.code,
    date: payment.date,
    cash_account_id: payment.cash_account_id,
    amount: payment.amount,
    remarks: payment.remarks,
  })) as any;
  purchaseOrderForm.refunded_payments = purchaseOrderRefundedPaymentsForm.value.map((refundedPayment) => ({
    id: refundedPayment.id,
    code: refundedPayment.code,
    date: refundedPayment.date,
    cash_account_id: refundedPayment.cash_account_id,
    amount: refundedPayment.amount,
    remarks: refundedPayment.remarks,
  })) as any;

  emits('loading-state', true);

  try {
    await purchaseOrderForm.submit();
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(t('views.purchase_order.alert.update.title'), t('views.purchase_order.alert.update.message'));
    router.push({ name: 'side-menu-purchase-order-list' });
  } catch (error) {
    purchaseOrderForm.items = backupItems as any;
    purchaseOrderForm.payments = backupPayments as any;
    purchaseOrderForm.refunded_payments = backupRefundedPayments as any;
    showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
  } finally {
    emits('loading-state', false);
  }
};
</script>

<template>
  <form id="purchaseOrderForm" @submit.prevent="onSubmit">
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
              <FormInput type="hidden" v-model="purchaseOrderForm.company_id" />
            </div>
            <!-- branch info -->
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="purchaseOrderForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <!-- card: purchase order header information -->
      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <!-- purchase order code -->
            <div class="col-span-12 md:col-span-6 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': purchaseOrderForm.invalid('code') }">
                {{ t('views.purchase_order.fields.code') }}
              </FormLabel>
              <FormInputCode v-model="purchaseOrderForm.code"
                :class="{ 'border-danger': purchaseOrderForm.invalid('code') }"
                :placeholder="t('views.purchase_order.fields.code')" @set-auto="setCode"
                @change="purchaseOrderForm.validate('code')" />
              <FormErrorMessages :messages="purchaseOrderForm.errors.code" />
            </div>
            <!-- purchase order date -->
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': purchaseOrderForm.invalid('date') }">
                {{ t('views.purchase_order.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto v-model="purchaseOrderForm.date"
                :class="{ 'border-danger': purchaseOrderForm.invalid('date') }"
                :placeholder="t('views.purchase_order.fields.date')" @change="purchaseOrderForm.validate('date')" />
              <FormErrorMessages :messages="purchaseOrderForm.errors.date" />
            </div>
            <!-- purchase order due days -->
            <div class="col-span-12 md:col-span-6 lg:col-span-1">
              <FormLabel :class="{ 'text-danger': purchaseOrderForm.invalid('due_days') }">
                {{ t('views.purchase_order.fields.due_days') }}
              </FormLabel>
              <FormInput v-model="purchaseOrderForm.due_days" type="number" min="0"
                :class="{ 'border-danger': purchaseOrderForm.invalid('due_days') }"
                @change="purchaseOrderForm.validate('due_days')" />
              <FormErrorMessages :messages="purchaseOrderForm.errors.due_days" />
            </div>
            <!-- purchase order supplier -->
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': purchaseOrderForm.invalid('supplier_id') }">
                {{ t('views.purchase_order.fields.supplier_id') }}
              </FormLabel>
              <FormSelectSearch v-model="purchaseOrderForm.supplier_id" v-model:search="supplierSearch"
                :options="supplierOptions" :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': purchaseOrderForm.invalid('supplier_id') }"
                @change="purchaseOrderForm.validate('supplier_id')" @search="loadSupplierDDL" @clear="clearSupplier" />
              <FormErrorMessages :messages="purchaseOrderForm.errors.supplier_id" />
            </div>
            <!-- purchase order remarks -->
            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': purchaseOrderForm.invalid('remarks') }">
                {{ t('views.purchase_order.fields.remarks') }}
              </FormLabel>
              <FormTextarea v-model="purchaseOrderForm.remarks"
                :class="{ 'border-danger': purchaseOrderForm.invalid('remarks') }"
                @change="purchaseOrderForm.validate('remarks')" />
              <FormErrorMessages :messages="purchaseOrderForm.errors.remarks" />
            </div>
          </div>
        </div>
      </template>

      <!-- card: purchase order item list and per-item breakdown -->
      <template #card-items-2>
        <div class="p-5 space-y-4">
          <FormErrorMessages :messages="purchaseOrderForm.errors.items" />

          <!-- items: empty state -->
          <div v-if="purchaseOrderItemsForm.length === 0" class="text-slate-500 text-sm">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <!-- items: repeating item blocks -->
          <div v-else>
            <div v-if="purchaseOrderItemsForm.length > 0"
              class="hidden lg:grid grid-cols-[minmax(0,1fr)_5.5rem_4.5rem_8rem_8rem_5.5rem] items-center gap-2 border-b border-slate-200/60 pb-2 text-xs font-medium text-slate-500 dark:border-darkmode-400 dark:text-slate-400">
              <div class="truncate">{{ t('views.purchase_order.fields.product_unit_id') }}</div>
              <div class="truncate text-right">{{ t('views.purchase_order.fields.qty') }}</div>
              <div class="truncate">{{ t('views.product.table.cols.unit') }}</div>
              <div class="truncate text-right">{{ t('views.purchase_order.fields.product_unit_price') }}</div>
              <div class="truncate text-right" :title="t('views.purchase_order.fields.subtotal_after_discount')">
                {{ t('views.purchase_order.fields.subtotal_column') }}
              </div>
              <div></div>
            </div>
            <div v-for="(item, index) in purchaseOrderItemsForm" :key="`${item.product_unit_id}-${index}`"
              class="mt-2 first:mt-0">
              <!-- item summary: single compact row -->
              <div
                class="grid grid-cols-2 gap-x-3 gap-y-2 lg:grid-cols-[minmax(0,1fr)_5.5rem_4.5rem_8rem_8rem_5.5rem] lg:items-center lg:gap-2">
                <div class="col-span-2 flex min-w-0 items-center gap-3 lg:col-span-1">
                <ProductImagePreview :image-url="item.product_unit_product_image_url"
                  wrapper-class="w-8 h-8 rounded-md overflow-hidden bg-slate-100 dark:bg-darkmode-600 flex items-center justify-center cursor-zoom-in shrink-0"
                  icon-class="w-4 h-4 text-slate-400"
                  :preview-title="item.product_unit_product_name || t('views.purchase_order.fields.product_unit_id')" />
                <div class="min-w-0 flex-1">
                  <ProductUnitSelectSearch :model-value="item.product_unit_id"
                    :fetch-options="fetchProductUnitOptions" :initial-option="buildItemInitialOption(item)"
                    :invalid="invalidPurchaseOrderField(`items.${index}.product_unit_id`)"
                    :placeholder="t('components.dropdown.placeholder')"
                    @select="handleProductUnitSelected(index, $event as ProductUnitOption | null)" />
                </div>
                <div class="flex shrink-0 items-center gap-1 lg:hidden">
                  <Button type="button" variant="outline-secondary"
                    class="flex h-[38px] w-[38px] min-w-0 items-center justify-center p-0"
                    @click="togglePurchaseOrderItemDetails(index)">
                    <Lucide :icon="purchaseOrderItemDetailsExpanded[index] ? 'ChevronUp' : 'ChevronDown'"
                      class="w-4 h-4" />
                  </Button>
                  <Button type="button" variant="outline-secondary"
                    class="flex h-[38px] w-[38px] min-w-0 items-center justify-center p-0"
                    @click="removeProductUnit(index)">
                    <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                  </Button>
                </div>
                </div>
                <div :title="t('views.purchase_order.fields.qty')">
                  <div class="mb-1 text-xs text-slate-500 lg:hidden">{{ t('views.purchase_order.fields.qty') }}</div>
                  <InputGroup>
                    <FormInputCurrency :id="`purchase-order-item-qty-${index}`" v-model="item.qty"
                      :allow-negative="false" class="min-w-0 flex-1"
                      :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.qty`) }"
                      @change="validatePurchaseOrderField(`items.${index}.qty`)" />
                    <InputGroup.Text v-if="item.product_unit_unit_name" class="flex shrink-0 items-center bg-transparent px-2 text-xs dark:bg-transparent lg:hidden">
                      {{ item.product_unit_unit_name }}
                    </InputGroup.Text>
                  </InputGroup>
                </div>
                <div
                  class="hidden min-w-0 text-xs text-slate-500 dark:text-slate-400 lg:block"
                  :title="
                    Number(item.product_unit_conversion_value || 1) > 1
                      ? `1 ${item.product_unit_unit_name} = ${item.product_unit_conversion_value} ${item.product_unit_base_unit_name || ''}`
                      : item.product_unit_unit_name || '-'
                  "
                >
                  <div class="truncate">{{ item.product_unit_unit_name || '-' }}</div>
                </div>
                <div :title="t('views.purchase_order.fields.product_unit_price')">
                  <div class="mb-1 text-xs text-slate-500 lg:hidden">{{ t('views.purchase_order.fields.product_unit_price') }}</div>
                  <FormInputCurrency v-model="item.product_unit_price" :allow-negative="false"
                    :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.product_unit_price`) }"
                    @change="validatePurchaseOrderField(`items.${index}.product_unit_price`)" />
                </div>
                <div class="col-span-2 lg:col-span-1" :title="t('views.purchase_order.fields.subtotal_after_discount')">
                  <div class="flex items-baseline justify-between gap-2 lg:hidden">
                    <span class="text-xs text-slate-500">{{ t('views.purchase_order.fields.subtotal_column') }}</span>
                    <span class="text-sm font-semibold">
                      {{ formatCurrency(getItemSubtotalAfterDiscountPreview(item)) }}
                    </span>
                  </div>
                  <FormInputCurrency :model-value="getItemSubtotalAfterDiscountPreview(item)" readonly
                    class="hidden bg-slate-50 dark:bg-darkmode-800 lg:block" />
                </div>
                <div class="col-span-2 hidden items-center justify-end gap-1 lg:col-span-1 lg:flex">
                  <Button type="button" variant="outline-secondary" class="flex h-[38px] w-[38px] min-w-0 items-center justify-center p-0"
                    @click="togglePurchaseOrderItemDetails(index)">
                    <Lucide :icon="purchaseOrderItemDetailsExpanded[index] ? 'ChevronUp' : 'ChevronDown'"
                      class="w-4 h-4" />
                  </Button>
                  <Button type="button" variant="outline-secondary" class="flex h-[38px] w-[38px] min-w-0 items-center justify-center p-0" @click="removeProductUnit(index)">
                    <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                  </Button>
                </div>
              </div>
              <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.product_unit_id`)" />
              <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.qty`)" />
              <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.product_unit_price`)" />

              <!-- item details: collapsible sections -->
              <div v-if="purchaseOrderItemDetailsExpanded[index]" class="ml-11 mt-3 space-y-3">
                <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400">
                  <button type="button"
                    class="flex w-full items-center justify-between px-4 py-3 text-left text-sm font-medium"
                    @click="purchaseOrderItemBreakdownExpanded[index] = !purchaseOrderItemBreakdownExpanded[index]">
                    <span>{{ t('views.purchase_order.fields.item_price_breakdown') }}</span>
                    <Lucide :icon="purchaseOrderItemBreakdownExpanded[index] ? 'ChevronUp' : 'ChevronDown'"
                      class="w-4 h-4" />
                  </button>
                  <div v-if="purchaseOrderItemBreakdownExpanded[index]"
                    class="border-t border-slate-200/60 p-4 dark:border-darkmode-400">
                  <div class="space-y-3">
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase_order.fields.product_unit_conversion_value') }}
                      </div>
                      <div class="col-span-12 md:col-span-8 max-w-xs">
                        <div class="flex items-center gap-2">
                          <FormInputCurrency v-model="item.product_unit_conversion_value" :allow-negative="false"
                            class="w-40"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.product_unit_conversion_value`) }"
                            @change="validatePurchaseOrderField(`items.${index}.product_unit_conversion_value`)" />
                          <span v-if="item.product_unit_base_unit_name"
                            class="text-sm text-slate-500 dark:text-slate-400">
                            {{ item.product_unit_base_unit_name }}
                          </span>
                        </div>
                        <FormErrorMessages
                          :messages="getPurchaseOrderFieldErrors(`items.${index}.product_unit_conversion_value`)" />
                      </div>
                    </div>
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase_order.fields.price_discount') }}
                      </div>
                      <div class="col-span-12 md:col-span-8 max-w-xs">
                        <FormInputCurrency v-model="item.price_discount" :allow-negative="false"
                          :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.price_discount`) }"
                          @change="validatePurchaseOrderField(`items.${index}.price_discount`)" />
                        <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.price_discount`)" />
                      </div>
                    </div>

                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase_order.fields.price_after_discount') }}
                      </div>
                      <div class="col-span-12 md:col-span-8 max-w-xs">
                        <FormInputCurrency :model-value="getItemUnitPriceAfterDiscountPreview(item)" readonly />
                      </div>
                    </div>

                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase_order.fields.subtotal') }}
                      </div>
                      <div class="col-span-12 md:col-span-8 max-w-xs">
                        <FormInputCurrency :model-value="getItemUnitPriceSubtotalAfterDiscountPreview(item)" readonly />
                      </div>
                    </div>

                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase_order.fields.subtotal_discount') }}
                      </div>
                      <div class="col-span-12 md:col-span-8 max-w-xs">
                        <FormInputCurrency v-model="item.subtotal_discount" :allow-negative="false"
                          :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.subtotal_discount`) }"
                          @change="validatePurchaseOrderField(`items.${index}.subtotal_discount`)" />
                        <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.subtotal_discount`)" />
                      </div>
                    </div>

                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase_order.fields.subtotal_after_discount') }}
                      </div>
                      <div class="col-span-12 md:col-span-8 max-w-xs">
                        <FormInputCurrency :model-value="getItemSubtotalAfterDiscountPreview(item)" readonly
                          class="bg-slate-50 dark:bg-darkmode-800" />
                      </div>
                    </div>
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{
                          t('views.purchase_order.fields.base_unit_price', {
                            unit: item.product_unit_unit_name || '-',
                          })
                        }}
                      </div>
                      <div class="col-span-12 md:col-span-8 max-w-xs">
                        <FormInputCurrency :model-value="getItemEffectiveUnitPricePreview(item)" readonly
                          class="w-40 bg-slate-50 dark:bg-darkmode-800" />
                      </div>
                    </div>
                    <div v-if="Number(item.product_unit_conversion_value || 1) > 1"
                      class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{
                          t('views.purchase_order.fields.base_unit_price', {
                            unit: item.product_unit_base_unit_name || '-',
                          })
                        }}
                      </div>
                      <div class="col-span-12 md:col-span-8 max-w-xs">
                        <FormInputCurrency :model-value="getItemBaseUnitPricePreview(item)" readonly
                          class="w-40 bg-slate-50 dark:bg-darkmode-800" />
                      </div>
                    </div>
                  </div>
                  </div>
                </div>

                <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400">
                  <button type="button"
                    class="flex w-full items-center justify-between px-4 py-3 text-left text-sm font-medium"
                    @click="purchaseOrderItemAdditionalExpanded[index] = !purchaseOrderItemAdditionalExpanded[index]">
                    <span>{{ t('views.purchase_order.fields.item_additional_details') }}</span>
                    <Lucide :icon="purchaseOrderItemAdditionalExpanded[index] ? 'ChevronUp' : 'ChevronDown'"
                      class="w-4 h-4" />
                  </button>
                  <div v-if="purchaseOrderItemAdditionalExpanded[index]"
                    class="border-t border-slate-200/60 p-4 dark:border-darkmode-400">
                  <div class="space-y-3">
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase_order.fields.vat_profile_id') }}
                      </div>
                      <div class="col-span-12 md:col-span-8 max-w-sm">
                        <FormSelectSearch v-model="item.vat_profile_id" v-model:search="vatProfileSearch"
                          :options="vatProfileOptions" :placeholder="t('components.dropdown.placeholder')"
                          :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.vat_profile_id`) }"
                          @change="syncVatProfile(index)" @search="loadVatProfileDDL" @clear="clearVatProfile(index)" />
                        <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.vat_profile_id`)" />
                      </div>
                    </div>
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase_order.fields.vat_rate') }}
                      </div>
                      <div class="col-span-12 md:col-span-8 max-w-xs">
                        <FormInputCurrency v-model="item.vat_rate" :allow-negative="false"
                          :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.vat_rate`) }"
                          @change="validatePurchaseOrderField(`items.${index}.vat_rate`)" />
                        <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.vat_rate`)" />
                      </div>
                    </div>
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase_order.fields.vat_base') }}
                      </div>
                      <div class="col-span-12 md:col-span-8">
                        <div class="flex items-center gap-2">
                          <FormInput v-model="item.vat_base_numerator" type="number" min="1" class="w-24"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.vat_base_numerator`) }"
                            @change="validatePurchaseOrderField(`items.${index}.vat_base_numerator`)" />
                          <span class="text-slate-500">/</span>
                          <FormInput v-model="item.vat_base_denominator" type="number" min="1" class="w-24"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.vat_base_denominator`) }"
                            @change="validatePurchaseOrderField(`items.${index}.vat_base_denominator`)" />
                        </div>
                        <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.vat_base_numerator`)" />
                        <FormErrorMessages
                          :messages="getPurchaseOrderFieldErrors(`items.${index}.vat_base_denominator`)" />
                      </div>
                    </div>
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase_order.fields.product_unit_is_price_include_vat') }}
                      </div>
                      <div class="col-span-12 md:col-span-8">
                        <FormSwitch>
                          <FormSwitch.Input v-model="item.product_unit_is_price_include_vat" type="checkbox" />
                        </FormSwitch>
                      </div>
                    </div>
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase_order.fields.remarks') }}
                      </div>
                      <div class="col-span-12 md:col-span-8 max-w-md">
                        <FormTextarea v-model="item.remarks" rows="2"
                          :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.remarks`) }"
                          @change="validatePurchaseOrderField(`items.${index}.remarks`)" />
                        <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.remarks`)" />
                      </div>
                    </div>
                  </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
          <div class="flex justify-end">
            <Button type="button" variant="outline-primary" @click="addItem">
              <Lucide icon="Plus" class="w-4 h-4 mr-1" />
              {{ t('components.buttons.create_new') }}
            </Button>
          </div>
        </div>
      </template>

      <!-- card: financial summary -->
      <template #card-items-3>
        <div class="p-5 space-y-4">
          <!-- summary card: item totals, global discount, and payments -->
          <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-4">
            <!-- summary: total of all items after item-level discounts -->
            <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
              <!-- summary spacer: keeps totals aligned to the right on desktop -->
              <div class="col-span-12 lg:col-span-9"></div>
              <div class="col-span-12 lg:col-span-3">
                <FormLabel>{{ t('views.purchase_order.fields.items_subtotal_after_discount') }}</FormLabel>
                <FormInputCurrency :model-value="getItemsSubtotalAfterDiscountPreview()" readonly />
              </div>
            </div>

            <!-- summary: adjustments, totals, and payment flow -->
            <div class="space-y-4">
              <!-- global discount: single nominal input -->
              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <!-- summary spacer: keeps the numeric field aligned with other totals -->
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': purchaseOrderForm.invalid('global_discount') }">
                    {{ t('views.purchase_order.fields.global_discount') }}
                  </FormLabel>
                  <FormInputCurrency v-model="purchaseOrderForm.global_discount" :allow-negative="false"
                    :class="{ 'border-danger': purchaseOrderForm.invalid('global_discount') }"
                    @change="purchaseOrderForm.validate('global_discount')" />
                  <FormErrorMessages :messages="purchaseOrderForm.errors.global_discount" />
                </div>
              </div>

              <template v-if="isTotalsBreakdownExpanded">
                <!-- summary: total after global discount -->
                <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                  <div class="col-span-12 lg:col-span-9"></div>
                  <div class="col-span-12 lg:col-span-3">
                    <FormLabel>{{ t('views.purchase_order.fields.item_total_after_global_discount') }}</FormLabel>
                    <FormInputCurrency :model-value="getPurchaseOrderItemTotalAfterGlobalDiscountPreview()" readonly />
                  </div>
                </div>

                <!-- summary: vat base -->
                <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                  <div class="col-span-12 lg:col-span-9"></div>
                  <div class="col-span-12 lg:col-span-3">
                    <FormLabel>{{ t('views.purchase_order.fields.vat_base') }}</FormLabel>
                    <FormInputCurrency :model-value="formatCurrencyPreviewValue(getPurchaseOrderVatBasePreview())"
                      readonly />
                  </div>
                </div>

                <!-- summary: vat -->
                <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                  <div class="col-span-12 lg:col-span-9"></div>
                  <div class="col-span-12 lg:col-span-3">
                    <FormLabel>{{ t('views.purchase_order.fields.vat') }}</FormLabel>
                    <FormInputCurrency :model-value="formatCurrencyPreviewValue(getPurchaseOrderVatPreview())"
                      readonly />
                  </div>
                </div>

                <!-- summary: rounding adjustment before amount payable -->
                <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                  <div class="col-span-12 lg:col-span-9"></div>
                  <div class="col-span-12 lg:col-span-3">
                    <FormLabel :class="{ 'text-danger': purchaseOrderForm.invalid('rounding') }">
                      {{ t('views.purchase_order.fields.rounding') }}
                    </FormLabel>
                    <FormInputCurrency v-model="purchaseOrderForm.rounding"
                      :class="{ 'border-danger': purchaseOrderForm.invalid('rounding') }"
                      @change="purchaseOrderForm.validate('rounding')" />
                    <FormErrorMessages :messages="purchaseOrderForm.errors.rounding" />
                  </div>
                </div>
              </template>

              <!-- summary: amount payable after applying global discount and rounding -->
              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <!-- summary spacer: keeps the numeric field aligned with other totals -->
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.purchase_order.fields.amount_payable') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="shrink-0">
                      <Button type="button" variant="outline-secondary"
                        class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                        @click="isTotalsBreakdownExpanded = !isTotalsBreakdownExpanded">
                        {{ isTotalsBreakdownExpanded ? '▲' : '▼' }}
                      </Button>
                    </div>
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency :model-value="getPurchaseOrderAmountPayablePreview()" readonly />
                    </div>
                  </div>
                </div>
              </div>

              <!-- summary: payment editor and aggregates -->
              <div class="space-y-4">
                <!-- payments: editor -->
                <div v-if="isPaymentEditorExpanded" class="space-y-4">
                  <FormErrorMessages :messages="purchaseOrderForm.errors.payments" />

                  <!-- payments: empty state -->
                  <div v-if="purchaseOrderPaymentsForm.length === 0" class="text-right text-slate-500 text-sm">
                    {{ t('components.data-list.data_not_found') }}
                  </div>

                  <!-- payments: list -->
                  <div v-else class="space-y-4">
                    <div v-for="(payment, index) in purchaseOrderPaymentsForm" :key="`payment-${index}`"
                      class="space-y-3">
                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <!-- payment spacer: keeps editor fields aligned to the right side -->
                        <div class="col-span-12 md:col-span-6 lg:col-span-2"></div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-2">
                          <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`payments.${index}.code`) }">
                            {{ t('views.purchase_order.fields.code') }}
                          </FormLabel>
                          <FormInputCode v-model="payment.code"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`payments.${index}.code`) }"
                            :placeholder="t('views.purchase_order.fields.code')" @set-auto="setPaymentCode(index)"
                            @change="validatePurchaseOrderField(`payments.${index}.code`)" />
                          <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`payments.${index}.code`)" />
                        </div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-4">
                          <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`payments.${index}.date`) }">
                            {{ t('views.purchase_order.fields.date') }}
                          </FormLabel>
                          <FormInputDateTimeAuto v-model="payment.date"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`payments.${index}.date`) }"
                            :placeholder="t('views.purchase_order.fields.date')"
                            @change="validatePurchaseOrderField(`payments.${index}.date`)" />
                          <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`payments.${index}.date`)" />
                        </div>
                        <div class="col-span-12 md:col-span-8 lg:col-span-2">
                          <FormLabel
                            :class="{ 'text-danger': invalidPurchaseOrderField(`payments.${index}.cash_account_id`) }">
                            {{ t('views.purchase_order.fields.cash_account_id') }}
                          </FormLabel>
                          <FormSelectSearch v-model="payment.cash_account_id" v-model:search="cashAccountSearch"
                            :options="cashAccountOptions" :placeholder="t('components.dropdown.placeholder')"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`payments.${index}.cash_account_id`) }"
                            @change="validatePurchaseOrderField(`payments.${index}.cash_account_id`)"
                            @search="loadCashAccountDDL" @clear="clearCashAccount(index)" />
                          <FormErrorMessages
                            :messages="getPurchaseOrderFieldErrors(`payments.${index}.cash_account_id`)" />
                        </div>
                        <div class="col-span-12 md:col-span-4 lg:col-span-2">
                          <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`payments.${index}.amount`) }">
                            {{ t('views.purchase_order.fields.amount') }}
                          </FormLabel>
                          <div class="flex items-start gap-2">
                            <div class="flex-1 min-w-0">
                              <FormInputCurrency v-model="payment.amount" :allow-negative="false"
                                :class="{ 'border-danger': invalidPurchaseOrderField(`payments.${index}.amount`) }"
                                @change="validatePurchaseOrderField(`payments.${index}.amount`)" />
                            </div>
                            <div class="shrink-0">
                              <Button type="button" variant="outline-secondary"
                                class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                                @click="removePayment(index)">
                                <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                              </Button>
                            </div>
                          </div>
                          <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`payments.${index}.amount`)" />
                        </div>
                      </div>
                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <!-- payment spacer: keeps remarks width consistent with fields above -->
                        <div class="col-span-12 lg:col-span-2"></div>
                        <div class="col-span-12 lg:col-span-7">
                          <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`payments.${index}.remarks`) }">
                            {{ t('views.purchase_order.fields.remarks') }}
                          </FormLabel>
                          <FormTextarea v-model="payment.remarks"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`payments.${index}.remarks`) }"
                            @change="validatePurchaseOrderField(`payments.${index}.remarks`)" />
                          <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`payments.${index}.remarks`)" />
                        </div>
                        <div class="col-span-12 lg:col-span-3">
                          <FormLabel>{{ t('views.purchase_order.fields.amount_allocated') }}</FormLabel>
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
                  <!-- summary spacer: keeps the numeric field aligned with other totals -->
                  <div class="col-span-12 lg:col-span-9"></div>
                  <div class="col-span-12 lg:col-span-3">
                    <FormLabel>{{ t('views.purchase_order.fields.amount_paid_down_payment') }}</FormLabel>
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

                <!-- refunded payments: editor -->
                <div v-if="isRefundedPaymentEditorExpanded" class="space-y-4">
                  <FormErrorMessages :messages="purchaseOrderForm.errors.refunded_payments" />

                  <div v-if="purchaseOrderRefundedPaymentsForm.length === 0" class="text-right text-slate-500 text-sm">
                    {{ t('components.data-list.data_not_found') }}
                  </div>

                  <div v-else class="space-y-4">
                    <div v-for="(refundedPayment, index) in purchaseOrderRefundedPaymentsForm"
                      :key="`refunded-payment-${index}`" class="space-y-3">
                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 md:col-span-6 lg:col-span-2"></div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-2">
                          <FormLabel
                            :class="{ 'text-danger': invalidPurchaseOrderField(`refunded_payments.${index}.code`) }">
                            {{ t('views.purchase_order.fields.code') }}
                          </FormLabel>
                          <FormInputCode v-model="refundedPayment.code"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`refunded_payments.${index}.code`) }"
                            :placeholder="t('views.purchase_order.fields.code')"
                            @set-auto="setRefundedPaymentCode(index)"
                            @change="validatePurchaseOrderField(`refunded_payments.${index}.code`)" />
                          <FormErrorMessages
                            :messages="getPurchaseOrderFieldErrors(`refunded_payments.${index}.code`)" />
                        </div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-4">
                          <FormLabel
                            :class="{ 'text-danger': invalidPurchaseOrderField(`refunded_payments.${index}.date`) }">
                            {{ t('views.purchase_order.fields.date') }}
                          </FormLabel>
                          <FormInputDateTimeAuto v-model="refundedPayment.date"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`refunded_payments.${index}.date`) }"
                            :placeholder="t('views.purchase_order.fields.date')"
                            @change="validatePurchaseOrderField(`refunded_payments.${index}.date`)" />
                          <FormErrorMessages
                            :messages="getPurchaseOrderFieldErrors(`refunded_payments.${index}.date`)" />
                        </div>
                        <div class="col-span-12 md:col-span-8 lg:col-span-2">
                          <FormLabel
                            :class="{ 'text-danger': invalidPurchaseOrderField(`refunded_payments.${index}.cash_account_id`) }">
                            {{ t('views.purchase_order.fields.cash_account_id') }}
                          </FormLabel>
                          <FormSelectSearch v-model="refundedPayment.cash_account_id"
                            v-model:search="cashAccountSearch" :options="cashAccountOptions"
                            :placeholder="t('components.dropdown.placeholder')"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`refunded_payments.${index}.cash_account_id`) }"
                            @change="validatePurchaseOrderField(`refunded_payments.${index}.cash_account_id`)"
                            @search="loadCashAccountDDL" @clear="clearRefundedCashAccount(index)" />
                          <FormErrorMessages
                            :messages="getPurchaseOrderFieldErrors(`refunded_payments.${index}.cash_account_id`)" />
                        </div>
                        <div class="col-span-12 md:col-span-4 lg:col-span-2">
                          <FormLabel
                            :class="{ 'text-danger': invalidPurchaseOrderField(`refunded_payments.${index}.amount`) }">
                            {{ t('views.purchase_order.fields.amount') }}
                          </FormLabel>
                          <div class="flex items-start gap-2">
                            <div class="flex-1 min-w-0">
                              <FormInputCurrency v-model="refundedPayment.amount" :allow-negative="false"
                                :class="{ 'border-danger': invalidPurchaseOrderField(`refunded_payments.${index}.amount`) }"
                                @change="validatePurchaseOrderField(`refunded_payments.${index}.amount`)" />
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
                            :messages="getPurchaseOrderFieldErrors(`refunded_payments.${index}.amount`)" />
                        </div>
                      </div>
                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 lg:col-span-2"></div>
                        <div class="col-span-12 lg:col-span-10">
                          <FormLabel
                            :class="{ 'text-danger': invalidPurchaseOrderField(`refunded_payments.${index}.remarks`) }">
                            {{ t('views.purchase_order.fields.remarks') }}
                          </FormLabel>
                          <FormTextarea v-model="refundedPayment.remarks"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`refunded_payments.${index}.remarks`) }"
                            @change="validatePurchaseOrderField(`refunded_payments.${index}.remarks`)" />
                          <FormErrorMessages
                            :messages="getPurchaseOrderFieldErrors(`refunded_payments.${index}.remarks`)" />
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
                    <FormLabel>{{ t('views.purchase_order.fields.amount_refunded_down_payment') }}</FormLabel>
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
                    <FormLabel>{{ t('views.purchase_order.fields.amount_allocated_down_payment') }}</FormLabel>
                    <FormInputCurrency :model-value="getAllocatedPaymentsTotalPreview()" readonly />
                  </div>
                </div>

                <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                  <div class="col-span-12 lg:col-span-9"></div>
                  <div class="col-span-12 lg:col-span-3">
                    <FormLabel>{{ t('views.purchase_order.fields.amount_available_down_payment') }}</FormLabel>
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
            :disabled="purchaseOrderForm.validating || purchaseOrderForm.hasErrors">
            <Lucide v-if="purchaseOrderForm.validating" icon="Loader" class="w-4 h-4 mr-2 animate-spin" />
            <template v-else>
              <Lucide icon="Save" class="w-4 h-4 mr-2" />
            </template>
            {{ t('components.buttons.save') }}
          </Button>
        </div>
      </template>
    </TwoColumnsLayout>
  </form>
</template>
