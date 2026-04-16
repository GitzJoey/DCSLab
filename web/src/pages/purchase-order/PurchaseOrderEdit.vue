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
import PurchaseOrderService from '@/services/PurchaseOrderService';
import SupplierService from '@/services/SupplierService';
import VatProfileService from '@/services/VatProfileService';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { DropDownOption } from '@/types/models/DropDownOption';
import { NotificationData } from '@/types/models/NotificationData';
import { PurchaseOrder } from '@/types/models/PurchaseOrder';
import {
  PurchaseOrderDownPaymentNestedUpdateRequest,
  PurchaseOrderDownPaymentRefundNestedUpdateRequest,
  PurchaseOrderGlobalDiscountNestedUpdateRequest,
  PurchaseOrderItemDiscountNestedUpdateRequest,
  PurchaseOrderItemNestedUpdateRequest,
} from '@/types/services/purchase-order/PurchaseOrderRequest';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { convertErrorTypeToAlertListType, formatDate } from '@/utils/helper';

type PurchaseOrderItemFormItem = PurchaseOrderItemNestedUpdateRequest & {
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
  conversion_value: number;
  price: number;
  product_unit_is_price_include_vat: boolean;
  vat_profile_id: string | null;
  vat_profile_name: string | null;
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
  { title: 'views.purchase_order.field_groups.summary', state: CardState.Collapsed },
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

const vatProfileDDL = ref<Array<DropDownOption> | null>(null);
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
const purchaseOrderGlobalDiscountsForm = computed<PurchaseOrderGlobalDiscountNestedUpdateRequest[]>(
  () => purchaseOrderForm.global_discounts as PurchaseOrderGlobalDiscountNestedUpdateRequest[],
);
const purchaseOrderDownPaymentsForm = computed<PurchaseOrderDownPaymentNestedUpdateRequest[]>(
  () => purchaseOrderForm.down_payments as PurchaseOrderDownPaymentNestedUpdateRequest[],
);
const purchaseOrderRefundedDownPaymentsForm = computed<PurchaseOrderDownPaymentRefundNestedUpdateRequest[]>(
  () => purchaseOrderForm.refunded_down_payments as PurchaseOrderDownPaymentRefundNestedUpdateRequest[],
);
const isGlobalDiscountEditorExpanded = ref(false);
const isDownPaymentEditorExpanded = ref(false);
const isRefundedDownPaymentEditorExpanded = ref(false);

const invalidPurchaseOrderField = (field: string) => purchaseOrderForm.invalid(field as any);
const validatePurchaseOrderField = (field: string) => purchaseOrderForm.validate(field as any);
const getPurchaseOrderFieldErrors = (field: string) =>
  (purchaseOrderForm.errors as Record<string, string | undefined>)[field];

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
    await Promise.all([loadSupplierDDL(), loadVatProfileDDL(), loadCashAccountDDL()]);
    await loadData();
    await Promise.all([loadSupplierDDL(), loadVatProfileDDL(), loadCashAccountDDL()]);
  } finally {
    emits('loading-state', false);
  }
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

const appendDDL = (
  target: typeof supplierDDL | typeof vatProfileDDL | typeof cashAccountDDL,
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
    }));
  }

  (purchaseOrderData.value?.items ?? []).forEach((item) => appendDDL(vatProfileDDL, item.vat_profile));
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

  (purchaseOrderData.value?.down_payments ?? []).forEach((item) => appendDDL(cashAccountDDL, item.cash_account));
  (purchaseOrderData.value?.refunded_down_payments ?? []).forEach((item) => appendDDL(cashAccountDDL, item.cash_account));
};

const clearSupplier = () => {
  purchaseOrderForm.setData({ supplier_id: null });
  purchaseOrderForm.forgetError('supplier_id');
  purchaseOrderForm.validate('supplier_id');
};

const clearVatProfile = (index: number) => {
  const item = purchaseOrderItemsForm.value[index];
  if (!item) return;
  item.vat_profile_id = null;
  item.vat_profile_name = null;
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
      product_unit_conversion_value: item.product_unit_conversion_value,
      product_unit_price: item.product_unit_price,
      delete_product_unit_price_discount_ids: [],
      product_unit_price_discounts: (item.product_unit_price_discounts || []).map((discount: any) => ({
        id: discount.id ?? null,
        sequence: discount.sequence,
        discount_type: discount.discount_type,
        discount_value: discount.discount_value,
      })),
      delete_subtotal_discount_ids: [],
      subtotal_discounts: (item.subtotal_discounts || []).map((discount: any) => ({
        id: discount.id ?? null,
        sequence: discount.sequence,
        discount_type: discount.discount_type,
        discount_value: discount.discount_value,
      })),
      product_unit_is_price_include_vat: item.product_unit_is_price_include_vat,
      vat_profile_id: item.vat_profile?.id ?? null,
      vat_profile_name: item.vat_profile?.name ?? null,
      vat_rate: item.vat_rate,
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
      rounding: data.rounding ?? 0,
      delete_global_discount_ids: [],
      global_discounts: (data.global_discounts || []).map((discount: any) => ({
        id: discount.id ?? null,
        sequence: discount.sequence,
        discount_type: discount.discount_type,
        discount_value: discount.discount_value,
      })) as any,
      delete_item_ids: [],
      items: items as any,
      delete_down_payment_ids: [],
      down_payments: (data.down_payments || []).map((downPayment: any) => ({
        id: downPayment.id ?? null,
        code: downPayment.code,
        date: formatDate(downPayment.date, 'YYYY-MM-DD HH:mm:ss'),
        cash_account_id: downPayment.cash_account?.id ?? '',
        amount: downPayment.amount,
        remarks: downPayment.remarks ?? '',
      })) as any,
      delete_refunded_down_payment_ids: [],
      refunded_down_payments: (data.refunded_down_payments || []).map((refundedDownPayment: any) => ({
        id: refundedDownPayment.id ?? null,
        code: refundedDownPayment.code,
        date: formatDate(refundedDownPayment.date, 'YYYY-MM-DD HH:mm:ss'),
        cash_account_id: refundedDownPayment.cash_account?.id ?? '',
        amount: refundedDownPayment.amount,
        remarks: refundedDownPayment.remarks ?? '',
      })) as any,
    } as any);
    purchaseOrderItemDiscountsExpanded.value = items.map(() => false);
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
      return units.map((unit: any) => ({
        product_unit_id: unit.id,
        product_unit_code: unit.code,
        product_name: product.name,
        product_image_url: product.main_product_image?.url ?? null,
        unit_name: unit.unit?.name ?? '',
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
  const itemData: Partial<PurchaseOrderItemFormItem> = {
    product_unit_id: option.product_unit_id,
    product_unit_product_code: option.product_unit_code,
    product_unit_product_name: option.product_name,
    product_unit_product_image_url: option.product_image_url,
    product_unit_unit_name: option.unit_name,
    product_unit_conversion_value: option.conversion_value,
    product_unit_price: option.price,
    product_unit_is_price_include_vat: option.product_unit_is_price_include_vat,
    vat_profile_id: option.vat_profile_id,
    vat_profile_name: option.vat_profile_name,
    vat_rate: option.vat_rate,
    vat_base_numerator: option.vat_base_numerator,
    vat_base_denominator: option.vat_base_denominator,
  };

  let targetIndex: number;

  if (editingProductUnitIndex.value === null) {
    purchaseOrderForm.items.push({
      id: null,
      qty: 1,
      product_unit_id: option.product_unit_id,
      product_unit_product_code: option.product_unit_code,
      product_unit_product_name: option.product_name,
      product_unit_product_image_url: option.product_image_url,
      product_unit_unit_name: option.unit_name,
      product_unit_conversion_value: option.conversion_value,
      product_unit_price: option.price,
      delete_product_unit_price_discount_ids: [],
      product_unit_price_discounts: [],
      delete_subtotal_discount_ids: [],
      subtotal_discounts: [],
      product_unit_is_price_include_vat: option.product_unit_is_price_include_vat,
      vat_profile_id: option.vat_profile_id,
      vat_profile_name: option.vat_profile_name,
      vat_rate: option.vat_rate,
      vat_base_numerator: option.vat_base_numerator,
      vat_base_denominator: option.vat_base_denominator,
      remarks: '',
    } as any);
    targetIndex = purchaseOrderForm.items.length - 1;
    purchaseOrderItemDiscountsExpanded.value[targetIndex] = true;
  } else {
    purchaseOrderItemsForm.value[editingProductUnitIndex.value] = {
      ...purchaseOrderItemsForm.value[editingProductUnitIndex.value],
      ...itemData,
    } as PurchaseOrderItemFormItem;
    targetIndex = editingProductUnitIndex.value;
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
    id: null,
    sequence: purchaseOrderGlobalDiscountsForm.value.length + 1,
    discount_type: 'PERCENTAGE',
    discount_value: 0,
  });
};

const removeGlobalDiscount = (index: number) => {
  const discount = purchaseOrderGlobalDiscountsForm.value[index];
  if (discount?.id) {
    purchaseOrderForm.delete_global_discount_ids.push(discount.id);
  }
  purchaseOrderGlobalDiscountsForm.value.splice(index, 1);
  resequence(purchaseOrderGlobalDiscountsForm.value);
};

const addItemPriceDiscount = (index: number) => {
  purchaseOrderItemsForm.value[index].product_unit_price_discounts.push({
    id: null,
    sequence: purchaseOrderItemsForm.value[index].product_unit_price_discounts.length + 1,
    discount_type: 'PERCENTAGE',
    discount_value: 0,
  });
};

const removeItemPriceDiscount = (index: number, discountIndex: number) => {
  const discount = purchaseOrderItemsForm.value[index].product_unit_price_discounts[discountIndex];
  if (discount?.id) {
    purchaseOrderItemsForm.value[index].delete_product_unit_price_discount_ids.push(discount.id);
  }
  purchaseOrderItemsForm.value[index].product_unit_price_discounts.splice(discountIndex, 1);
  resequence(purchaseOrderItemsForm.value[index].product_unit_price_discounts);
};

const addItemSubtotalDiscount = (index: number) => {
  purchaseOrderItemsForm.value[index].subtotal_discounts.push({
    id: null,
    sequence: purchaseOrderItemsForm.value[index].subtotal_discounts.length + 1,
    discount_type: 'PERCENTAGE',
    discount_value: 0,
  });
};

const removeItemSubtotalDiscount = (index: number, discountIndex: number) => {
  const discount = purchaseOrderItemsForm.value[index].subtotal_discounts[discountIndex];
  if (discount?.id) {
    purchaseOrderItemsForm.value[index].delete_subtotal_discount_ids.push(discount.id);
  }
  purchaseOrderItemsForm.value[index].subtotal_discounts.splice(discountIndex, 1);
  resequence(purchaseOrderItemsForm.value[index].subtotal_discounts);
};

const removeProductUnit = (index: number) => {
  const item = purchaseOrderItemsForm.value[index];
  if (item?.id) {
    purchaseOrderForm.delete_item_ids.push(item.id);
  }
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
    id: null,
    code: '_AUTO_',
    date: '_AUTO_',
    cash_account_id: '',
    amount: 0,
    remarks: '',
  });
};

const removeDownPayment = (index: number) => {
  const downPayment = purchaseOrderDownPaymentsForm.value[index];
  if (downPayment?.id) {
    purchaseOrderForm.delete_down_payment_ids.push(downPayment.id);
  }
  purchaseOrderDownPaymentsForm.value.splice(index, 1);
  Object.keys(purchaseOrderForm.errors).forEach((key) => {
    if (key.startsWith('down_payments.')) {
      purchaseOrderForm.forgetError(key as any);
    }
  });
};

const addRefundedDownPayment = () => {
  purchaseOrderRefundedDownPaymentsForm.value.push({
    id: null,
    code: '_AUTO_',
    date: '_AUTO_',
    cash_account_id: '',
    amount: 0,
    remarks: '',
  });
};

const removeRefundedDownPayment = (index: number) => {
  const refundedDownPayment = purchaseOrderRefundedDownPaymentsForm.value[index];
  if (refundedDownPayment?.id) {
    purchaseOrderForm.delete_refunded_down_payment_ids.push(refundedDownPayment.id);
  }
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

const getItemsGrandTotalPreview = () =>
  purchaseOrderItemsForm.value.reduce((total, item) => total + getItemSubtotalAfterDiscountPreview(item), 0);

const getPurchaseOrderGlobalDiscountPreview = () => {
  let totalDiscount = 0;

  purchaseOrderGlobalDiscountsForm.value.forEach((discount) => {
    const discountValue = Math.max(Number(discount.discount_value || 0), 0);
    const currentTotal = Math.max(getItemsGrandTotalPreview() - totalDiscount, 0);
    const appliedDiscount = discount.discount_type === 'PERCENTAGE'
      ? (currentTotal * discountValue) / 100
      : discountValue;

    totalDiscount += Math.min(Math.max(appliedDiscount, 0), currentTotal);
  });

  return totalDiscount;
};

const getItemGlobalDiscountPreview = (item: PurchaseOrderItemFormItem, itemIndex: number) => {
  const totalBeforeGlobalDiscount = getItemsGrandTotalPreview();
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

const getItemTotalBeforeVatPreview = (item: PurchaseOrderItemFormItem, itemIndex: number) => {
  const subtotalAfterGlobalDiscount = Math.max(
    getItemSubtotalAfterDiscountPreview(item) - getItemGlobalDiscountPreview(item, itemIndex),
    0,
  );
  const vatBaseFactor = Number(item.vat_base_denominator || 0) > 0
    ? Number(item.vat_base_numerator || 0) / Number(item.vat_base_denominator || 1)
    : 0;
  const vatMultiplier = 1 + (vatBaseFactor * Number(item.vat_rate || 0));

  if (item.product_unit_is_price_include_vat && vatMultiplier > 0) {
    return subtotalAfterGlobalDiscount / vatMultiplier;
  }

  return subtotalAfterGlobalDiscount;
};

const getItemVatPreview = (item: PurchaseOrderItemFormItem, itemIndex: number) => {
  const vatBaseFactor = Number(item.vat_base_denominator || 0) > 0
    ? Number(item.vat_base_numerator || 0) / Number(item.vat_base_denominator || 1)
    : 0;
  const vatBase = getItemTotalBeforeVatPreview(item, itemIndex) * vatBaseFactor;

  return vatBase * Number(item.vat_rate || 0);
};

const getItemGrandTotalPreview = (item: PurchaseOrderItemFormItem) => {
  const itemIndex = purchaseOrderItemsForm.value.indexOf(item);

  if (itemIndex < 0) {
    return 0;
  }

  return getItemTotalBeforeVatPreview(item, itemIndex) + getItemVatPreview(item, itemIndex);
};

const getGlobalDiscountedGrandTotalPreview = () =>
  purchaseOrderItemsForm.value.reduce((total, item) => total + getItemGrandTotalPreview(item), 0);

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

  const cleanedItems: PurchaseOrderItemNestedUpdateRequest[] = purchaseOrderItemsForm.value.map((item) => ({
    id: item.id,
    qty: item.qty,
    product_unit_id: item.product_unit_id,
    product_unit_conversion_value: item.product_unit_conversion_value,
    product_unit_price: item.product_unit_price,
    delete_product_unit_price_discount_ids: item.delete_product_unit_price_discount_ids,
    product_unit_price_discounts: item.product_unit_price_discounts.map((discount: PurchaseOrderItemDiscountNestedUpdateRequest) => ({
      id: discount.id,
      sequence: discount.sequence,
      discount_type: discount.discount_type,
      discount_value: discount.discount_value,
    })),
    delete_subtotal_discount_ids: item.delete_subtotal_discount_ids,
    subtotal_discounts: item.subtotal_discounts.map((discount: PurchaseOrderItemDiscountNestedUpdateRequest) => ({
      id: discount.id,
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
    id: discount.id,
    sequence: discount.sequence,
    discount_type: discount.discount_type,
    discount_value: discount.discount_value,
  })) as any;
  purchaseOrderForm.down_payments = purchaseOrderDownPaymentsForm.value.map((downPayment) => ({
    id: downPayment.id,
    code: downPayment.code,
    date: downPayment.date,
    cash_account_id: downPayment.cash_account_id,
    amount: downPayment.amount,
    remarks: downPayment.remarks,
  })) as any;
  purchaseOrderForm.refunded_down_payments = purchaseOrderRefundedDownPaymentsForm.value.map((refundedDownPayment) => ({
    id: refundedDownPayment.id,
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
    showNotification(t('views.purchase_order.alert.update.title'), t('views.purchase_order.alert.update.message'));
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
      <template #card-items-0>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.company.code }}
                <br />
                {{ selectedUserLocation.company.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="purchaseOrderForm.company_id" />
            </div>
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

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
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
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': purchaseOrderForm.invalid('date') }">
                {{ t('views.purchase_order.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto v-model="purchaseOrderForm.date"
                :class="{ 'border-danger': purchaseOrderForm.invalid('date') }"
                :placeholder="t('views.purchase_order.fields.date')" @change="purchaseOrderForm.validate('date')" />
              <FormErrorMessages :messages="purchaseOrderForm.errors.date" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': purchaseOrderForm.invalid('due_days') }">
                {{ t('views.purchase_order.fields.due_days') }}
              </FormLabel>
              <FormInput v-model="purchaseOrderForm.due_days" type="number" min="0"
                :class="{ 'border-danger': purchaseOrderForm.invalid('due_days') }"
                @change="purchaseOrderForm.validate('due_days')" />
              <FormErrorMessages :messages="purchaseOrderForm.errors.due_days" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': purchaseOrderForm.invalid('supplier_id') }">
                {{ t('views.purchase_order.fields.supplier_id') }}
              </FormLabel>
              <FormSelectSearch v-model="purchaseOrderForm.supplier_id" v-model:search="supplierSearch"
                :options="supplierOptions" :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': purchaseOrderForm.invalid('supplier_id') }"
                @change="purchaseOrderForm.validate('supplier_id')" @search="loadSupplierDDL" @clear="clearSupplier" />
              <FormErrorMessages :messages="purchaseOrderForm.errors.supplier_id" />
            </div>
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

      <template #card-items-2>
        <div class="p-5 space-y-4">
          <div class="font-medium">{{ t('views.purchase_order.field_groups.items') }}</div>

          <FormErrorMessages :messages="purchaseOrderForm.errors.items" />

          <div v-if="purchaseOrderItemsForm.length === 0" class="text-slate-500 text-sm">
            {{ t('components.data-list.data_not_found') }}
          </div>

          <div v-else>
            <div v-for="(item, index) in purchaseOrderItemsForm" :key="`${item.id ?? item.product_unit_id}-${index}`"
              class="mt-3 border-t border-slate-200/60 pt-5 first:mt-0 first:border-t-0 first:pt-0 dark:border-darkmode-400">
              <!-- item content: stacked mobile layout -->
              <div v-if="currentItemLayout === 'sm'" class="grid grid-cols-12 gap-4 gap-y-3">
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
                <div class="col-span-12">
                  <div class="grid grid-cols-2 gap-4">
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
                    <div>
                      <FormLabel>
                        {{ t('views.product.table.cols.unit') }}
                      </FormLabel>
                      <FormInput :model-value="item.product_unit_unit_name || '-'" readonly />
                    </div>
                  </div>
                  <div v-if="Number(item.product_unit_conversion_value || 1) > 1" class="mt-1 text-right">
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                      {{ `${t('views.product.fields.conversion_value')} ${item.product_unit_conversion_value}
                      ${item.product_unit_base_unit_name || ''}`.trim() }}
                    </div>
                  </div>
                </div>
                <div class="col-span-12">
                  <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.product_unit_price`) }">
                    {{ t('views.purchase_order.fields.product_unit_price') }}
                  </FormLabel>
                  <FormInputCurrency v-model="item.product_unit_price" :allow-negative="false"
                    :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.product_unit_price`) }"
                    @change="validatePurchaseOrderField(`items.${index}.product_unit_price`)" />
                  <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.product_unit_price`)" />
                </div>
                <div class="col-span-12">
                  <FormLabel>{{ t('views.purchase_order.fields.item_grand_total') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency :model-value="getItemGrandTotalPreview(item)" readonly />
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
              <div v-else-if="currentItemLayout === 'md'" class="grid grid-cols-12 gap-4 gap-y-3">
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
                <div class="col-span-12 md:col-span-4">
                  <div class="grid grid-cols-2 gap-4">
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
                    <div>
                      <FormLabel>
                        {{ t('views.product.table.cols.unit') }}
                      </FormLabel>
                      <FormInput :model-value="item.product_unit_unit_name || '-'" readonly />
                    </div>
                  </div>
                  <div v-if="Number(item.product_unit_conversion_value || 1) > 1" class="mt-1 text-right">
                    <div class="text-sm text-slate-500 dark:text-slate-400">
                      {{ `${t('views.product.fields.conversion_value')} ${item.product_unit_conversion_value}
                      ${item.product_unit_base_unit_name || ''}`.trim() }}
                    </div>
                  </div>
                </div>
                <div class="col-span-12 md:col-span-4">
                  <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.product_unit_price`) }">
                    {{ t('views.purchase_order.fields.product_unit_price') }}
                  </FormLabel>
                  <FormInputCurrency v-model="item.product_unit_price" :allow-negative="false"
                    :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.product_unit_price`) }"
                    @change="validatePurchaseOrderField(`items.${index}.product_unit_price`)" />
                  <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.product_unit_price`)" />
                </div>
                <div class="col-span-12 md:col-span-4">
                  <FormLabel>{{ t('views.purchase_order.fields.item_grand_total') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency :model-value="getItemGrandTotalPreview(item)" readonly />
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
              <div v-else class="grid grid-cols-12 gap-4 gap-y-3">
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
                <div class="col-span-12 lg:col-span-2">
                  <div class="grid grid-cols-1 gap-4 lg:grid-cols-2">
                    <div>
                      <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.qty`) }">
                        {{ t('views.purchase_order.fields.qty') }}
                      </FormLabel>
                      <FormInputCurrency :id="`purchase-order-item-qty-${index}`" v-model="item.qty"
                        :allow-negative="false"
                        :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.qty`) }"
                        @change="validatePurchaseOrderField(`items.${index}.qty`)" />
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
                      {{ `${t('views.product.fields.conversion_value')} ${item.product_unit_conversion_value}
                      ${item.product_unit_base_unit_name || ''}`.trim() }}
                    </div>
                  </div>
                  <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.qty`)" />
                </div>
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
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.purchase_order.fields.item_grand_total') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="flex-1 min-w-0">
                      <FormInputCurrency :model-value="getItemGrandTotalPreview(item)" readonly />
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

              <div v-if="currentItemLayout !== 'lg' && purchaseOrderItemDiscountsExpanded[index]" class="mt-4 space-y-4">
                <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-5">
                  <div class="font-medium text-sm">{{ t('views.purchase_order.fields.item_price_breakdown') }}</div>
                  <div class="space-y-3">
                    <div class="flex justify-between items-center">
                      <div class="font-medium text-sm">{{ t('views.purchase_order.fields.product_unit_price_discounts') }}</div>
                      <Button type="button" variant="outline-primary" @click="addItemPriceDiscount(index)">
                        <Lucide icon="Plus" class="w-4 h-4 mr-1" />
                        {{ t('components.buttons.create_new') }}
                      </Button>
                    </div>
                    <div v-if="item.product_unit_price_discounts.length === 0" class="text-slate-500 text-sm">
                      {{ t('views.purchase_order.fields.product_unit_price_discounts_empty') }}
                    </div>
                    <div v-else class="space-y-3">
                      <div v-for="(discount, discountIndex) in item.product_unit_price_discounts"
                        :key="`${index}-price-${discount.id ?? discountIndex}`" class="grid grid-cols-12 gap-4 gap-y-3">
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
                  <div class="space-y-3">
                    <div class="flex justify-between items-center">
                      <div class="font-medium text-sm">{{ t('views.purchase_order.fields.subtotal_discounts') }}</div>
                      <Button type="button" variant="outline-primary" @click="addItemSubtotalDiscount(index)">
                        <Lucide icon="Plus" class="w-4 h-4 mr-1" />
                        {{ t('components.buttons.create_new') }}
                      </Button>
                    </div>
                    <div v-if="item.subtotal_discounts.length === 0" class="text-slate-500 text-sm">
                      {{ t('views.purchase_order.fields.subtotal_discounts_empty') }}
                    </div>
                    <div v-else class="space-y-3">
                      <div v-for="(discount, discountIndex) in item.subtotal_discounts"
                        :key="`${index}-subtotal-${discount.id ?? discountIndex}`" class="grid grid-cols-12 gap-4 gap-y-3">
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
                        @change="validatePurchaseOrderField(`items.${index}.vat_profile_id`)" @search="loadVatProfileDDL"
                        @clear="clearVatProfile(index)" />
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
                      <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`items.${index}.vat_base_numerator`) }">
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
                      <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.vat_base_denominator`)" />
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

              <div v-else-if="purchaseOrderItemDiscountsExpanded[index]" class="mt-4 grid grid-cols-12 gap-4">
                <div class="col-span-12 lg:col-span-7">
                  <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-5">
                    <div class="font-medium text-sm">{{ t('views.purchase_order.fields.item_price_breakdown') }}</div>
                    <div class="space-y-3">
                      <div class="flex justify-between items-center">
                        <div class="font-medium text-sm">{{ t('views.purchase_order.fields.product_unit_price_discounts') }}</div>
                        <Button type="button" variant="outline-primary" @click="addItemPriceDiscount(index)">
                          <Lucide icon="Plus" class="w-4 h-4 mr-1" />
                          {{ t('components.buttons.create_new') }}
                        </Button>
                      </div>
                      <div v-if="item.product_unit_price_discounts.length === 0" class="text-slate-500 text-sm">
                        {{ t('views.purchase_order.fields.product_unit_price_discounts_empty') }}
                      </div>
                      <div v-else class="space-y-3">
                        <div v-for="(discount, discountIndex) in item.product_unit_price_discounts"
                          :key="`${index}-price-lg-${discount.id ?? discountIndex}`" class="grid grid-cols-12 gap-4 gap-y-3">
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
                    <div class="space-y-3">
                      <div class="flex justify-between items-center">
                        <div class="font-medium text-sm">{{ t('views.purchase_order.fields.subtotal_discounts') }}</div>
                        <Button type="button" variant="outline-primary" @click="addItemSubtotalDiscount(index)">
                          <Lucide icon="Plus" class="w-4 h-4 mr-1" />
                          {{ t('components.buttons.create_new') }}
                        </Button>
                      </div>
                      <div v-if="item.subtotal_discounts.length === 0" class="text-slate-500 text-sm">
                        {{ t('views.purchase_order.fields.subtotal_discounts_empty') }}
                      </div>
                      <div v-else class="space-y-3">
                        <div v-for="(discount, discountIndex) in item.subtotal_discounts"
                          :key="`${index}-subtotal-lg-${discount.id ?? discountIndex}`" class="grid grid-cols-12 gap-4 gap-y-3">
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
                <div class="col-span-12 lg:col-span-5">
                  <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-4">
                    <div class="font-medium text-sm">{{ t('views.purchase_order.fields.item_additional_details') }}</div>
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel class="flex min-h-[40px] items-start">{{ t('views.purchase_order.fields.product_unit_is_price_include_vat') }}</FormLabel>
                        <FormSwitch>
                          <FormSwitch.Input v-model="item.product_unit_is_price_include_vat" type="checkbox" />
                        </FormSwitch>
                      </div>
                      <div class="col-span-12 md:col-span-8">
                        <FormLabel :class="['flex min-h-[40px] items-start', { 'text-danger': invalidPurchaseOrderField(`items.${index}.vat_profile_id`) }]">
                          {{ t('views.purchase_order.fields.vat_profile_id') }}
                        </FormLabel>
                        <FormSelectSearch v-model="item.vat_profile_id" v-model:search="vatProfileSearch"
                          :options="vatProfileOptions" :placeholder="t('components.dropdown.placeholder')"
                          :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.vat_profile_id`) }"
                          @change="validatePurchaseOrderField(`items.${index}.vat_profile_id`)" @search="loadVatProfileDDL"
                          @clear="clearVatProfile(index)" />
                        <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.vat_profile_id`)" />
                      </div>
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel :class="['flex min-h-[40px] items-start', { 'text-danger': invalidPurchaseOrderField(`items.${index}.vat_rate`) }]">
                          {{ t('views.purchase_order.fields.vat_rate') }}
                        </FormLabel>
                        <FormInputCurrency v-model="item.vat_rate" :allow-negative="false"
                          :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.vat_rate`) }"
                          @change="validatePurchaseOrderField(`items.${index}.vat_rate`)" />
                        <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.vat_rate`)" />
                      </div>
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel :class="['flex min-h-[40px] items-start', { 'text-danger': invalidPurchaseOrderField(`items.${index}.vat_base_numerator`) }]">
                          {{ t('views.purchase_order.fields.vat_base_numerator') }}
                        </FormLabel>
                        <FormInput v-model="item.vat_base_numerator" type="number" min="1"
                          :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.vat_base_numerator`) }"
                          @change="validatePurchaseOrderField(`items.${index}.vat_base_numerator`)" />
                        <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.vat_base_numerator`)" />
                      </div>
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel :class="['flex min-h-[40px] items-start', { 'text-danger': invalidPurchaseOrderField(`items.${index}.vat_base_denominator`) }]">
                          {{ t('views.purchase_order.fields.vat_base_denominator') }}
                        </FormLabel>
                        <FormInput v-model="item.vat_base_denominator" type="number" min="1"
                          :class="{ 'border-danger': invalidPurchaseOrderField(`items.${index}.vat_base_denominator`) }"
                          @change="validatePurchaseOrderField(`items.${index}.vat_base_denominator`)" />
                        <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`items.${index}.vat_base_denominator`)" />
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

      <template #card-items-3>
        <div class="p-5 space-y-4">
          <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 p-4 space-y-4">
            <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
              <div class="col-span-12 lg:col-span-9"></div>
              <div class="col-span-12 lg:col-span-3">
                <FormLabel>{{ t('views.purchase_order.fields.items_grand_total_after_discount') }}</FormLabel>
                <FormInputCurrency :model-value="getItemsGrandTotalPreview()" readonly />
              </div>
            </div>

            <div class="space-y-4">
              <div v-if="isGlobalDiscountEditorExpanded" class="space-y-4">
                <FormErrorMessages :messages="purchaseOrderForm.errors.global_discounts" />
                <div v-if="purchaseOrderGlobalDiscountsForm.length === 0" class="text-right text-slate-500 text-sm">
                  {{ t('components.data-list.data_not_found') }}
                </div>
                <div v-else class="space-y-3">
                  <div v-for="(discount, index) in purchaseOrderGlobalDiscountsForm"
                    :key="`global-discount-${discount.id ?? index}`" class="grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 md:col-span-4 lg:col-span-6"></div>
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
                <div class="flex justify-end">
                  <Button type="button" variant="outline-primary" @click="addGlobalDiscount">
                    <Lucide icon="Plus" class="w-4 h-4 mr-1" />
                    {{ t('components.buttons.create_new') }}
                  </Button>
                </div>
              </div>

              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
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
                      <FormInputCurrency
                        :model-value="getPurchaseOrderGlobalDiscountPreview()"
                        readonly />
                    </div>
                  </div>
                </div>
              </div>

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

              <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.purchase_order.fields.grand_total') }}</FormLabel>
                  <FormInputCurrency :model-value="getPurchaseOrderGrandTotalPreview()" readonly />
                </div>
              </div>

              <div class="border-t border-slate-200/60 dark:border-darkmode-400 pt-4 space-y-4">
                <div v-if="isDownPaymentEditorExpanded" class="space-y-4">
                  <FormErrorMessages :messages="purchaseOrderForm.errors.down_payments" />
                  <div v-if="purchaseOrderDownPaymentsForm.length === 0" class="text-right text-slate-500 text-sm">
                    {{ t('components.data-list.data_not_found') }}
                  </div>
                  <div v-else class="space-y-4">
                    <div v-for="(downPayment, index) in purchaseOrderDownPaymentsForm"
                      :key="`down-payment-${downPayment.id ?? index}`" class="space-y-3">
                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 md:col-span-6 lg:col-span-2"></div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-2">
                          <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`down_payments.${index}.code`) }">
                            {{ t('views.purchase_order.fields.code') }}
                          </FormLabel>
                          <FormInputCode v-model="downPayment.code"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`down_payments.${index}.code`) }"
                            :placeholder="t('views.purchase_order.fields.code')"
                            @set-auto="setDownPaymentCode(index)"
                            @change="validatePurchaseOrderField(`down_payments.${index}.code`)" />
                          <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`down_payments.${index}.code`)" />
                        </div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-4">
                          <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`down_payments.${index}.date`) }">
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
                          <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`down_payments.${index}.amount`) }">
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
                        <div class="col-span-12 lg:col-span-2"></div>
                        <div class="col-span-12 lg:col-span-10">
                          <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`down_payments.${index}.remarks`) }">
                            {{ t('views.purchase_order.fields.remarks') }}
                          </FormLabel>
                          <FormTextarea v-model="downPayment.remarks"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`down_payments.${index}.remarks`) }"
                            @change="validatePurchaseOrderField(`down_payments.${index}.remarks`)" />
                          <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`down_payments.${index}.remarks`)" />
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="flex justify-end">
                    <Button type="button" variant="outline-primary" @click="addDownPayment">
                      <Lucide icon="Plus" class="w-4 h-4 mr-1" />
                      {{ t('components.buttons.create_new') }}
                    </Button>
                  </div>
                </div>

                <div class="grid grid-cols-12 gap-4 gap-y-3 items-end">
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
                  <div v-if="purchaseOrderRefundedDownPaymentsForm.length === 0" class="text-right text-slate-500 text-sm">
                    {{ t('components.data-list.data_not_found') }}
                  </div>
                  <div v-else class="space-y-4">
                    <div
                      v-for="(refundedDownPayment, index) in purchaseOrderRefundedDownPaymentsForm"
                      :key="`refunded-down-payment-${refundedDownPayment.id ?? index}`"
                      class="space-y-3">
                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 md:col-span-6 lg:col-span-2"></div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-2">
                          <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.code`) }">
                            {{ t('views.purchase_order.fields.code') }}
                          </FormLabel>
                          <FormInputCode
                            v-model="refundedDownPayment.code"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.code`) }"
                            :placeholder="t('views.purchase_order.fields.code')"
                            @set-auto="setRefundedDownPaymentCode(index)"
                            @change="validatePurchaseOrderField(`refunded_down_payments.${index}.code`)" />
                          <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`refunded_down_payments.${index}.code`)" />
                        </div>
                        <div class="col-span-12 md:col-span-6 lg:col-span-4">
                          <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.date`) }">
                            {{ t('views.purchase_order.fields.date') }}
                          </FormLabel>
                          <FormInputDateTimeAuto
                            v-model="refundedDownPayment.date"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.date`) }"
                            :placeholder="t('views.purchase_order.fields.date')"
                            @change="validatePurchaseOrderField(`refunded_down_payments.${index}.date`)" />
                          <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`refunded_down_payments.${index}.date`)" />
                        </div>
                        <div class="col-span-12 md:col-span-8 lg:col-span-2">
                          <FormLabel
                            :class="{ 'text-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.cash_account_id`) }">
                            {{ t('views.purchase_order.fields.cash_account_id') }}
                          </FormLabel>
                          <FormSelectSearch
                            v-model="refundedDownPayment.cash_account_id"
                            v-model:search="cashAccountSearch"
                            :options="cashAccountOptions"
                            :placeholder="t('components.dropdown.placeholder')"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.cash_account_id`) }"
                            @change="validatePurchaseOrderField(`refunded_down_payments.${index}.cash_account_id`)"
                            @search="loadCashAccountDDL"
                            @clear="clearRefundedCashAccount(index)" />
                          <FormErrorMessages
                            :messages="getPurchaseOrderFieldErrors(`refunded_down_payments.${index}.cash_account_id`)" />
                        </div>
                        <div class="col-span-12 md:col-span-4 lg:col-span-2">
                          <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.amount`) }">
                            {{ t('views.purchase_order.fields.amount') }}
                          </FormLabel>
                          <div class="flex items-start gap-2">
                            <div class="flex-1 min-w-0">
                              <FormInputCurrency
                                v-model="refundedDownPayment.amount"
                                :allow-negative="false"
                                :class="{ 'border-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.amount`) }"
                                @change="validatePurchaseOrderField(`refunded_down_payments.${index}.amount`)" />
                            </div>
                            <div class="shrink-0">
                              <Button
                                type="button"
                                variant="outline-secondary"
                                class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                                @click="removeRefundedDownPayment(index)">
                                <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                              </Button>
                            </div>
                          </div>
                          <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`refunded_down_payments.${index}.amount`)" />
                        </div>
                      </div>
                      <div class="grid grid-cols-12 gap-4 gap-y-3">
                        <div class="col-span-12 lg:col-span-2"></div>
                        <div class="col-span-12 lg:col-span-10">
                          <FormLabel :class="{ 'text-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.remarks`) }">
                            {{ t('views.purchase_order.fields.remarks') }}
                          </FormLabel>
                          <FormTextarea
                            v-model="refundedDownPayment.remarks"
                            :class="{ 'border-danger': invalidPurchaseOrderField(`refunded_down_payments.${index}.remarks`) }"
                            @change="validatePurchaseOrderField(`refunded_down_payments.${index}.remarks`)" />
                          <FormErrorMessages :messages="getPurchaseOrderFieldErrors(`refunded_down_payments.${index}.remarks`)" />
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
              </div>
            </div>
          </div>
        </div>
      </template>

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

  <ProductUnitPickerDialog size="xl" panel-class="max-w-5xl" :open="showProductUnitModal"
    :title="t('views.purchase_order.fields.product_unit_id')" :search-text="productSearchText"
    :is-searching="isSearchingProductUnit" :options="productUnitOptions" :columns="productUnitDialogColumns"
    @update:search-text="productSearchText = $event" @search="searchProductUnits"
    @select="selectProductUnit($event as ProductUnitOption)" @close="showProductUnitModal = false"
    @after-leave="handleProductUnitModalAfterLeave" />
</template>
