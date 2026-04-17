<script setup lang="ts">
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
  FormInputDateTime,
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
import CacheService from '@/services/CacheService';
import CashAccountService from '@/services/CashAccountService';
import ProductService from '@/services/ProductService';
import PurchaseOrderService from '@/services/PurchaseOrderService';
import SupplierService from '@/services/SupplierService';
import VatProfileService from '@/services/VatProfileService';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { DropDownOption } from '@/types/models/DropDownOption';
import { NotificationData } from '@/types/models/NotificationData';
import {
  PurchaseOrderDownPaymentNestedStoreRequest,
  PurchaseOrderDownPaymentRefundNestedStoreRequest,
  PurchaseOrderGlobalDiscountNestedStoreRequest,
  PurchaseOrderItemDiscountNestedStoreRequest,
  PurchaseOrderItemNestedStoreRequest,
} from '@/types/services/purchase-order/PurchaseOrderRequest';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { convertErrorTypeToAlertListType, formatCurrency } from '@/utils/helper';

type PurchaseOrderItemFormItem = PurchaseOrderItemNestedStoreRequest & {
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
const cacheService = new CacheService();

const purchaseOrderForm = purchaseOrderService.usePurchaseOrderCreateForm();

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

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

const showProductUnitModal = ref<boolean>(false);
const productSearchText = ref<string>('');
const isSearchingProductUnit = ref<boolean>(false);
const productUnitOptions = ref<Array<ProductUnitOption>>([]);
const editingProductUnitIndex = ref<number | null>(null);
const productUnitQtyToFocus = ref<number | null>(null);
const purchaseOrderItemDiscountsExpanded = ref<boolean[]>([]);
const viewportWidth = ref<number>(window.innerWidth);

const productUnitDialogColumns = [
  {
    key: 'unit_name',
    label: t('views.product.table.cols.unit'),
  },
  {
    key: 'conversion_value',
    label: t('views.purchase_order.fields.product_unit_conversion_value'),
    align: 'right' as const,
    formatter: 'number' as const,
  },
  {
    key: 'price',
    label: t('views.purchase_order.fields.product_unit_price'),
    align: 'right' as const,
    formatter: 'number' as const,
  },
];

const currentItemLayout = computed<'sm' | 'md' | 'lg'>(() => {
  if (viewportWidth.value >= 1024) return 'lg';
  if (viewportWidth.value >= 768) return 'md';
  return 'sm';
});

const discountTypeOptions = [
  { value: 'PERCENTAGE', label: 'Percentage' },
  { value: 'NOMINAL', label: 'Nominal' },
];

const purchaseOrderItemsForm = computed<PurchaseOrderItemFormItem[]>(
  () => purchaseOrderForm.items as PurchaseOrderItemFormItem[],
);
const purchaseOrderGlobalDiscountsForm = computed<PurchaseOrderGlobalDiscountNestedStoreRequest[]>(
  () => purchaseOrderForm.global_discounts as PurchaseOrderGlobalDiscountNestedStoreRequest[],
);

const isGlobalDiscountEditorExpanded = ref(false);
const isTotalsBreakdownExpanded = ref(false);
const purchaseOrderDownPaymentsForm = computed<PurchaseOrderDownPaymentNestedStoreRequest[]>(
  () => purchaseOrderForm.down_payments as PurchaseOrderDownPaymentNestedStoreRequest[],
);
const isDownPaymentEditorExpanded = ref(false);
const purchaseOrderRefundedDownPaymentsForm = computed<PurchaseOrderDownPaymentRefundNestedStoreRequest[]>(
  () => purchaseOrderForm.refunded_down_payments as PurchaseOrderDownPaymentRefundNestedStoreRequest[],
);
const isRefundedDownPaymentEditorExpanded = ref(false);

const invalidPurchaseOrderField = (field: string) => purchaseOrderForm.invalid(field as any);
const validatePurchaseOrderField = (field: string) => purchaseOrderForm.validate(field as any);
const getPurchaseOrderFieldErrors = (field: string) =>
  (purchaseOrderForm.errors as Record<string, string | undefined>)[field];

const handleExpandCard = (index: number) => {
  cards.value[index].state =
    cards.value[index].state === CardState.Collapsed ? CardState.Expanded : CardState.Collapsed;
};

watch(
  purchaseOrderForm,
  debounce((newValue) => {
    cacheService.setLastEntity('PURCHASE_ORDER_CREATE', newValue.data());
  }, 500),
  { deep: true },
);

const syncViewportWidth = () => {
  viewportWidth.value = window.innerWidth;
};

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

  purchaseOrderForm.setData({
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
  });

  await Promise.all([loadSupplierDDL(), loadVatProfileDDL(), loadCashAccountDDL()]);
});

onUnmounted(() => {
  window.removeEventListener('resize', syncViewportWidth);
});

const setCode = () => {
  purchaseOrderForm.forgetError('code');
  purchaseOrderForm.setData({
    code: purchaseOrderForm.code === '_AUTO_' ? '' : '_AUTO_',
  });
};

const setDownPaymentCode = (index: number) => {
  purchaseOrderForm.forgetError(`down_payments.${index}.code` as any);
  purchaseOrderDownPaymentsForm.value[index].code =
    purchaseOrderDownPaymentsForm.value[index].code === '_AUTO_' ? '' : '_AUTO_';
};

const setRefundedDownPaymentCode = (index: number) => {
  purchaseOrderForm.forgetError(`refunded_down_payments.${index}.code` as any);
  purchaseOrderRefundedDownPaymentsForm.value[index].code =
    purchaseOrderRefundedDownPaymentsForm.value[index].code === '_AUTO_' ? '' : '_AUTO_';
};

const loadSupplierDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await supplierService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    status: undefined,
    include_id: undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    supplierDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
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

  purchaseOrderItemsForm.value.forEach((item) => {
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
      name: item.name,
    }));
  }
};

const clearSupplier = () => {
  purchaseOrderForm.setData({ supplier_id: null });
  purchaseOrderForm.forgetError('supplier_id');
  purchaseOrderForm.validate('supplier_id');
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
  const downPayment = purchaseOrderDownPaymentsForm.value[index];
  if (!downPayment) return;
  downPayment.cash_account_id = '';
  purchaseOrderForm.validate(`down_payments.${index}.cash_account_id` as any);
};

const clearRefundedCashAccount = (index: number) => {
  const refundedDownPayment = purchaseOrderRefundedDownPaymentsForm.value[index];
  if (!refundedDownPayment) return;
  refundedDownPayment.cash_account_id = '';
  purchaseOrderForm.validate(`refunded_down_payments.${index}.cash_account_id` as any);
};

const loadFromCache = () => {
  const data = cacheService.getLastEntity('PURCHASE_ORDER_CREATE') as Record<string, unknown>;
  if (!data) return;
  purchaseOrderForm.setData(data);
  purchaseOrderItemDiscountsExpanded.value = purchaseOrderItemsForm.value.map(
    (item) => item.product_unit_price_discounts.length > 0,
  );
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

  const itemData: PurchaseOrderItemFormItem = {
    qty: 1,
    product_unit_id: option.product_unit_id,
    product_unit_product_code: option.product_unit_code,
    product_unit_product_name: option.product_name,
    product_unit_product_image_url: option.product_image_url,
    product_unit_unit_name: option.unit_name,
    product_unit_base_unit_name: option.base_unit_name,
    product_unit_conversion_value: option.conversion_value,
    product_unit_price: option.price,
    product_unit_price_discounts: [],
    subtotal_discounts: [],
    product_unit_is_price_include_vat: option.product_unit_is_price_include_vat,
    vat_profile_id: vatProfileId,
    vat_profile_name: vatProfileName,
    vat_rate: vatRate,
    vat_base_numerator: vatBaseNumerator,
    vat_base_denominator: vatBaseDenominator,
    remarks: '',
  };

  let targetIndex: number;

  if (editingProductUnitIndex.value === null) {
    purchaseOrderForm.items.push(itemData as any);
    targetIndex = purchaseOrderForm.items.length - 1;
    purchaseOrderItemDiscountsExpanded.value[targetIndex] = false;
  } else {
    const currentItem = purchaseOrderItemsForm.value[editingProductUnitIndex.value];
    purchaseOrderItemsForm.value[editingProductUnitIndex.value] = {
      ...currentItem,
      ...itemData,
      product_unit_price_discounts: currentItem.product_unit_price_discounts,
      subtotal_discounts: currentItem.subtotal_discounts,
    };
    targetIndex = editingProductUnitIndex.value;
    purchaseOrderItemDiscountsExpanded.value[targetIndex] =
      purchaseOrderItemDiscountsExpanded.value[targetIndex] ??
      (currentItem.product_unit_price_discounts.length > 0);
  }

  showProductUnitModal.value = false;
  editingProductUnitIndex.value = null;
  productUnitQtyToFocus.value = targetIndex;

  Object.keys(purchaseOrderForm.errors).forEach((key) => {
    if (key.startsWith('items.')) {
      purchaseOrderForm.forgetError(key as any);
    }
  });
};

const handleProductUnitModalAfterLeave = () => {
  const index = productUnitQtyToFocus.value;
  productUnitQtyToFocus.value = null;

  if (index === null) return;

  nextTick(() => {
    const el = document.getElementById(`purchase-order-item-qty-${index}`) as HTMLInputElement | null;
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el?.focus();
    el?.select();
  });
};

const resequence = (items: Array<{ sequence: number }>) => {
  items.forEach((item, index) => {
    item.sequence = index + 1;
  });
};

const addGlobalDiscount = () => {
  purchaseOrderGlobalDiscountsForm.value.push({
    sequence: purchaseOrderGlobalDiscountsForm.value.length + 1,
    discount_type: 'PERCENTAGE',
    discount_value: 0,
  });
};

const removeGlobalDiscount = (index: number) => {
  purchaseOrderGlobalDiscountsForm.value.splice(index, 1);
  resequence(purchaseOrderGlobalDiscountsForm.value);
  Object.keys(purchaseOrderForm.errors).forEach((key) => {
    if (key.startsWith('global_discounts.')) {
      purchaseOrderForm.forgetError(key as any);
    }
  });
};

const addItemPriceDiscount = (index: number) => {
  purchaseOrderItemDiscountsExpanded.value[index] = true;
  purchaseOrderItemsForm.value[index].product_unit_price_discounts.push({
    sequence: purchaseOrderItemsForm.value[index].product_unit_price_discounts.length + 1,
    discount_type: 'PERCENTAGE',
    discount_value: 0,
  });
};

const removeItemPriceDiscount = (index: number, discountIndex: number) => {
  purchaseOrderItemsForm.value[index].product_unit_price_discounts.splice(discountIndex, 1);
  resequence(purchaseOrderItemsForm.value[index].product_unit_price_discounts);
};

const addItemSubtotalDiscount = (index: number) => {
  purchaseOrderItemsForm.value[index].subtotal_discounts.push({
    sequence: purchaseOrderItemsForm.value[index].subtotal_discounts.length + 1,
    discount_type: 'PERCENTAGE',
    discount_value: 0,
  });
};

const removeItemSubtotalDiscount = (index: number, discountIndex: number) => {
  purchaseOrderItemsForm.value[index].subtotal_discounts.splice(discountIndex, 1);
  resequence(purchaseOrderItemsForm.value[index].subtotal_discounts);
};

const removeProductUnit = (index: number) => {
  purchaseOrderItemsForm.value.splice(index, 1);
  purchaseOrderItemDiscountsExpanded.value.splice(index, 1);
  Object.keys(purchaseOrderForm.errors).forEach((key) => {
    if (key.startsWith('items.')) {
      purchaseOrderForm.forgetError(key as any);
    }
  });
};

const togglePurchaseOrderItemDiscounts = (index: number) => {
  purchaseOrderItemDiscountsExpanded.value[index] = !purchaseOrderItemDiscountsExpanded.value[index];
};

const addDownPayment = () => {
  purchaseOrderDownPaymentsForm.value.push({
    code: '_AUTO_',
    date: '_AUTO_',
    cash_account_id: '',
    amount: 0,
    amount_allocated: 0,
    remarks: '',
  });
};

const removeDownPayment = (index: number) => {
  purchaseOrderDownPaymentsForm.value.splice(index, 1);
  Object.keys(purchaseOrderForm.errors).forEach((key) => {
    if (key.startsWith('down_payments.')) {
      purchaseOrderForm.forgetError(key as any);
    }
  });
};

const addRefundedDownPayment = () => {
  purchaseOrderRefundedDownPaymentsForm.value.push({
    code: '_AUTO_',
    date: '_AUTO_',
    cash_account_id: '',
    amount: 0,
    remarks: '',
  });
};

const removeRefundedDownPayment = (index: number) => {
  purchaseOrderRefundedDownPaymentsForm.value.splice(index, 1);
  Object.keys(purchaseOrderForm.errors).forEach((key) => {
    if (key.startsWith('refunded_down_payments.')) {
      purchaseOrderForm.forgetError(key as any);
    }
  });
};

const getItemUnitPriceAfterDiscountPreview = (item: PurchaseOrderItemFormItem) => {
  return item.product_unit_price_discounts.reduce((currentPrice, discount) => {
    const discountValue = Math.max(Number(discount.discount_value || 0), 0);
    const nextPrice = discount.discount_type === 'PERCENTAGE'
      ? currentPrice - ((currentPrice * discountValue) / 100)
      : currentPrice - discountValue;

    return Math.max(nextPrice, 0);
  }, Number(item.product_unit_price || 0));
};

const getItemUnitPriceSubtotalAfterDiscountPreview = (item: PurchaseOrderItemFormItem) =>
  Number(item.qty || 0) * getItemUnitPriceAfterDiscountPreview(item);

const getItemSubtotalAfterDiscountPreview = (item: PurchaseOrderItemFormItem) => {
  return item.subtotal_discounts.reduce((currentSubtotal, discount) => {
    const discountValue = Math.max(Number(discount.discount_value || 0), 0);
    const nextSubtotal = discount.discount_type === 'PERCENTAGE'
      ? currentSubtotal - ((currentSubtotal * discountValue) / 100)
      : currentSubtotal - discountValue;

    return Math.max(nextSubtotal, 0);
  }, getItemUnitPriceSubtotalAfterDiscountPreview(item));
};

const getItemsSubtotalAfterDiscountPreview = () =>
  purchaseOrderItemsForm.value.reduce((total, item) => total + getItemSubtotalAfterDiscountPreview(item), 0);

const getPurchaseOrderGlobalDiscountPreview = () => {
  let totalDiscount = 0;

  purchaseOrderGlobalDiscountsForm.value.forEach((discount) => {
    const discountValue = Math.max(Number(discount.discount_value || 0), 0);
    const currentTotal = Math.max(getItemsSubtotalAfterDiscountPreview() - totalDiscount, 0);
    const appliedDiscount = discount.discount_type === 'PERCENTAGE'
      ? (currentTotal * discountValue) / 100
      : discountValue;

    totalDiscount += Math.min(Math.max(appliedDiscount, 0), currentTotal);
  });

  return totalDiscount;
};

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

const getItemGrandTotalPreview = (item: PurchaseOrderItemFormItem) => {
  const itemIndex = purchaseOrderItemsForm.value.indexOf(item);

  if (itemIndex < 0) {
    return 0;
  }

  return getItemTotalBeforeRoundingPreview(item, itemIndex);
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
const formatCompactNumberValue = (value: number | string, precision = 4) =>
  formatCurrency(Number(Number(value ?? 0).toFixed(precision)));

const getGlobalDiscountedGrandTotalPreview = () =>
  purchaseOrderItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemTotalBeforeRoundingPreview(item, itemIndex),
    0,
  );

const getPurchaseOrderGrandTotalPreview = () =>
  getGlobalDiscountedGrandTotalPreview() + Number(purchaseOrderForm.rounding || 0);

const getDownPaymentsTotalPreview = () =>
  purchaseOrderDownPaymentsForm.value.reduce(
    (total, downPayment) => total + Math.max(Number(downPayment.amount || 0), 0),
    0,
  );

const getRefundedDownPaymentsTotalPreview = () =>
  purchaseOrderRefundedDownPaymentsForm.value.reduce(
    (total, refundedDownPayment) => total + Math.max(Number(refundedDownPayment.amount || 0), 0),
    0,
  );

const getAllocatedDownPaymentsTotalPreview = () =>
  purchaseOrderDownPaymentsForm.value.reduce(
    (total, downPayment) => total + Math.max(Number(downPayment.amount_allocated ?? 0), 0),
    0,
  );

const getAvailableDownPaymentsTotalPreview = () =>
  getDownPaymentsTotalPreview()
  - getAllocatedDownPaymentsTotalPreview()
  - getRefundedDownPaymentsTotalPreview();

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
  const backupGlobalDiscounts = [...purchaseOrderGlobalDiscountsForm.value];
  const backupDownPayments = [...purchaseOrderDownPaymentsForm.value];
  const backupRefundedDownPayments = [...purchaseOrderRefundedDownPaymentsForm.value];

  const cleanedItems: PurchaseOrderItemNestedStoreRequest[] = purchaseOrderItemsForm.value.map((item) => ({
    qty: item.qty,
    product_unit_id: item.product_unit_id,
    product_unit_conversion_value: item.product_unit_conversion_value,
    product_unit_price: item.product_unit_price,
    product_unit_price_discounts: item.product_unit_price_discounts.map((discount) => ({
      sequence: discount.sequence,
      discount_type: discount.discount_type,
      discount_value: discount.discount_value,
    })),
    subtotal_discounts: item.subtotal_discounts.map((discount) => ({
      sequence: discount.sequence,
      discount_type: discount.discount_type,
      discount_value: discount.discount_value,
    })),
    product_unit_is_price_include_vat: item.product_unit_is_price_include_vat,
    vat_profile_id: item.vat_profile_id,
    vat_rate: item.vat_rate,
    vat_base_numerator: item.vat_base_numerator,
    vat_base_denominator: item.vat_base_denominator,
    remarks: item.remarks,
  }));

  purchaseOrderForm.items = cleanedItems as any;
  purchaseOrderForm.global_discounts = purchaseOrderGlobalDiscountsForm.value.map((discount) => ({
    sequence: discount.sequence,
    discount_type: discount.discount_type,
    discount_value: discount.discount_value,
  })) as any;
  purchaseOrderForm.down_payments = purchaseOrderDownPaymentsForm.value.map((downPayment) => ({
    code: downPayment.code,
    date: downPayment.date,
    cash_account_id: downPayment.cash_account_id,
    amount: downPayment.amount,
    remarks: downPayment.remarks,
  })) as any;
  purchaseOrderForm.refunded_down_payments = purchaseOrderRefundedDownPaymentsForm.value.map((refundedDownPayment) => ({
    code: refundedDownPayment.code,
    date: refundedDownPayment.date,
    cash_account_id: refundedDownPayment.cash_account_id,
    amount: refundedDownPayment.amount,
    remarks: refundedDownPayment.remarks,
  })) as any;

  emits('loading-state', true);

  try {
    await purchaseOrderForm.submit();
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(t('views.purchase_order.alert.create.title'), t('views.purchase_order.alert.create.message'));
    router.push({ name: 'side-menu-purchase-order-list' });
  } catch (error) {
    purchaseOrderForm.items = backupItems as any;
    purchaseOrderForm.global_discounts = backupGlobalDiscounts as any;
    purchaseOrderForm.down_payments = backupDownPayments as any;
    purchaseOrderForm.refunded_down_payments = backupRefundedDownPayments as any;
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
            <div v-for="(item, index) in purchaseOrderItemsForm" :key="`${item.product_unit_id}-${index}`"
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
                        :preview-title="item.product_unit_product_name || t('views.purchase_order.fields.product_unit_id')" />
                    </div>
                  </div>
                </div>
                <!-- item product -->
                <div class="col-span-12">
                  <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.product_unit_id`) }">
                    <span>{{ t('views.purchase_order.fields.product_unit_id') }}</span>
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
                  <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.product_unit_id`)" />
                </div>
                <!-- item qty and unit -->
                <div class="col-span-12">
                  <div class="grid grid-cols-2 gap-4">
                    <!-- item qty -->
                    <div>
                      <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.qty`) }">
                        {{ t('views.purchase_order.fields.qty') }}
                      </FormLabel>
                      <FormInputCurrency :id="`purchase-order-item-qty-${index}`" v-model="item.qty"
                        :allow-negative="false"
                        :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.qty`) }"
                        @change="validatePurchaseOrderField(`items.${index}.qty`)" />
                      <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.qty`)" />
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
                  <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.product_unit_price`) }">
                    {{ t('views.purchase_order.fields.product_unit_price') }}
                  </FormLabel>
                  <FormInputCurrency v-model="item.product_unit_price" :allow-negative="false"
                    :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.product_unit_price`) }"
                    @change="validatePurchaseOrderField(`items.${index}.product_unit_price`)" />
                  <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.product_unit_price`)" />
                </div>
                <!-- item grand total -->
                <div class="col-span-12">
                  <FormLabel>{{ t('views.purchase_order.fields.subtotal_after_discount') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency :model-value="getItemSubtotalAfterDiscountPreview(item)" readonly />
                    </div>
                    <div class="shrink-0">
                      <Button type="button" variant="outline-secondary"
                        class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                        @click="togglePurchaseOrderItemDiscounts(index)">
                        {{ purchaseOrderItemDiscountsExpanded[index] ? '▲' : '▼' }}
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
                        :preview-title="item.product_unit_product_name || t('views.purchase_order.fields.product_unit_id')" />
                    </div>
                  </div>
                </div>
                <!-- item product -->
                <div class="col-span-12 md:col-span-9">
                  <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.product_unit_id`) }">
                    <span>{{ t('views.purchase_order.fields.product_unit_id') }}</span>
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
                  <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.product_unit_id`)" />
                </div>
                <!-- item qty and unit -->
                <div class="col-span-12 md:col-span-4">
                  <div class="grid grid-cols-2 gap-4">
                    <!-- item qty -->
                    <div>
                      <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.qty`) }">
                        {{ t('views.purchase_order.fields.qty') }}
                      </FormLabel>
                      <FormInputCurrency :id="`purchase-order-item-qty-${index}`" v-model="item.qty"
                        :allow-negative="false"
                        :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.qty`) }"
                        @change="validatePurchaseOrderField(`items.${index}.qty`)" />
                      <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.qty`)" />
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
                  <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.product_unit_price`) }">
                    {{ t('views.purchase_order.fields.product_unit_price') }}
                  </FormLabel>
                  <FormInputCurrency v-model="item.product_unit_price" :allow-negative="false"
                    :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.product_unit_price`) }"
                    @change="validatePurchaseOrderField(`items.${index}.product_unit_price`)" />
                  <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.product_unit_price`)" />
                </div>
                <!-- item grand total -->
                <div class="col-span-12 md:col-span-4">
                  <FormLabel>{{ t('views.purchase_order.fields.subtotal_after_discount') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency :model-value="getItemSubtotalAfterDiscountPreview(item)" readonly />
                    </div>
                    <div class="shrink-0">
                      <Button type="button" variant="outline-secondary"
                        class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                        @click="togglePurchaseOrderItemDiscounts(index)">
                        {{ purchaseOrderItemDiscountsExpanded[index] ? '▲' : '▼' }}
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
                        :preview-title="item.product_unit_product_name || t('views.purchase_order.fields.product_unit_id')" />
                    </div>
                  </div>
                </div>
                <!-- item product -->
                <div class="col-span-12 lg:col-span-4">
                  <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.product_unit_id`) }">
                    <span>{{ t('views.purchase_order.fields.product_unit_id') }}</span>
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
                  <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.product_unit_id`)" />
                </div>
                <!-- item qty and unit -->
                <div class="col-span-12 lg:col-span-2">
                  <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <!-- item qty -->
                    <div>
                      <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.qty`) }">
                        {{ t('views.purchase_order.fields.qty') }}
                      </FormLabel>
                      <FormInputCurrency :id="`purchase-order-item-qty-${index}`" v-model="item.qty"
                        :allow-negative="false"
                        :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.qty`) }"
                        @change="validatePurchaseOrderField(`items.${index}.qty`)" />
                      <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.qty`)" />
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
                  <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.product_unit_price`) }">
                    {{ t('views.purchase_order.fields.product_unit_price') }}
                  </FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency v-model="item.product_unit_price" :allow-negative="false"
                        :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.product_unit_price`) }"
                        @change="validatePurchaseOrderField(`items.${index}.product_unit_price`)" />
                    </div>
                  </div>
                  <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.product_unit_price`)" />
                </div>
                <!-- item grand total -->
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.purchase_order.fields.subtotal_after_discount') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency :model-value="getItemSubtotalAfterDiscountPreview(item)" readonly />
                    </div>
                    <div class="shrink-0">
                      <Button type="button" variant="outline-secondary"
                        class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                        @click="togglePurchaseOrderItemDiscounts(index)">
                        {{ purchaseOrderItemDiscountsExpanded[index] ? '▲' : '▼' }}
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

              <!-- item details: stacked breakdown panels for mobile and tablet -->
              <div v-if="currentItemLayout !== 'lg' && purchaseOrderItemDiscountsExpanded[index]"
                class="mt-4 space-y-4">
                <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-5">
                  <!-- stacked panel: price and discount breakdown -->
                  <div class="font-medium text-sm">{{ t('views.purchase_order.fields.item_price_breakdown') }}</div>

                  <!-- unit price discounts and derived totals -->
                  <div class="space-y-3">
                    <div v-if="item.product_unit_price_discounts.length === 0"
                      class="text-right text-slate-500 text-sm">
                      {{ t('views.purchase_order.fields.product_unit_price_discounts_empty') }}
                    </div>

                    <div v-else class="space-y-3">
                      <div v-for="(discount, discountIndex) in item.product_unit_price_discounts"
                        :key="`${index}-price-mobile-${discountIndex}`" class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 md:col-span-2">
                          <FormLabel>{{ t('views.purchase_order.fields.sequence') }}</FormLabel>
                          <FormInput :model-value="discountIndex + 1" readonly />
                        </div>
                        <div class="col-span-12 md:col-span-4">
                          <FormLabel>{{ t('views.purchase_order.fields.discount_type') }}</FormLabel>
                          <FormSelect v-model="discount.discount_type">
                            <option v-for="option in discountTypeOptions" :key="option.value" :value="option.value">
                              {{ option.label }}
                            </option>
                          </FormSelect>
                        </div>
                        <div class="col-span-12 md:col-span-6">
                          <FormLabel>{{ t('views.purchase_order.fields.discount_value') }}</FormLabel>
                          <div class="flex items-start gap-2">
                            <div class="flex-1 min-w-0">
                              <FormInputCurrency v-model="discount.discount_value" :allow-negative="false" />
                            </div>
                            <div class="shrink-0">
                              <Button type="button" variant="outline-secondary"
                                class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                                @click="removeItemPriceDiscount(index, discountIndex)">
                                <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                              </Button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="flex justify-end">
                      <Button type="button" variant="outline-primary" @click="addItemPriceDiscount(index)">
                        <Lucide icon="Plus" class="w-4 h-4 mr-1" />
                        {{ t('components.buttons.create_new') }}
                      </Button>
                    </div>

                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase_order.fields.price_after_discount') }}
                      </div>
                      <div class="col-span-12 md:col-span-8">
                        <FormInputCurrency :model-value="getItemUnitPriceAfterDiscountPreview(item)" readonly />
                      </div>
                    </div>

                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase_order.fields.subtotal') }}
                      </div>
                      <div class="col-span-12 md:col-span-8">
                        <FormInputCurrency :model-value="getItemUnitPriceSubtotalAfterDiscountPreview(item)" readonly />
                      </div>
                    </div>
                  </div>

                  <!-- subtotal discounts and final item subtotal -->
                  <div class="space-y-3">
                    <div v-if="item.subtotal_discounts.length === 0" class="text-right text-slate-500 text-sm">
                      {{ t('views.purchase_order.fields.subtotal_discounts_empty') }}
                    </div>

                    <div v-else class="space-y-3">
                      <div v-for="(discount, discountIndex) in item.subtotal_discounts"
                        :key="`${index}-subtotal-mobile-${discountIndex}`" class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 md:col-span-2">
                          <FormLabel>{{ t('views.purchase_order.fields.sequence') }}</FormLabel>
                          <FormInput :model-value="discountIndex + 1" readonly />
                        </div>
                        <div class="col-span-12 md:col-span-4">
                          <FormLabel>{{ t('views.purchase_order.fields.discount_type') }}</FormLabel>
                          <FormSelect v-model="discount.discount_type">
                            <option v-for="option in discountTypeOptions" :key="option.value" :value="option.value">
                              {{ option.label }}
                            </option>
                          </FormSelect>
                        </div>
                        <div class="col-span-12 md:col-span-6">
                          <FormLabel>{{ t('views.purchase_order.fields.discount_value') }}</FormLabel>
                          <div class="flex items-start gap-2">
                            <div class="flex-1 min-w-0">
                              <FormInputCurrency v-model="discount.discount_value" :allow-negative="false" />
                            </div>
                            <div class="shrink-0">
                              <Button type="button" variant="outline-secondary"
                                class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                                @click="removeItemSubtotalDiscount(index, discountIndex)">
                                <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                              </Button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="flex justify-end">
                      <Button type="button" variant="outline-primary" @click="addItemSubtotalDiscount(index)">
                        <Lucide icon="Plus" class="w-4 h-4 mr-1" />
                        {{ t('components.buttons.create_new') }}
                      </Button>
                    </div>

                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase_order.fields.subtotal_after_discount') }}
                      </div>
                      <div class="col-span-12 md:col-span-8">
                        <FormInputCurrency :model-value="getItemSubtotalAfterDiscountPreview(item)" readonly />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-4">
                  <!-- stacked panel: tax and additional item metadata -->
                  <div class="font-medium text-sm">{{ t('views.purchase_order.fields.item_additional_details') }}</div>

                  <div class="grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 md:col-span-4">
                      <FormLabel>{{ t('views.purchase_order.fields.product_unit_is_price_include_vat') }}</FormLabel>
                      <FormSwitch>
                        <FormSwitch.Input v-model="item.product_unit_is_price_include_vat" type="checkbox" />
                      </FormSwitch>
                    </div>
                    <div class="col-span-12 md:col-span-8">
                      <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.vat_profile_id`) }">
                        {{ t('views.purchase_order.fields.vat_profile_id') }}
                      </FormLabel>
                      <FormSelectSearch v-model="item.vat_profile_id" v-model:search="vatProfileSearch"
                        :options="vatProfileOptions" :placeholder="t('components.dropdown.placeholder')"
                        :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.vat_profile_id`) }"
                        @change="syncVatProfile(index)" @search="loadVatProfileDDL" @clear="clearVatProfile(index)" />
                      <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.vat_profile_id`)" />
                    </div>
                    <div class="col-span-12 md:col-span-4">
                      <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.vat_rate`) }">
                        {{ t('views.purchase_order.fields.vat_rate') }}
                      </FormLabel>
                      <FormInputCurrency v-model="item.vat_rate" :allow-negative="false"
                        :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.vat_rate`) }"
                        @change="validatePurchaseOrderField(`items.${index}.vat_rate`)" />
                      <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.vat_rate`)" />
                    </div>
                    <div class="col-span-12 md:col-span-4">
                      <FormLabel
                        :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.vat_base_numerator`) }">
                        {{ t('views.purchase_order.fields.vat_base_numerator') }}
                      </FormLabel>
                      <FormInput v-model="item.vat_base_numerator" type="number" min="1"
                        :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.vat_base_numerator`) }"
                        @change="validatePurchaseOrderField(`items.${index}.vat_base_numerator`)" />
                      <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.vat_base_numerator`)" />
                    </div>
                    <div class="col-span-12 md:col-span-4">
                      <FormLabel
                        :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.vat_base_denominator`) }">
                        {{ t('views.purchase_order.fields.vat_base_denominator') }}
                      </FormLabel>
                      <FormInput v-model="item.vat_base_denominator" type="number" min="1"
                        :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.vat_base_denominator`) }"
                        @change="validatePurchaseOrderField(`items.${index}.vat_base_denominator`)" />
                      <FormErrorMessages
                        :messages="getPurchaseOrderFieldErrors(`items.${index}.vat_base_denominator`)" />
                    </div>
                    <div class="col-span-12">
                      <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.remarks`) }">
                        {{ t('views.purchase_order.fields.remarks') }}
                      </FormLabel>
                      <FormTextarea v-model="item.remarks"
                        :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.remarks`) }"
                        @change="validatePurchaseOrderField(`items.${index}.remarks`)" />
                      <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.remarks`)" />
                    </div>
                  </div>
                </div>
              </div>
              <!-- item details: desktop breakdown panels -->
              <div v-else-if="purchaseOrderItemDiscountsExpanded[index]" class="mt-4 grid grid-cols-12 gap-4">
                <div class="col-span-12 lg:col-span-6">
                  <!-- right panel: tax and additional item metadata -->
                  <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-4">
                    <div class="font-medium text-sm">{{ t('views.purchase_order.fields.item_additional_details') }}
                    </div>

                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-5">
                        <FormLabel>{{
                          t('views.purchase_order.fields.product_unit_is_price_include_vat') }}</FormLabel>
                        <FormSwitch>
                          <FormSwitch.Input v-model="item.product_unit_is_price_include_vat" type="checkbox" />
                        </FormSwitch>
                      </div>
                      <div class="col-span-12 md:col-span-7">
                        <FormLabel
                          :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.vat_profile_id`) }">
                          {{ t('views.purchase_order.fields.vat_profile_id') }}
                        </FormLabel>
                        <FormSelectSearch v-model="item.vat_profile_id" v-model:search="vatProfileSearch"
                          :options="vatProfileOptions" :placeholder="t('components.dropdown.placeholder')"
                          :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.vat_profile_id`) }"
                          @change="syncVatProfile(index)" @search="loadVatProfileDDL" @clear="clearVatProfile(index)" />
                        <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.vat_profile_id`)" />
                      </div>
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.vat_rate`) }">
                          {{ t('views.purchase_order.fields.vat_rate') }}
                        </FormLabel>
                        <FormInputCurrency v-model="item.vat_rate" :allow-negative="false"
                          :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.vat_rate`) }"
                          @change="validatePurchaseOrderField(`items.${index}.vat_rate`)" />
                        <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.vat_rate`)" />
                      </div>
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel
                          :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.vat_base_numerator`) }">
                          {{ t('views.purchase_order.fields.vat_base_numerator') }}
                        </FormLabel>
                        <FormInput v-model="item.vat_base_numerator" type="number" min="1"
                          :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.vat_base_numerator`) }"
                          @change="validatePurchaseOrderField(`items.${index}.vat_base_numerator`)" />
                        <FormErrorMessages
                          :messages="getPurchaseOrderFieldErrors(`items.${index}.vat_base_numerator`)" />
                      </div>
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel
                          :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.vat_base_denominator`) }">
                          {{ t('views.purchase_order.fields.vat_base_denominator') }}
                        </FormLabel>
                        <FormInput v-model="item.vat_base_denominator" type="number" min="1"
                          :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.vat_base_denominator`) }"
                          @change="validatePurchaseOrderField(`items.${index}.vat_base_denominator`)" />
                        <FormErrorMessages
                          :messages="getPurchaseOrderFieldErrors(`items.${index}.vat_base_denominator`)" />
                      </div>
                      <div class="col-span-12">
                        <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.remarks`) }">
                          {{ t('views.purchase_order.fields.remarks') }}
                        </FormLabel>
                        <FormTextarea v-model="item.remarks"
                          :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.remarks`) }"
                          @change="validatePurchaseOrderField(`items.${index}.remarks`)" />
                        <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.remarks`)" />
                      </div>
                    </div>
                  </div>
                </div>
                <div class="col-span-12 lg:col-span-6">
                  <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-5">
                    <!-- left panel: price and discount breakdown -->
                    <div class="font-medium text-sm">{{ t('views.purchase_order.fields.item_price_breakdown') }}</div>

                    <!-- unit price discounts and derived totals -->
                    <div class="space-y-3">
                      <div v-if="item.product_unit_price_discounts.length === 0"
                        class="text-right text-slate-500 text-sm">
                        {{ t('views.purchase_order.fields.product_unit_price_discounts_empty') }}
                      </div>

                      <div v-else class="space-y-3">
                        <div v-for="(discount, discountIndex) in item.product_unit_price_discounts"
                          :key="`${index}-price-${discountIndex}`" class="grid grid-cols-12 gap-4 gap-y-3">
                          <div class="col-span-12 md:col-span-2">
                            <FormLabel>{{ t('views.purchase_order.fields.sequence') }}</FormLabel>
                            <FormInput :model-value="discountIndex + 1" readonly />
                          </div>
                          <div class="col-span-12 md:col-span-4">
                            <FormLabel>{{ t('views.purchase_order.fields.discount_type') }}</FormLabel>
                            <FormSelect v-model="discount.discount_type">
                              <option v-for="option in discountTypeOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                              </option>
                            </FormSelect>
                          </div>
                          <div class="col-span-12 md:col-span-6">
                            <FormLabel>{{ t('views.purchase_order.fields.discount_value') }}</FormLabel>
                            <div class="flex items-start gap-2">
                              <div class="flex-1">
                                <FormInputCurrency v-model="discount.discount_value" :allow-negative="false" />
                              </div>
                              <div class="shrink-0">
                                <Button type="button" variant="outline-secondary"
                                  class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                                  @click="removeItemPriceDiscount(index, discountIndex)">
                                  <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                                </Button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="flex justify-end">
                        <Button type="button" variant="outline-primary" @click="addItemPriceDiscount(index)">
                          <Lucide icon="Plus" class="w-4 h-4 mr-1" />
                          {{ t('components.buttons.create_new') }}
                        </Button>
                      </div>

                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                          {{ t('views.purchase_order.fields.price_after_discount') }}
                        </div>
                        <div class="col-span-12 md:col-span-8">
                          <FormInputCurrency :model-value="getItemUnitPriceAfterDiscountPreview(item)" readonly />
                        </div>
                      </div>

                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                          {{ t('views.purchase_order.fields.subtotal') }}
                        </div>
                        <div class="col-span-12 md:col-span-8">
                          <FormInputCurrency :model-value="getItemUnitPriceSubtotalAfterDiscountPreview(item)"
                            readonly />
                        </div>
                      </div>
                    </div>

                    <!-- subtotal discounts and final item subtotal -->
                    <div class="space-y-3">
                      <div v-if="item.subtotal_discounts.length === 0" class="text-right text-slate-500 text-sm">
                        {{ t('views.purchase_order.fields.subtotal_discounts_empty') }}
                      </div>

                      <div v-else class="space-y-3">
                        <div v-for="(discount, discountIndex) in item.subtotal_discounts"
                          :key="`${index}-subtotal-${discountIndex}`" class="grid grid-cols-12 gap-4 gap-y-3">
                          <div class="col-span-12 md:col-span-2">
                            <FormLabel>{{ t('views.purchase_order.fields.sequence') }}</FormLabel>
                            <FormInput :model-value="discountIndex + 1" readonly />
                          </div>
                          <div class="col-span-12 md:col-span-4">
                            <FormLabel>{{ t('views.purchase_order.fields.discount_type') }}</FormLabel>
                            <FormSelect v-model="discount.discount_type">
                              <option v-for="option in discountTypeOptions" :key="option.value" :value="option.value">
                                {{ option.label }}
                              </option>
                            </FormSelect>
                          </div>
                          <div class="col-span-12 md:col-span-6">
                            <FormLabel>{{ t('views.purchase_order.fields.discount_value') }}</FormLabel>
                            <div class="flex items-start gap-2">
                              <div class="flex-1">
                                <FormInputCurrency v-model="discount.discount_value" :allow-negative="false" />
                              </div>
                              <div class="shrink-0">
                                <Button type="button" variant="outline-secondary"
                                  class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                                  @click="removeItemSubtotalDiscount(index, discountIndex)">
                                  <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                                </Button>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                      <div class="flex justify-end">
                        <Button type="button" variant="outline-primary" @click="addItemSubtotalDiscount(index)">
                          <Lucide icon="Plus" class="w-4 h-4 mr-1" />
                          {{ t('components.buttons.create_new') }}
                        </Button>
                      </div>

                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                          {{ t('views.purchase_order.fields.subtotal_after_discount') }}
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
          <!-- summary card: item totals, global discounts, and down payments -->
          <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-4">
            <!-- summary: total of all item grand totals after item-level discounts -->
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
              <!-- global discounts: editor -->
              <div v-if="isGlobalDiscountEditorExpanded" class="space-y-4">
                <FormErrorMessages :messages="purchaseOrderForm.errors.global_discounts" />

                <!-- global discounts: empty state -->
                <div v-if="purchaseOrderGlobalDiscountsForm.length === 0" class="text-right text-slate-500 text-sm">
                  {{ t('components.data-list.data_not_found') }}
                </div>

                <!-- global discounts: list -->
                <div v-else class="space-y-3">
                  <div v-for="(discount, index) in purchaseOrderGlobalDiscountsForm" :key="`global-discount-${index}`"
                    class="grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 md:col-span-4 lg:col-span-6">

                    </div>
                    <div class="col-span-12 md:col-span-2 lg:col-span-1">
                      <FormLabel>{{ t('views.purchase_order.fields.sequence') }}</FormLabel>
                      <FormInput :model-value="index + 1" readonly />
                    </div>
                    <div class="col-span-12 md:col-span-3 lg:col-span-3">
                      <FormLabel>{{ t('views.purchase_order.fields.discount_type') }}</FormLabel>
                      <FormSelect v-model="discount.discount_type">
                        <option v-for="option in discountTypeOptions" :key="option.value" :value="option.value">
                          {{ option.label }}
                        </option>
                      </FormSelect>
                    </div>
                    <div class="col-span-12 md:col-span-3 lg:col-span-2">
                      <FormLabel>{{ t('views.purchase_order.fields.discount_value') }}</FormLabel>
                      <div class="flex items-start gap-2">
                        <div class="flex-1 min-w-0">
                          <FormInputCurrency v-model="discount.discount_value" :allow-negative="false" />
                        </div>
                        <div class="shrink-0">
                          <Button type="button" variant="outline-secondary"
                            class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                            @click="removeGlobalDiscount(index)">
                            <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                          </Button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <!-- global discounts: add action -->
                <div class="flex justify-end">
                  <Button type="button" variant="outline-primary" @click="addGlobalDiscount">
                    <Lucide icon="Plus" class="w-4 h-4 mr-1" />
                    {{ t('components.buttons.create_new') }}
                  </Button>
                </div>
              </div>

              <!-- global discounts: collapsed summary row and expand trigger -->
              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <!-- summary spacer: keeps the numeric field aligned with other totals -->
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.purchase_order.fields.global_discount_total') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="shrink-0">
                      <Button type="button" variant="outline-secondary"
                        class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                        @click="isGlobalDiscountEditorExpanded = !isGlobalDiscountEditorExpanded">
                        {{ isGlobalDiscountEditorExpanded ? '▲' : '▼' }}
                      </Button>
                    </div>
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency :model-value="getPurchaseOrderGlobalDiscountPreview()" readonly />
                    </div>
                  </div>
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

                <!-- summary: rounding adjustment before grand total -->
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

              <!-- summary: grand total after applying global discounts and rounding -->
              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <!-- summary spacer: keeps the numeric field aligned with other totals -->
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.purchase_order.fields.grand_total') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="shrink-0">
                      <Button type="button" variant="outline-secondary"
                        class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                        @click="isTotalsBreakdownExpanded = !isTotalsBreakdownExpanded">
                        {{ isTotalsBreakdownExpanded ? '▲' : '▼' }}
                      </Button>
                    </div>
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency :model-value="getPurchaseOrderGrandTotalPreview()" readonly />
                    </div>
                  </div>
                </div>
              </div>

              <!-- summary: down payment editor and aggregates -->
              <div class="space-y-4">
                <!-- down payments: editor -->
                <div v-if="isDownPaymentEditorExpanded" class="space-y-4">
                  <FormErrorMessages :messages="purchaseOrderForm.errors.down_payments" />

                  <!-- down payments: empty state -->
                  <div v-if="purchaseOrderDownPaymentsForm.length === 0" class="text-right text-slate-500 text-sm">
                    {{ t('components.data-list.data_not_found') }}
                  </div>

                  <!-- down payments: list -->
                  <div v-else class="space-y-4">
                    <div v-for="(downPayment, index) in purchaseOrderDownPaymentsForm" :key="`down-payment-${index}`"
                      class="space-y-3">
                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <!-- down payment spacer: keeps editor fields aligned to the right side -->
                        <div class="col-span-12 md:col-span-6 lg:col-span-2"></div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-2">
                          <FormLabel
                            :class="{ 'text-danger': invalidPurchaseOrderField(`down_payments.${index}.code`) }">
                            {{ t('views.purchase_order.fields.code') }}
                          </FormLabel>
                          <FormInputCode v-model="downPayment.code"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`down_payments.${index}.code`) }"
                            :placeholder="t('views.purchase_order.fields.code')" @set-auto="setDownPaymentCode(index)"
                            @change="validatePurchaseOrderField(`down_payments.${index}.code`)" />
                          <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`down_payments.${index}.code`)" />
                        </div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-4">
                          <FormLabel
                            :class="{ 'text-danger': invalidPurchaseOrderField(`down_payments.${index}.date`) }">
                            {{ t('views.purchase_order.fields.date') }}
                          </FormLabel>
                          <FormInputDateTimeAuto v-model="downPayment.date"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`down_payments.${index}.date`) }"
                            :placeholder="t('views.purchase_order.fields.date')"
                            @change="validatePurchaseOrderField(`down_payments.${index}.date`)" />
                          <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`down_payments.${index}.date`)" />
                        </div>
                        <div class="col-span-12 md:col-span-8 lg:col-span-2">
                          <FormLabel
                            :class="{ 'text-danger': invalidPurchaseOrderField(`down_payments.${index}.cash_account_id`) }">
                            {{ t('views.purchase_order.fields.cash_account_id') }}
                          </FormLabel>
                          <FormSelectSearch v-model="downPayment.cash_account_id" v-model:search="cashAccountSearch"
                            :options="cashAccountOptions" :placeholder="t('components.dropdown.placeholder')"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`down_payments.${index}.cash_account_id`) }"
                            @change="validatePurchaseOrderField(`down_payments.${index}.cash_account_id`)"
                            @search="loadCashAccountDDL" @clear="clearCashAccount(index)" />
                          <FormErrorMessages
                            :messages="getPurchaseOrderFieldErrors(`down_payments.${index}.cash_account_id`)" />
                        </div>
                        <div class="col-span-12 md:col-span-4 lg:col-span-2">
                          <FormLabel
                            :class="{ 'text-danger': invalidPurchaseOrderField(`down_payments.${index}.amount`) }">
                            {{ t('views.purchase_order.fields.amount') }}
                          </FormLabel>
                          <div class="flex items-start gap-2">
                            <div class="flex-1 min-w-0">
                              <FormInputCurrency v-model="downPayment.amount" :allow-negative="false"
                                :class="{ 'border-danger': invalidPurchaseOrderField(`down_payments.${index}.amount`) }"
                                @change="validatePurchaseOrderField(`down_payments.${index}.amount`)" />
                            </div>
                            <div class="shrink-0">
                              <Button type="button" variant="outline-secondary"
                                class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                                @click="removeDownPayment(index)">
                                <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                              </Button>
                            </div>
                          </div>
                          <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`down_payments.${index}.amount`)" />
                        </div>
                      </div>
                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <!-- down payment spacer: keeps remarks width consistent with fields above -->
                        <div class="col-span-12 lg:col-span-2"></div>
                        <div class="col-span-12 lg:col-span-7">
                          <FormLabel
                            :class="{ 'text-danger': invalidPurchaseOrderField(`down_payments.${index}.remarks`) }">
                            {{ t('views.purchase_order.fields.remarks') }}
                          </FormLabel>
                          <FormTextarea v-model="downPayment.remarks"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`down_payments.${index}.remarks`) }"
                            @change="validatePurchaseOrderField(`down_payments.${index}.remarks`)" />
                          <FormErrorMessages
                            :messages="getPurchaseOrderFieldErrors(`down_payments.${index}.remarks`)" />
                        </div>
                        <div class="col-span-12 lg:col-span-3">
                          <FormLabel>{{ t('views.purchase_order.fields.amount_allocated') }}</FormLabel>
                          <FormInputCurrency :model-value="Number(downPayment.amount_allocated ?? 0)" readonly />
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- down payments: add action -->
                  <div class="flex justify-end">
                    <Button type="button" variant="outline-primary" @click="addDownPayment">
                      <Lucide icon="Plus" class="w-4 h-4 mr-1" />
                      {{ t('components.buttons.create_new') }}
                    </Button>
                  </div>
                </div>

                <!-- down payments: collapsed summary row and expand trigger -->
                <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                  <!-- summary spacer: keeps the numeric field aligned with other totals -->
                  <div class="col-span-12 lg:col-span-9"></div>
                  <div class="col-span-12 lg:col-span-3">
                    <FormLabel>{{ t('views.purchase_order.fields.amount_paid_down_payment') }}</FormLabel>
                    <div class="flex items-start gap-2">
                      <div class="shrink-0">
                        <Button type="button" variant="outline-secondary"
                          class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                          @click="isDownPaymentEditorExpanded = !isDownPaymentEditorExpanded">
                          {{ isDownPaymentEditorExpanded ? '▲' : '▼' }}
                        </Button>
                      </div>
                      <div class="flex-1 min-w-0">
                        <FormInputCurrency :model-value="getDownPaymentsTotalPreview()" readonly />
                      </div>
                    </div>
                  </div>
                </div>

                <div v-if="isRefundedDownPaymentEditorExpanded" class="space-y-4">
                  <FormErrorMessages :messages="purchaseOrderForm.errors.refunded_down_payments" />

                  <div v-if="purchaseOrderRefundedDownPaymentsForm.length === 0"
                    class="text-right text-slate-500 text-sm">
                    {{ t('components.data-list.data_not_found') }}
                  </div>

                  <div v-else class="space-y-4">
                    <div v-for="(refundedDownPayment, index) in purchaseOrderRefundedDownPaymentsForm"
                      :key="`refunded-down-payment-${index}`" class="space-y-3">
                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 md:col-span-6 lg:col-span-2"></div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-2">
                          <FormLabel
                            :class="{ 'text-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.code`) }">
                            {{ t('views.purchase_order.fields.code') }}
                          </FormLabel>
                          <FormInputCode v-model="refundedDownPayment.code"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.code`) }"
                            :placeholder="t('views.purchase_order.fields.code')"
                            @set-auto="setRefundedDownPaymentCode(index)"
                            @change="validatePurchaseOrderField(`refunded_down_payments.${index}.code`)" />
                          <FormErrorMessages
                            :messages="getPurchaseOrderFieldErrors(`refunded_down_payments.${index}.code`)" />
                        </div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-4">
                          <FormLabel
                            :class="{ 'text-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.date`) }">
                            {{ t('views.purchase_order.fields.date') }}
                          </FormLabel>
                          <FormInputDateTimeAuto v-model="refundedDownPayment.date"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.date`) }"
                            :placeholder="t('views.purchase_order.fields.date')"
                            @change="validatePurchaseOrderField(`refunded_down_payments.${index}.date`)" />
                          <FormErrorMessages
                            :messages="getPurchaseOrderFieldErrors(`refunded_down_payments.${index}.date`)" />
                        </div>
                        <div class="col-span-12 md:col-span-8 lg:col-span-2">
                          <FormLabel
                            :class="{ 'text-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.cash_account_id`) }">
                            {{ t('views.purchase_order.fields.cash_account_id') }}
                          </FormLabel>
                          <FormSelectSearch v-model="refundedDownPayment.cash_account_id"
                            v-model:search="cashAccountSearch" :options="cashAccountOptions"
                            :placeholder="t('components.dropdown.placeholder')"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.cash_account_id`) }"
                            @change="validatePurchaseOrderField(`refunded_down_payments.${index}.cash_account_id`)"
                            @search="loadCashAccountDDL" @clear="clearRefundedCashAccount(index)" />
                          <FormErrorMessages
                            :messages="getPurchaseOrderFieldErrors(`refunded_down_payments.${index}.cash_account_id`)" />
                        </div>
                        <div class="col-span-12 md:col-span-4 lg:col-span-2">
                          <FormLabel
                            :class="{ 'text-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.amount`) }">
                            {{ t('views.purchase_order.fields.amount') }}
                          </FormLabel>
                          <div class="flex items-start gap-2">
                            <div class="flex-1 min-w-0">
                              <FormInputCurrency v-model="refundedDownPayment.amount" :allow-negative="false"
                                :class="{ 'border-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.amount`) }"
                                @change="validatePurchaseOrderField(`refunded_down_payments.${index}.amount`)" />
                            </div>
                            <div class="shrink-0">
                              <Button type="button" variant="outline-secondary"
                                class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                                @click="removeRefundedDownPayment(index)">
                                <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                              </Button>
                            </div>
                          </div>
                          <FormErrorMessages
                            :messages="getPurchaseOrderFieldErrors(`refunded_down_payments.${index}.amount`)" />
                        </div>
                      </div>
                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 lg:col-span-2"></div>
                        <div class="col-span-12 lg:col-span-10">
                          <FormLabel
                            :class="{ 'text-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.remarks`) }">
                            {{ t('views.purchase_order.fields.remarks') }}
                          </FormLabel>
                          <FormTextarea v-model="refundedDownPayment.remarks"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.remarks`) }"
                            @change="validatePurchaseOrderField(`refunded_down_payments.${index}.remarks`)" />
                          <FormErrorMessages
                            :messages="getPurchaseOrderFieldErrors(`refunded_down_payments.${index}.remarks`)" />
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="flex justify-end">
                    <Button type="button" variant="outline-primary" @click="addRefundedDownPayment">
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
                          @click="isRefundedDownPaymentEditorExpanded = !isRefundedDownPaymentEditorExpanded">
                          {{ isRefundedDownPaymentEditorExpanded ? '▲' : '▼' }}
                        </Button>
                      </div>
                      <div class="flex-1 min-w-0">
                        <FormInputCurrency :model-value="getRefundedDownPaymentsTotalPreview()" readonly />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                  <div class="col-span-12 lg:col-span-9"></div>
                  <div class="col-span-12 lg:col-span-3">
                    <FormLabel>{{ t('views.purchase_order.fields.amount_allocated_down_payment') }}</FormLabel>
                    <FormInputCurrency :model-value="getAllocatedDownPaymentsTotalPreview()" readonly />
                  </div>
                </div>

                <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                  <div class="col-span-12 lg:col-span-9"></div>
                  <div class="col-span-12 lg:col-span-3">
                    <FormLabel>{{ t('views.purchase_order.fields.amount_available_down_payment') }}</FormLabel>
                    <FormInputCurrency :model-value="getAvailableDownPaymentsTotalPreview()" readonly />
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

  <!-- dialog: product unit picker for add/change item -->
  <ProductUnitPickerDialog size="xl" panel-class="max-w-5xl" :open="showProductUnitModal"
    :title="t('views.purchase_order.fields.product_unit_id')" :search-text="productSearchText"
    :is-searching="isSearchingProductUnit" :options="productUnitOptions" :columns="productUnitDialogColumns"
    @update:search-text="productSearchText = $event" @search="searchProductUnits"
    @select="selectProductUnit($event as ProductUnitOption)" @close="showProductUnitModal = false"
    @after-leave="handleProductUnitModalAfterLeave" />
</template>
