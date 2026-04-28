<script setup lang="ts">
import { computed, nextTick, onMounted, onUnmounted, ref, watch } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import type { TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
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
import ProductUnitPickerDialog from '@/components/Product/ProductUnitPickerDialog.vue';
import CashAccountService from '@/services/CashAccountService';
import ProductService from '@/services/ProductService';
import PurchaseAdditionalCostCategoryService from '@/services/PurchaseAdditionalCostCategoryService';
import PurchaseService from '@/services/PurchaseService';
import SupplierService from '@/services/SupplierService';
import VatProfileService from '@/services/VatProfileService';
import WarehouseService from '@/services/WarehouseService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { CardState } from '@/types/enums/CardState';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { NotificationData } from '@/types/models/NotificationData';
import type { Purchase } from '@/types/models/Purchase';
import type {
  PurchaseAdditionalCostNestedUpdateRequest,
  PurchaseDirectItemNestedUpdateRequest,
  PurchaseGlobalDiscountNestedUpdateRequest,
} from '@/types/services/purchase/PurchaseRequest';
import { convertErrorTypeToAlertListType, formatCurrency, formatDate } from '@/utils/helper';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';

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
  is_use_serial_number: boolean;
};

type VatProfileOption = {
  code: string;
  name: string;
  vat_rate: number;
  vat_base_numerator: number;
  vat_base_denominator: number;
};

type PurchaseDiscountForm = {
  id?: string | null;
  sequence: number;
  discount_type: string;
  discount_value: number;
};

type PurchaseSerialForm = {
  id?: string | null;
  serial: string;
};

type PurchaseItemForm = {
  id?: string | null;
  purchase_order_item_id: string | null;
  qty: number;
  product_unit_id: string;
  product_unit_conversion_value: number;
  product_unit_price: number;
  product_unit_is_price_include_vat: boolean;
  delete_product_unit_price_discount_ids?: string[];
  product_unit_price_discounts: PurchaseDiscountForm[];
  delete_subtotal_discount_ids?: string[];
  subtotal_discounts: PurchaseDiscountForm[];
  vat_profile_id: string | null;
  vat_rate: number;
  vat_base_numerator: number;
  vat_base_denominator: number;
  remarks: string | null;
  product_unit_product_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_unit_name?: string | null;
  product_unit_base_unit_name?: string | null;
  vat_profile_name?: string | null;
  is_use_serial_number?: boolean;
  serials: PurchaseSerialForm[];
};

type PurchaseAdditionalCostForm = PurchaseAdditionalCostNestedUpdateRequest;

const emits = defineEmits([
  'mode-state',
  'loading-state',
  'update-profile',
  'show-alertplaceholder',
  'show-notification',
]);

const { t } = useI18n();
const route = useRoute();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const purchaseService = new PurchaseService();
const supplierService = new SupplierService();
const vatProfileService = new VatProfileService();
const cashAccountService = new CashAccountService();
const warehouseService = new WarehouseService();
const purchaseAdditionalCostCategoryService = new PurchaseAdditionalCostCategoryService();
const productService = new ProductService();
const ulid = route.params.ulid.toString();
const purchaseForm = purchaseService.usePurchaseEditDirectForm(ulid);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);
const isDirectMode = computed(() => true);

const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'views.purchase.field_groups.company_info', state: CardState.Expanded },
  { title: 'views.purchase.field_groups.purchase_data', state: CardState.Expanded },
  { title: 'views.purchase.field_groups.items', state: CardState.Expanded },
  { title: 'views.purchase.field_groups.additional_costs', state: CardState.Expanded },
  { title: 'views.purchase.field_groups.summary', state: CardState.Expanded },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const supplierDDL = ref<Array<DropDownOption> | null>(null);
const supplierSearch = ref('');
const supplierOptions = computed(() =>
  (supplierDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const warehouseDDL = ref<Array<DropDownOption> | null>(null);
const warehouseSearch = ref('');
const warehouseOptions = computed(() =>
  (warehouseDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const cashAccountDDL = ref<Array<DropDownOption> | null>(null);
const cashAccountSearch = ref('');
const cashAccountOptions = computed(() =>
  (cashAccountDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const additionalCostCategoryDDL = ref<Array<DropDownOption> | null>(null);
const additionalCostCategorySearch = ref('');
const additionalCostCategoryOptions = computed(() =>
  (additionalCostCategoryDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const vatProfileDDL = ref<Array<VatProfileOption> | null>(null);
const vatProfileOptions = computed(() => vatProfileDDL.value ?? []);

const showProductUnitModal = ref(false);
const productSearchText = ref('');
const isSearchingProductUnit = ref(false);
const productUnitOptions = ref<Array<ProductUnitOption>>([]);

const discountTypeOptions = [
  { value: 'PERCENTAGE', label: 'Percentage' },
  { value: 'NOMINAL', label: 'Nominal' },
];

const productUnitDialogColumns = [
  {
    key: 'unit_name',
    label: t('views.product.table.cols.unit'),
  },
  {
    key: 'conversion_value',
    label: t('views.purchase.fields.product_unit_conversion_value'),
    align: 'right' as const,
    formatter: 'number' as const,
  },
  {
    key: 'price',
    label: t('views.purchase.fields.product_unit_price'),
    align: 'right' as const,
    formatter: 'currency' as const,
  },
];

const purchaseItemsForm = computed<PurchaseItemForm[]>(
  () => purchaseForm.items as PurchaseItemForm[],
);
const purchaseItemDetailsExpanded = ref<boolean[]>([]);
const viewportWidth = ref<number>(typeof window !== 'undefined' ? window.innerWidth : 1024);
const purchaseGlobalDiscountsForm = computed<PurchaseDiscountForm[]>(
  () => purchaseForm.global_discounts as PurchaseDiscountForm[],
);
const purchaseAdditionalCostsForm = computed<PurchaseAdditionalCostForm[]>(
  () => purchaseForm.additional_costs as PurchaseAdditionalCostForm[],
);

const currentItemLayout = computed<'sm' | 'md' | 'lg'>(() => {
  if (viewportWidth.value >= 1024) return 'lg';
  if (viewportWidth.value >= 768) return 'md';
  return 'sm';
});

const invalidField = (field: string) => purchaseForm.invalid(field as any);
const validateField = (field: string) => purchaseForm.validate(field as any);
const getFieldErrors = (field: string) =>
  (purchaseForm.errors as Record<string, string | undefined>)[field];

const handleExpandCard = (index: number) => {
  cards.value[index].state =
    cards.value[index].state === CardState.Collapsed ? CardState.Expanded : CardState.Collapsed;
};

const getPurchaseItemShouldStartExpanded = (item: PurchaseItemForm) =>
  item.product_unit_price_discounts.length > 0 ||
  item.subtotal_discounts.length > 0 ||
  (isDirectMode.value && item.serials.length > 0);

const syncPurchaseItemDetailsExpanded = () => {
  purchaseItemDetailsExpanded.value = purchaseItemsForm.value.map(
    (item, index) => purchaseItemDetailsExpanded.value[index] ?? getPurchaseItemShouldStartExpanded(item),
  );
};

const syncViewportWidth = () => {
  viewportWidth.value = typeof window !== 'undefined' ? window.innerWidth : 1024;
};

const appendDropDownOption = (
  target: typeof supplierDDL | typeof warehouseDDL | typeof cashAccountDDL | typeof additionalCostCategoryDDL,
  option?: DropDownOption | null,
) => {
  if (!option?.code) return;

  const current = target.value ?? [];
  if (current.some((item) => item.code === option.code)) return;
  target.value = [...current, option];
};

const appendVatProfileOption = (option: VatProfileOption) => {
  const current = vatProfileDDL.value ?? [];
  if (current.some((item) => item.code === option.code)) return;
  vatProfileDDL.value = [...current, option];
};

const normalizeVatBaseFactor = (value: unknown) => Math.max(1, Number(value ?? 1) || 0);
const getBaseUnitName = (product: any) =>
  (product?.product_units ?? []).find((unit: any) => Number(unit.conversion_value ?? 1) === 1)?.unit?.name ?? '';

const syncDerivedFields = () => {
  purchaseForm.additional_cost = getAdditionalCostsTotalPreview();
};

watch(
  purchaseAdditionalCostsForm,
  () => {
    syncDerivedFields();
  },
  { deep: true },
);

watch(
  purchaseItemsForm,
  () => {
    syncPurchaseItemDetailsExpanded();
  },
  { deep: true },
);

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
    purchaseForm.setData({
      company_id: selectedUserLocation.value.company.id,
      branch_id: selectedUserLocation.value.branch.id,
    });

    await Promise.all([
      loadSupplierDDL(),
      loadVatProfileDDL(),
      loadCashAccountDDL(),
      loadAdditionalCostCategoryDDL(),
      ...(isDirectMode.value ? [loadWarehouseDDL()] : []),
    ]);

    await loadData();
  } finally {
    emits('loading-state', false);
  }

  syncDerivedFields();
  syncPurchaseItemDetailsExpanded();
  window.addEventListener('resize', syncViewportWidth);
});

onUnmounted(() => {
  window.removeEventListener('resize', syncViewportWidth);
});

const loadData = async () => {
  const result = await purchaseService.read(ulid);

  if (!result.success || !result.data) {
    router.push({ name: 'side-menu-purchase-list' });
    return;
  }

  hydrateForm(result.data);
};

const hydrateForm = (purchase: Purchase) => {
  appendDropDownOption(supplierDDL, purchase.supplier
    ? {
      code: purchase.supplier.id,
      name: purchase.supplier.name,
    }
    : null);
  appendDropDownOption(warehouseDDL, purchase.direct_receipt?.warehouse
    ? {
      code: purchase.direct_receipt.warehouse.id,
      name: purchase.direct_receipt.warehouse.name,
    }
    : null);

  (purchase.additional_costs ?? []).forEach((additionalCost) => {
    appendDropDownOption(additionalCostCategoryDDL, additionalCost.category
      ? {
        code: additionalCost.category.id,
        name: additionalCost.category.name,
      }
      : null);
    appendDropDownOption(cashAccountDDL, additionalCost.paid_immediately_cash_account
      ? {
        code: additionalCost.paid_immediately_cash_account.id,
        name: additionalCost.paid_immediately_cash_account.name,
      }
      : null);
  });

  purchaseForm.setData({
    company_id: purchase.company?.id ?? selectedUserLocation.value?.company.id ?? '',
    branch_id: purchase.branch?.id ?? selectedUserLocation.value?.branch.id ?? '',
    code: purchase.code,
    date: formatDate(purchase.date, 'YYYY-MM-DD HH:mm:ss'),
    due_days: Number(purchase.due_days ?? 0),
    supplier_id: purchase.supplier?.id ?? null,
    purchase_order_id: purchase.purchase_order?.id ?? null,
    direct_receipt_warehouse_id: purchase.direct_receipt?.warehouse?.id ?? '',
    tax_invoice_number: purchase.tax_invoice_number ?? null,
    tax_invoice_vat_base: Number(purchase.tax_invoice_vat_base ?? 0),
    tax_invoice_vat: Number(purchase.tax_invoice_vat ?? 0),
    remarks: purchase.remarks ?? '',
    is_posted: Boolean(purchase.is_posted),
    additional_cost: Number(purchase.additional_cost ?? 0),
    rounding: Number(purchase.rounding ?? 0),
    delete_item_ids: [],
    items: (purchase.items ?? []).map((item) => {
      const serials = isDirectMode.value
        ? (purchase.direct_receipt?.items ?? [])
          .find((receiptItem) => receiptItem.purchase_item?.id === item.id)
          ?.serials
          ?.map((serial) => ({
            id: serial.id ?? null,
            serial: serial.serial,
          })) ?? []
        : [];

      if (item.vat_profile?.id) {
        appendVatProfileOption({
          code: item.vat_profile.id,
          name: item.vat_profile.name,
          vat_rate: Number(item.vat_profile.vat_rate ?? item.vat_rate ?? 0),
          vat_base_numerator: normalizeVatBaseFactor(
            item.vat_profile.vat_base_numerator ?? item.vat_base_numerator ?? 1,
          ),
          vat_base_denominator: normalizeVatBaseFactor(
            item.vat_profile.vat_base_denominator ?? item.vat_base_denominator ?? 1,
          ),
        });
      }

      return {
        id: item.id ?? null,
        purchase_order_item_id: item.purchase_order_item?.id ?? null,
        qty: Number(item.qty ?? 0),
        product_unit_id: item.product_unit?.id ?? '',
        product_unit_product_code: item.product_unit?.code ?? '',
        product_unit_product_name: item.product_unit?.product?.name ?? '',
        product_unit_unit_name: item.product_unit?.unit?.name ?? '',
        product_unit_base_unit_name: getBaseUnitName(item.product_unit?.product),
        product_unit_conversion_value: Number(item.product_unit_conversion_value ?? item.product_unit?.conversion_value ?? 1),
        product_unit_price: Number(item.product_unit_price ?? 0),
        product_unit_is_price_include_vat: Boolean(item.product_unit_is_price_include_vat),
        delete_product_unit_price_discount_ids: [],
        product_unit_price_discounts: (item.product_unit_price_discounts ?? []).map((discount) => ({
          id: discount.id ?? null,
          sequence: Number(discount.sequence ?? 0),
          discount_type: discount.discount_type ?? 'PERCENTAGE',
          discount_value: Number(discount.discount_value ?? 0),
        })),
        delete_subtotal_discount_ids: [],
        subtotal_discounts: (item.subtotal_discounts ?? []).map((discount) => ({
          id: discount.id ?? null,
          sequence: Number(discount.sequence ?? 0),
          discount_type: discount.discount_type ?? 'PERCENTAGE',
          discount_value: Number(discount.discount_value ?? 0),
        })),
        vat_profile_id: item.vat_profile?.id ?? null,
        vat_profile_name: item.vat_profile?.name ?? null,
        vat_rate: Number(item.vat_rate ?? 0),
        vat_base_numerator: normalizeVatBaseFactor(item.vat_base_numerator ?? 1),
        vat_base_denominator: normalizeVatBaseFactor(item.vat_base_denominator ?? 1),
        remarks: item.remarks ?? '',
        is_use_serial_number: Boolean(item.product_unit?.product?.is_use_serial_number),
        serials,
      };
    }) as PurchaseDirectItemNestedUpdateRequest[],
    delete_global_discount_ids: [],
    global_discounts: (purchase.global_discounts ?? []).map((discount) => ({
      id: discount.id ?? null,
      sequence: Number(discount.sequence ?? 0),
      discount_type: discount.discount_type ?? 'PERCENTAGE',
      discount_value: Number(discount.discount_value ?? 0),
    })),
    delete_additional_cost_ids: [],
    additional_costs: (purchase.additional_costs ?? []).map((additionalCost) => ({
      id: additionalCost.id ?? null,
      purchase_additional_cost_category_id: additionalCost.category?.id ?? '',
      code: additionalCost.code ?? '_AUTO_',
      date: formatDate(additionalCost.date, 'YYYY-MM-DD HH:mm:ss'),
      due_days: Number(additionalCost.due_days ?? 0),
      paid_immediately_cash_account_id: additionalCost.paid_immediately_cash_account?.id ?? null,
      amount_paid_immediately: Number(additionalCost.amount_paid_immediately ?? 0),
      amount_payable: Number(additionalCost.amount_payable ?? 0),
      remarks: additionalCost.remarks ?? '',
    })),
  } as any);
  syncPurchaseItemDetailsExpanded();
};

const setCode = () => {
  purchaseForm.forgetError('code');
  purchaseForm.setData({
    code: purchaseForm.code === '_AUTO_' ? '' : '_AUTO_',
  });
};

const setAdditionalCostCode = (index: number) => {
  purchaseForm.forgetError(`additional_costs.${index}.code` as any);
  purchaseAdditionalCostsForm.value[index].code =
    purchaseAdditionalCostsForm.value[index].code === '_AUTO_' ? '' : '_AUTO_';
};

const loadSupplierDDL = async (search = supplierSearch.value) => {
  if (!selectedUserLocation.value) return;

  const result = await supplierService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: undefined,
    status: undefined,
    refresh: false,
    limit: 20,
  } as any);

  if (result.success && result.data) {
    supplierDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadWarehouseDDL = async (search = warehouseSearch.value) => {
  if (!selectedUserLocation.value) return;

  const result = await warehouseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    include_id: undefined,
    status: undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    warehouseDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadCashAccountDDL = async (search = cashAccountSearch.value) => {
  if (!selectedUserLocation.value) return;

  const result = await cashAccountService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    include_id: undefined,
    category_id: undefined,
    type: undefined,
    refresh: false,
    limit: 20,
  } as any);

  if (result.success && result.data) {
    cashAccountDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadAdditionalCostCategoryDDL = async (search = additionalCostCategorySearch.value) => {
  if (!selectedUserLocation.value) return;

  const result = await purchaseAdditionalCostCategoryService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    include_id: undefined,
    refresh: false,
    limit: 20,
  });

  if (result.success && result.data) {
    additionalCostCategoryDDL.value = result.data.data.map((item: any) => ({
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
    status: undefined,
    refresh: false,
    limit: 20,
  } as any);

  if (result.success && result.data) {
    vatProfileDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
      vat_rate: Number(item.vat_rate ?? 0),
      vat_base_numerator: normalizeVatBaseFactor(item.vat_base_numerator ?? 1),
      vat_base_denominator: normalizeVatBaseFactor(item.vat_base_denominator ?? 1),
    }));
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
  } as any);

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
        vat_base_numerator: normalizeVatBaseFactor(product.default_vat_profile?.vat_base_numerator ?? 1),
        vat_base_denominator: normalizeVatBaseFactor(product.default_vat_profile?.vat_base_denominator ?? 1),
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
  showProductUnitModal.value = true;
};

const selectProductUnit = (option: ProductUnitOption) => {
  if (option.vat_profile_id) {
    appendVatProfileOption({
      code: option.vat_profile_id,
      name: option.vat_profile_name ?? '-',
      vat_rate: option.vat_rate,
      vat_base_numerator: option.vat_base_numerator,
      vat_base_denominator: option.vat_base_denominator,
    });
  }

  purchaseItemsForm.value.push({
    purchase_order_item_id: null,
    qty: 1,
    product_unit_id: option.product_unit_id,
    product_unit_product_code: option.product_unit_code,
    product_unit_product_name: option.product_name,
    product_unit_unit_name: option.unit_name,
    product_unit_base_unit_name: option.base_unit_name,
    product_unit_conversion_value: option.conversion_value,
    product_unit_price: option.price,
    product_unit_is_price_include_vat: option.product_unit_is_price_include_vat,
    product_unit_price_discounts: [],
    subtotal_discounts: [],
    vat_profile_id: option.vat_profile_id,
    vat_profile_name: option.vat_profile_name,
    vat_rate: option.vat_rate,
    vat_base_numerator: normalizeVatBaseFactor(option.vat_base_numerator),
    vat_base_denominator: normalizeVatBaseFactor(option.vat_base_denominator),
    remarks: '',
    is_use_serial_number: option.is_use_serial_number,
    serials: [],
  });
  purchaseItemDetailsExpanded.value.push(false);

  showProductUnitModal.value = false;
  nextTick(() => {
    const index = purchaseItemsForm.value.length - 1;
    const el = document.getElementById(`purchase-item-qty-${index}`) as HTMLInputElement | null;
    el?.focus();
    el?.select();
  });
};

const removeItem = (index: number) => {
  const item = purchaseItemsForm.value[index];
  if (item?.id) {
    purchaseForm.delete_item_ids.push(item.id);
  }
  purchaseItemsForm.value.splice(index, 1);
  purchaseItemDetailsExpanded.value.splice(index, 1);
  Object.keys(purchaseForm.errors).forEach((key) => {
    if (key.startsWith('items.')) {
      purchaseForm.forgetError(key as any);
    }
  });
};

const resequence = (items: Array<{ sequence: number }>) => {
  items.forEach((item, index) => {
    item.sequence = index + 1;
  });
};

const addItemPriceDiscount = (index: number) => {
  purchaseItemDetailsExpanded.value[index] = true;
  purchaseItemsForm.value[index].product_unit_price_discounts.push({
    id: null,
    sequence: purchaseItemsForm.value[index].product_unit_price_discounts.length + 1,
    discount_type: 'PERCENTAGE',
    discount_value: 0,
  });
};

const removeItemPriceDiscount = (index: number, discountIndex: number) => {
  const item = purchaseItemsForm.value[index];
  const discount = item.product_unit_price_discounts[discountIndex];
  if (discount?.id) {
    item.delete_product_unit_price_discount_ids ??= [];
    item.delete_product_unit_price_discount_ids.push(discount.id);
  }
  item.product_unit_price_discounts.splice(discountIndex, 1);
  resequence(item.product_unit_price_discounts);
};

const addItemSubtotalDiscount = (index: number) => {
  purchaseItemDetailsExpanded.value[index] = true;
  purchaseItemsForm.value[index].subtotal_discounts.push({
    id: null,
    sequence: purchaseItemsForm.value[index].subtotal_discounts.length + 1,
    discount_type: 'PERCENTAGE',
    discount_value: 0,
  });
};

const removeItemSubtotalDiscount = (index: number, discountIndex: number) => {
  const item = purchaseItemsForm.value[index];
  const discount = item.subtotal_discounts[discountIndex];
  if (discount?.id) {
    item.delete_subtotal_discount_ids ??= [];
    item.delete_subtotal_discount_ids.push(discount.id);
  }
  item.subtotal_discounts.splice(discountIndex, 1);
  resequence(item.subtotal_discounts);
};

const addGlobalDiscount = () => {
  purchaseGlobalDiscountsForm.value.push({
    id: null,
    sequence: purchaseGlobalDiscountsForm.value.length + 1,
    discount_type: 'PERCENTAGE',
    discount_value: 0,
  });
};

const removeGlobalDiscount = (index: number) => {
  const discount = purchaseGlobalDiscountsForm.value[index];
  if (discount?.id) {
    purchaseForm.delete_global_discount_ids.push(discount.id);
  }
  purchaseGlobalDiscountsForm.value.splice(index, 1);
  resequence(purchaseGlobalDiscountsForm.value);
};

const addAdditionalCost = () => {
  purchaseAdditionalCostsForm.value.push({
    id: null,
    purchase_additional_cost_category_id: '',
    code: '_AUTO_',
    date: '_AUTO_',
    due_days: 0,
    paid_immediately_cash_account_id: null,
    amount_paid_immediately: 0,
    amount_payable: 0,
    remarks: '',
  });
};

const removeAdditionalCost = (index: number) => {
  const additionalCost = purchaseAdditionalCostsForm.value[index];
  if (additionalCost?.id) {
    purchaseForm.delete_additional_cost_ids.push(additionalCost.id);
  }
  purchaseAdditionalCostsForm.value.splice(index, 1);
  Object.keys(purchaseForm.errors).forEach((key) => {
    if (key.startsWith('additional_costs.')) {
      purchaseForm.forgetError(key as any);
    }
  });
  syncDerivedFields();
};

const addSerial = (index: number) => {
  purchaseItemDetailsExpanded.value[index] = true;
  purchaseItemsForm.value[index].serials.push({ id: null, serial: '' });
  validateField(`items.${index}.serials`);
};

const removeSerial = (index: number, serialIndex: number) => {
  purchaseItemsForm.value[index].serials.splice(serialIndex, 1);
  validateField(`items.${index}.serials`);
};

const togglePurchaseItemDetails = (index: number) => {
  purchaseItemDetailsExpanded.value[index] = !purchaseItemDetailsExpanded.value[index];
};

const updateVatProfileForItem = (item: PurchaseItemForm) => {
  const selectedVatProfile = vatProfileOptions.value.find((option) => option.code === item.vat_profile_id);

  if (!selectedVatProfile) {
    item.vat_profile_id = null;
    item.vat_profile_name = null;
    item.vat_rate = 0;
    item.vat_base_numerator = 1;
    item.vat_base_denominator = 1;
    return;
  }

  item.vat_profile_name = selectedVatProfile.name;
  item.vat_rate = selectedVatProfile.vat_rate;
  item.vat_base_numerator = normalizeVatBaseFactor(selectedVatProfile.vat_base_numerator);
  item.vat_base_denominator = normalizeVatBaseFactor(selectedVatProfile.vat_base_denominator);
};

const getItemUnitPriceAfterDiscountPreview = (item: PurchaseItemForm) =>
  item.product_unit_price_discounts.reduce((currentPrice, discount) => {
    const discountValue = Math.max(Number(discount.discount_value || 0), 0);
    const nextPrice = discount.discount_type === 'PERCENTAGE'
      ? currentPrice - ((currentPrice * discountValue) / 100)
      : currentPrice - discountValue;

    return Math.max(nextPrice, 0);
  }, Number(item.product_unit_price || 0));

const getItemUnitPriceSubtotalAfterDiscountPreview = (item: PurchaseItemForm) =>
  Number(item.qty || 0) * getItemUnitPriceAfterDiscountPreview(item);

const getItemSubtotalAfterDiscountPreview = (item: PurchaseItemForm) =>
  item.subtotal_discounts.reduce((currentSubtotal, discount) => {
    const discountValue = Math.max(Number(discount.discount_value || 0), 0);
    const nextSubtotal = discount.discount_type === 'PERCENTAGE'
      ? currentSubtotal - ((currentSubtotal * discountValue) / 100)
      : currentSubtotal - discountValue;

    return Math.max(nextSubtotal, 0);
  }, getItemUnitPriceSubtotalAfterDiscountPreview(item));

const getItemsSubtotalAfterDiscountPreview = () =>
  purchaseItemsForm.value.reduce((total, item) => total + getItemSubtotalAfterDiscountPreview(item), 0);

const getPurchaseGlobalDiscountPreview = () => {
  let totalDiscount = 0;

  purchaseGlobalDiscountsForm.value.forEach((discount) => {
    const discountValue = Math.max(Number(discount.discount_value || 0), 0);
    const currentTotal = Math.max(getItemsSubtotalAfterDiscountPreview() - totalDiscount, 0);
    const appliedDiscount = discount.discount_type === 'PERCENTAGE'
      ? (currentTotal * discountValue) / 100
      : discountValue;

    totalDiscount += Math.min(Math.max(appliedDiscount, 0), currentTotal);
  });

  return totalDiscount;
};

const getItemGlobalDiscountPreview = (item: PurchaseItemForm, itemIndex: number) => {
  const totalBeforeGlobalDiscount = getItemsSubtotalAfterDiscountPreview();
  const totalGlobalDiscount = getPurchaseGlobalDiscountPreview();

  if (totalBeforeGlobalDiscount <= 0 || totalGlobalDiscount <= 0) {
    return 0;
  }

  const allocations = purchaseItemsForm.value.map((currentItem, index) => {
    if (index === purchaseItemsForm.value.length - 1) {
      return 0;
    }

    return totalGlobalDiscount * (getItemSubtotalAfterDiscountPreview(currentItem) / totalBeforeGlobalDiscount);
  });

  const allocatedBeforeCurrent = allocations
    .slice(0, itemIndex)
    .reduce((total, allocation) => total + allocation, 0);

  if (itemIndex === purchaseItemsForm.value.length - 1) {
    return Math.max(totalGlobalDiscount - allocatedBeforeCurrent, 0);
  }

  return Math.min(Math.max(allocations[itemIndex] || 0, 0), getItemSubtotalAfterDiscountPreview(item));
};

const getItemSubtotalAfterGlobalDiscountPreview = (item: PurchaseItemForm, itemIndex: number) =>
  Math.max(getItemSubtotalAfterDiscountPreview(item) - getItemGlobalDiscountPreview(item, itemIndex), 0);

const getItemVatBasePreview = (item: PurchaseItemForm, itemIndex: number) => {
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

const getItemVatPreview = (item: PurchaseItemForm, itemIndex: number) => {
  const vatBase = getItemVatBasePreview(item, itemIndex);
  const vatRate = Number(item.vat_rate || 0);

  if (vatBase <= 0 || vatRate <= 0) {
    return 0;
  }

  return vatBase * (vatRate / 100);
};

const getItemAmountPayablePreview = (item: PurchaseItemForm, itemIndex: number) => {
  const subtotalAfterGlobalDiscount = getItemSubtotalAfterGlobalDiscountPreview(item, itemIndex);
  if (item.product_unit_is_price_include_vat) {
    return subtotalAfterGlobalDiscount;
  }

  return subtotalAfterGlobalDiscount + getItemVatPreview(item, itemIndex);
};

const getPurchaseItemTotalAfterGlobalDiscountPreview = () =>
  purchaseItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemSubtotalAfterGlobalDiscountPreview(item, itemIndex),
    0,
  );

const getPurchaseVatBasePreview = () =>
  purchaseItemsForm.value.reduce((total, item, itemIndex) => total + getItemVatBasePreview(item, itemIndex), 0);

const getPurchaseVatPreview = () =>
  purchaseItemsForm.value.reduce((total, item, itemIndex) => total + getItemVatPreview(item, itemIndex), 0);

const getAdditionalCostTotal = (additionalCost: PurchaseAdditionalCostForm) =>
  Math.max(Number(additionalCost.amount_paid_immediately || 0), 0)
  + Math.max(Number(additionalCost.amount_payable || 0), 0);

const getAdditionalCostsTotalPreview = () =>
  purchaseAdditionalCostsForm.value.reduce((total, item) => total + getAdditionalCostTotal(item), 0);

const getPurchaseAmountPayablePreview = () =>
  purchaseItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemAmountPayablePreview(item, itemIndex),
    0,
  ) + getAdditionalCostsTotalPreview() + Number(purchaseForm.rounding || 0);

const scrollToError = (id: string) => {
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

const onSubmit = async () => {
  syncDerivedFields();

  if (purchaseForm.hasErrors) {
    const firstErrorKey = Object.keys(purchaseForm.errors)[0];
    if (firstErrorKey) {
      scrollToError(firstErrorKey);
    }
    return;
  }

  emits('loading-state', true);

  try {
    await purchaseForm.submit();
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(
      t('views.purchase.alert.update.title'),
      t('views.purchase.alert.update.message'),
    );
    router.push({ name: 'side-menu-purchase-list' });
  } catch (error) {
    showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
  } finally {
    emits('loading-state', false);
  }
};
</script>

<template>
  <form v-if="selectedUserLocation" id="purchaseForm" @submit.prevent="onSubmit">
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
              <FormInput type="hidden" v-model="purchaseForm.company_id" />
            </div>
            <div class="col-span-12 lg:col-span-4 md:col-span-6">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput type="hidden" v-model="purchaseForm.branch_id" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 md:col-span-6 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': invalidField('code') }">
                {{ t('views.purchase.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="purchaseForm.code"
                :class="{ 'border-danger': invalidField('code') }"
                :placeholder="t('views.purchase.fields.code')"
                @set-auto="setCode"
                @change="purchaseForm.validate('code')"
              />
              <FormErrorMessages :messages="purchaseForm.errors.code" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('date') }">
                {{ t('views.purchase.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="purchaseForm.date"
                :class="{ 'border-danger': invalidField('date') }"
                @change="purchaseForm.validate('date')"
              />
              <FormErrorMessages :messages="purchaseForm.errors.date" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-1">
              <FormLabel :class="{ 'text-danger': invalidField('due_days') }">
                {{ t('views.purchase.fields.due_days') }}
              </FormLabel>
              <FormInput
                v-model="purchaseForm.due_days"
                type="number"
                min="0"
                :class="{ 'border-danger': invalidField('due_days') }"
                @change="purchaseForm.validate('due_days')"
              />
              <FormErrorMessages :messages="purchaseForm.errors.due_days" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('supplier_id') }">
                {{ t('views.purchase.fields.supplier_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="purchaseForm.supplier_id"
                v-model:search="supplierSearch"
                :options="supplierOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': invalidField('supplier_id') }"
                @search="loadSupplierDDL"
                @change="purchaseForm.validate('supplier_id')"
              />
              <FormErrorMessages :messages="purchaseForm.errors.supplier_id" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('purchase_order_id') }">
                {{ t('views.purchase.fields.purchase_order_id') }}
              </FormLabel>
              <FormInput
                v-model="purchaseForm.purchase_order_id"
                :class="{ 'border-danger': invalidField('purchase_order_id') }"
                :placeholder="t('views.purchase.fields.purchase_order_id_optional')"
                @change="purchaseForm.validate('purchase_order_id')"
              />
              <FormErrorMessages :messages="purchaseForm.errors.purchase_order_id" />
            </div>
            <div v-if="isDirectMode" class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('direct_receipt_warehouse_id') }">
                {{ t('views.purchase.fields.direct_receipt_warehouse_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="purchaseForm.direct_receipt_warehouse_id"
                v-model:search="warehouseSearch"
                :options="warehouseOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': invalidField('direct_receipt_warehouse_id') }"
                @search="loadWarehouseDDL"
                @change="purchaseForm.validate('direct_receipt_warehouse_id')"
              />
              <FormErrorMessages :messages="purchaseForm.errors.direct_receipt_warehouse_id" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('tax_invoice_number') }">
                {{ t('views.purchase.fields.tax_invoice_number') }}
              </FormLabel>
              <FormInput
                v-model="purchaseForm.tax_invoice_number"
                :class="{ 'border-danger': invalidField('tax_invoice_number') }"
                @change="purchaseForm.validate('tax_invoice_number')"
              />
              <FormErrorMessages :messages="purchaseForm.errors.tax_invoice_number" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('tax_invoice_vat_base') }">
                {{ t('views.purchase.fields.tax_invoice_vat_base') }}
              </FormLabel>
              <FormInputCurrency
                v-model="purchaseForm.tax_invoice_vat_base"
                :allow-negative="false"
                :class="{ 'border-danger': invalidField('tax_invoice_vat_base') }"
                @change="purchaseForm.validate('tax_invoice_vat_base')"
              />
              <FormErrorMessages :messages="purchaseForm.errors.tax_invoice_vat_base" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel :class="{ 'text-danger': invalidField('tax_invoice_vat') }">
                {{ t('views.purchase.fields.tax_invoice_vat') }}
              </FormLabel>
              <FormInputCurrency
                v-model="purchaseForm.tax_invoice_vat"
                :allow-negative="false"
                :class="{ 'border-danger': invalidField('tax_invoice_vat') }"
                @change="purchaseForm.validate('tax_invoice_vat')"
              />
              <FormErrorMessages :messages="purchaseForm.errors.tax_invoice_vat" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-4 flex flex-col justify-center">
              <FormLabel>
                {{ t('views.purchase.fields.is_posted') }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input v-model="purchaseForm.is_posted" type="checkbox" />
              </FormSwitch>
            </div>
            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': invalidField('remarks') }">
                {{ t('views.purchase.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                v-model="purchaseForm.remarks"
                :class="{ 'border-danger': invalidField('remarks') }"
                @change="purchaseForm.validate('remarks')"
              />
              <FormErrorMessages :messages="purchaseForm.errors.remarks" />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-2>
        <div class="space-y-4 p-5">
          <div class="flex items-center justify-between gap-3">
            <div class="text-sm text-slate-500">
              {{ t('views.purchase.fields.items_empty') }}
            </div>
            <Button type="button" variant="primary" @click="openAddProductUnit">
              <Lucide icon="Plus" class="mr-1 h-4 w-4" />
              {{ t('components.buttons.add') }}
            </Button>
          </div>

          <FormErrorMessages :messages="purchaseForm.errors.items" />

          <div v-if="purchaseItemsForm.length === 0" class="rounded-md border border-dashed p-4 text-sm text-slate-500">
            {{ t('views.purchase.fields.items_empty') }}
          </div>

          <div
            v-for="(item, index) in purchaseItemsForm"
            :key="`${item.product_unit_id}-${index}`"
            class="mt-3 border-t border-slate-200/60 pt-5 first:mt-0 first:border-t-0 first:pt-0 dark:border-darkmode-400"
          >
            <div v-if="currentItemLayout === 'sm'" class="grid grid-cols-12 gap-4 gap-y-3">
              <div class="col-span-12">
                <FormLabel>
                  <span>{{ t('views.purchase.fields.product_unit_id') }}</span>
                  <span class="ml-2 text-xs font-normal text-slate-500 dark:text-slate-400">#{{ index + 1 }}</span>
                  <span class="ml-2 text-xs font-normal text-slate-500 dark:text-slate-400">
                    {{ item.product_unit_product_code || '-' }}
                  </span>
                </FormLabel>
                <div
                  class="form-control border rounded-md px-3 py-2 bg-slate-50 dark:bg-darkmode-800 text-slate-700 dark:text-slate-300"
                >
                  {{ item.product_unit_product_name || '-' }}
                </div>
              </div>
              <div class="col-span-12">
                <div class="grid grid-cols-3 gap-4">
                  <div>
                    <FormLabel :class="{ 'text-danger': invalidField(`items.${index}.qty`) }">
                      {{ t('views.purchase.fields.qty') }}
                    </FormLabel>
                    <FormInputCurrency
                      :id="`purchase-item-qty-${index}`"
                      v-model="item.qty"
                      :allow-negative="false"
                      :class="{ 'border-danger': invalidField(`items.${index}.qty`) }"
                      @change="validateField(`items.${index}.qty`)"
                    />
                    <FormErrorMessages :messages="getFieldErrors(`items.${index}.qty`)" />
                  </div>
                  <div>
                    <FormLabel>{{ t('views.product.table.cols.unit') }}</FormLabel>
                    <FormInput :model-value="item.product_unit_unit_name || '-'" readonly />
                  </div>
                  <div>
                    <FormLabel>{{ t('views.purchase.fields.product_unit_conversion_value') }}</FormLabel>
                    <FormInputCurrency
                      v-model="item.product_unit_conversion_value"
                      :allow-negative="false"
                      @change="validateField(`items.${index}.product_unit_conversion_value`)"
                    />
                    <FormErrorMessages :messages="getFieldErrors(`items.${index}.product_unit_conversion_value`)" />
                  </div>
                </div>
              </div>
              <div class="col-span-12">
                <FormLabel :class="{ 'text-danger': invalidField(`items.${index}.product_unit_price`) }">
                  {{ t('views.purchase.fields.product_unit_price') }}
                </FormLabel>
                <FormInputCurrency
                  v-model="item.product_unit_price"
                  :allow-negative="false"
                  :class="{ 'border-danger': invalidField(`items.${index}.product_unit_price`) }"
                  @change="validateField(`items.${index}.product_unit_price`)"
                />
                <FormErrorMessages :messages="getFieldErrors(`items.${index}.product_unit_price`)" />
              </div>
              <div class="col-span-12">
                <FormLabel>{{ t('views.purchase.fields.amount_payable') }}</FormLabel>
                <div class="flex items-start gap-2">
                  <div class="flex-1 min-w-0">
                    <FormInputCurrency :model-value="getItemAmountPayablePreview(item, index)" readonly />
                  </div>
                  <div class="shrink-0">
                    <Button
                      type="button"
                      variant="outline-secondary"
                      class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                      @click="togglePurchaseItemDetails(index)"
                    >
                      <Lucide :icon="purchaseItemDetailsExpanded[index] ? 'ChevronUp' : 'ChevronDown'" class="w-4 h-4" />
                    </Button>
                  </div>
                  <div class="shrink-0">
                    <Button
                      type="button"
                      variant="outline-secondary"
                      class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                      @click="removeItem(index)"
                    >
                      <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                    </Button>
                  </div>
                </div>
              </div>
            </div>

            <div v-else-if="currentItemLayout === 'md'" class="grid grid-cols-12 gap-4 gap-y-3">
              <div class="col-span-12">
                <FormLabel>
                  <span>{{ t('views.purchase.fields.product_unit_id') }}</span>
                  <span class="ml-2 text-xs font-normal text-slate-500 dark:text-slate-400">#{{ index + 1 }}</span>
                  <span class="ml-2 text-xs font-normal text-slate-500 dark:text-slate-400">
                    {{ item.product_unit_product_code || '-' }}
                  </span>
                </FormLabel>
                <div
                  class="form-control border rounded-md px-3 py-2 bg-slate-50 dark:bg-darkmode-800 text-slate-700 dark:text-slate-300"
                >
                  {{ item.product_unit_product_name || '-' }}
                </div>
              </div>
              <div class="col-span-12 md:col-span-5">
                <div class="grid grid-cols-3 gap-4">
                  <div>
                    <FormLabel :class="{ 'text-danger': invalidField(`items.${index}.qty`) }">
                      {{ t('views.purchase.fields.qty') }}
                    </FormLabel>
                    <FormInputCurrency
                      :id="`purchase-item-qty-${index}`"
                      v-model="item.qty"
                      :allow-negative="false"
                      :class="{ 'border-danger': invalidField(`items.${index}.qty`) }"
                      @change="validateField(`items.${index}.qty`)"
                    />
                    <FormErrorMessages :messages="getFieldErrors(`items.${index}.qty`)" />
                  </div>
                  <div>
                    <FormLabel>{{ t('views.product.table.cols.unit') }}</FormLabel>
                    <FormInput :model-value="item.product_unit_unit_name || '-'" readonly />
                  </div>
                  <div>
                    <FormLabel>{{ t('views.purchase.fields.product_unit_conversion_value') }}</FormLabel>
                    <FormInputCurrency
                      v-model="item.product_unit_conversion_value"
                      :allow-negative="false"
                      @change="validateField(`items.${index}.product_unit_conversion_value`)"
                    />
                    <FormErrorMessages :messages="getFieldErrors(`items.${index}.product_unit_conversion_value`)" />
                  </div>
                </div>
              </div>
              <div class="col-span-12 md:col-span-3">
                <FormLabel :class="{ 'text-danger': invalidField(`items.${index}.product_unit_price`) }">
                  {{ t('views.purchase.fields.product_unit_price') }}
                </FormLabel>
                <FormInputCurrency
                  v-model="item.product_unit_price"
                  :allow-negative="false"
                  :class="{ 'border-danger': invalidField(`items.${index}.product_unit_price`) }"
                  @change="validateField(`items.${index}.product_unit_price`)"
                />
                <FormErrorMessages :messages="getFieldErrors(`items.${index}.product_unit_price`)" />
              </div>
              <div class="col-span-12 md:col-span-4">
                <FormLabel>{{ t('views.purchase.fields.amount_payable') }}</FormLabel>
                <div class="flex items-start gap-2">
                  <div class="flex-1 min-w-0">
                    <FormInputCurrency :model-value="getItemAmountPayablePreview(item, index)" readonly />
                  </div>
                  <div class="shrink-0">
                    <Button
                      type="button"
                      variant="outline-secondary"
                      class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                      @click="togglePurchaseItemDetails(index)"
                    >
                      <Lucide :icon="purchaseItemDetailsExpanded[index] ? 'ChevronUp' : 'ChevronDown'" class="w-4 h-4" />
                    </Button>
                  </div>
                  <div class="shrink-0">
                    <Button
                      type="button"
                      variant="outline-secondary"
                      class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                      @click="removeItem(index)"
                    >
                      <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                    </Button>
                  </div>
                </div>
              </div>
            </div>

            <div v-else class="grid grid-cols-12 gap-4 gap-y-3">
              <div class="col-span-12 lg:col-span-4">
                <FormLabel>
                  <span>{{ t('views.purchase.fields.product_unit_id') }}</span>
                  <span class="ml-2 text-xs font-normal text-slate-500 dark:text-slate-400">#{{ index + 1 }}</span>
                  <span class="ml-2 text-xs font-normal text-slate-500 dark:text-slate-400">
                    {{ item.product_unit_product_code || '-' }}
                  </span>
                </FormLabel>
                <div
                  class="form-control border rounded-md px-3 py-2 bg-slate-50 dark:bg-darkmode-800 text-slate-700 dark:text-slate-300"
                >
                  {{ item.product_unit_product_name || '-' }}
                </div>
              </div>
              <div class="col-span-12 lg:col-span-3">
                <div class="grid grid-cols-1 gap-4 lg:grid-cols-3">
                  <div>
                    <FormLabel :class="{ 'text-danger': invalidField(`items.${index}.qty`) }">
                      {{ t('views.purchase.fields.qty') }}
                    </FormLabel>
                    <FormInputCurrency
                      :id="`purchase-item-qty-${index}`"
                      v-model="item.qty"
                      :allow-negative="false"
                      :class="{ 'border-danger': invalidField(`items.${index}.qty`) }"
                      @change="validateField(`items.${index}.qty`)"
                    />
                    <FormErrorMessages :messages="getFieldErrors(`items.${index}.qty`)" />
                  </div>
                  <div>
                    <FormLabel>{{ t('views.product.table.cols.unit') }}</FormLabel>
                    <FormInput :model-value="item.product_unit_unit_name || '-'" readonly />
                  </div>
                  <div>
                    <FormLabel>{{ t('views.purchase.fields.product_unit_conversion_value') }}</FormLabel>
                    <FormInputCurrency
                      v-model="item.product_unit_conversion_value"
                      :allow-negative="false"
                      @change="validateField(`items.${index}.product_unit_conversion_value`)"
                    />
                    <FormErrorMessages :messages="getFieldErrors(`items.${index}.product_unit_conversion_value`)" />
                  </div>
                </div>
              </div>
              <div class="col-span-12 lg:col-span-2">
                <FormLabel :class="{ 'text-danger': invalidField(`items.${index}.product_unit_price`) }">
                  {{ t('views.purchase.fields.product_unit_price') }}
                </FormLabel>
                <FormInputCurrency
                  v-model="item.product_unit_price"
                  :allow-negative="false"
                  :class="{ 'border-danger': invalidField(`items.${index}.product_unit_price`) }"
                  @change="validateField(`items.${index}.product_unit_price`)"
                />
                <FormErrorMessages :messages="getFieldErrors(`items.${index}.product_unit_price`)" />
              </div>
              <div class="col-span-12 lg:col-span-3">
                <FormLabel>{{ t('views.purchase.fields.amount_payable') }}</FormLabel>
                <div class="flex items-start gap-2">
                  <div class="flex-1 min-w-0">
                    <FormInputCurrency :model-value="getItemAmountPayablePreview(item, index)" readonly />
                  </div>
                  <div class="shrink-0">
                    <Button
                      type="button"
                      variant="outline-secondary"
                      class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                      @click="togglePurchaseItemDetails(index)"
                    >
                      <Lucide :icon="purchaseItemDetailsExpanded[index] ? 'ChevronUp' : 'ChevronDown'" class="w-4 h-4" />
                    </Button>
                  </div>
                  <div class="shrink-0">
                    <Button
                      type="button"
                      variant="outline-secondary"
                      class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                      @click="removeItem(index)"
                    >
                      <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                    </Button>
                  </div>
                </div>
              </div>
            </div>

            <div v-if="currentItemLayout !== 'lg' && purchaseItemDetailsExpanded[index]" class="mt-4 space-y-4">
              <div class="rounded-md border border-slate-200/60 p-4 space-y-5 dark:border-darkmode-400">
                <div class="font-medium text-sm">{{ t('views.purchase.fields.item_price_breakdown') }}</div>

                <div class="space-y-3">
                  <div v-if="item.product_unit_price_discounts.length === 0" class="text-sm text-right text-slate-500">
                    {{ t('views.purchase.fields.product_unit_price_discounts_empty') }}
                  </div>
                  <div v-else class="space-y-3">
                    <div
                      v-for="(discount, discountIndex) in item.product_unit_price_discounts"
                      :key="`${index}-price-mobile-${discountIndex}`"
                      class="grid grid-cols-12 gap-4 gap-y-3"
                    >
                      <div class="col-span-12 md:col-span-2">
                        <FormLabel>{{ t('views.purchase.fields.sequence') }}</FormLabel>
                        <FormInput :model-value="discountIndex + 1" readonly />
                      </div>
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel>{{ t('views.purchase.fields.discount_type') }}</FormLabel>
                        <FormSelect v-model="discount.discount_type">
                          <option v-for="option in discountTypeOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                          </option>
                        </FormSelect>
                      </div>
                      <div class="col-span-12 md:col-span-6">
                        <FormLabel>{{ t('views.purchase.fields.discount_value') }}</FormLabel>
                        <div class="flex items-start gap-2">
                          <div class="flex-1 min-w-0">
                            <FormInputCurrency v-model="discount.discount_value" :allow-negative="false" />
                          </div>
                          <div class="shrink-0">
                            <Button
                              type="button"
                              variant="outline-secondary"
                              class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                              @click="removeItemPriceDiscount(index, discountIndex)"
                            >
                              <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                            </Button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="flex justify-end">
                    <Button type="button" variant="outline-primary" @click="addItemPriceDiscount(index)">
                      <Lucide icon="Plus" class="mr-1 h-4 w-4" />
                      {{ t('components.buttons.add') }}
                    </Button>
                  </div>

                  <div class="grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                      {{ t('views.purchase.fields.price_after_discount') }}
                    </div>
                    <div class="col-span-12 md:col-span-8">
                      <FormInputCurrency :model-value="getItemUnitPriceAfterDiscountPreview(item)" readonly />
                    </div>
                  </div>

                  <div class="grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                      {{ t('views.purchase.fields.subtotal') }}
                    </div>
                    <div class="col-span-12 md:col-span-8">
                      <FormInputCurrency :model-value="getItemUnitPriceSubtotalAfterDiscountPreview(item)" readonly />
                    </div>
                  </div>
                </div>

                <div class="space-y-3">
                  <div v-if="item.subtotal_discounts.length === 0" class="text-sm text-right text-slate-500">
                    {{ t('views.purchase.fields.subtotal_discounts_empty') }}
                  </div>
                  <div v-else class="space-y-3">
                    <div
                      v-for="(discount, discountIndex) in item.subtotal_discounts"
                      :key="`${index}-subtotal-mobile-${discountIndex}`"
                      class="grid grid-cols-12 gap-4 gap-y-3"
                    >
                      <div class="col-span-12 md:col-span-2">
                        <FormLabel>{{ t('views.purchase.fields.sequence') }}</FormLabel>
                        <FormInput :model-value="discountIndex + 1" readonly />
                      </div>
                      <div class="col-span-12 md:col-span-4">
                        <FormLabel>{{ t('views.purchase.fields.discount_type') }}</FormLabel>
                        <FormSelect v-model="discount.discount_type">
                          <option v-for="option in discountTypeOptions" :key="option.value" :value="option.value">
                            {{ option.label }}
                          </option>
                        </FormSelect>
                      </div>
                      <div class="col-span-12 md:col-span-6">
                        <FormLabel>{{ t('views.purchase.fields.discount_value') }}</FormLabel>
                        <div class="flex items-start gap-2">
                          <div class="flex-1 min-w-0">
                            <FormInputCurrency v-model="discount.discount_value" :allow-negative="false" />
                          </div>
                          <div class="shrink-0">
                            <Button
                              type="button"
                              variant="outline-secondary"
                              class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                              @click="removeItemSubtotalDiscount(index, discountIndex)"
                            >
                              <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                            </Button>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>
                  <div class="flex justify-end">
                    <Button type="button" variant="outline-primary" @click="addItemSubtotalDiscount(index)">
                      <Lucide icon="Plus" class="mr-1 h-4 w-4" />
                      {{ t('components.buttons.add') }}
                    </Button>
                  </div>

                  <div class="grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                      {{ t('views.purchase.fields.subtotal_after_discount') }}
                    </div>
                    <div class="col-span-12 md:col-span-8">
                      <FormInputCurrency :model-value="getItemSubtotalAfterDiscountPreview(item)" readonly />
                    </div>
                  </div>
                  <div class="grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                      {{ t('views.purchase.fields.global_discount') }}
                    </div>
                    <div class="col-span-12 md:col-span-8">
                      <FormInputCurrency :model-value="getItemGlobalDiscountPreview(item, index)" readonly />
                    </div>
                  </div>
                  <div class="grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                      {{ t('views.purchase.fields.vat') }}
                    </div>
                    <div class="col-span-12 md:col-span-8">
                      <FormInputCurrency :model-value="getItemVatPreview(item, index)" readonly />
                    </div>
                  </div>
                  <div class="grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                      {{ t('views.purchase.fields.amount_payable') }}
                    </div>
                    <div class="col-span-12 md:col-span-8">
                      <FormInputCurrency :model-value="getItemAmountPayablePreview(item, index)" readonly />
                    </div>
                  </div>
                </div>
              </div>

              <div class="rounded-md border border-slate-200/60 p-4 space-y-4 dark:border-darkmode-400">
                <div class="font-medium text-sm">{{ t('views.purchase.fields.item_additional_details') }}</div>

                <div class="grid grid-cols-12 gap-4 gap-y-3">
                  <div class="col-span-12 md:col-span-4">
                    <FormLabel>{{ t('views.purchase.fields.product_unit_is_price_include_vat') }}</FormLabel>
                    <FormSwitch>
                      <FormSwitch.Input v-model="item.product_unit_is_price_include_vat" type="checkbox" />
                    </FormSwitch>
                  </div>
                  <div class="col-span-12 md:col-span-8">
                    <FormLabel>{{ t('views.purchase.fields.vat_profile_id') }}</FormLabel>
                    <FormSelect
                      v-model="item.vat_profile_id"
                      @change="
                        updateVatProfileForItem(item);
                        validateField(`items.${index}.vat_profile_id`);
                      "
                    >
                      <option :value="null">{{ t('components.dropdown.placeholder') }}</option>
                      <option v-for="vatProfile in vatProfileOptions" :key="vatProfile.code" :value="vatProfile.code">
                        {{ vatProfile.name }}
                      </option>
                    </FormSelect>
                    <FormErrorMessages :messages="getFieldErrors(`items.${index}.vat_profile_id`)" />
                  </div>
                  <div class="col-span-12 md:col-span-4">
                    <FormLabel>{{ t('views.purchase.fields.vat_rate') }}</FormLabel>
                    <FormInputCurrency :model-value="item.vat_rate" readonly />
                  </div>
                  <div class="col-span-12 md:col-span-8">
                    <FormLabel>{{ t('views.purchase.fields.vat_base_fraction') }}</FormLabel>
                    <FormInput :model-value="`${item.vat_base_numerator}/${item.vat_base_denominator}`" readonly />
                  </div>
                  <div class="col-span-12">
                    <FormLabel>{{ t('views.purchase.fields.remarks') }}</FormLabel>
                    <FormTextarea v-model="item.remarks" @change="validateField(`items.${index}.remarks`)" />
                    <FormErrorMessages :messages="getFieldErrors(`items.${index}.remarks`)" />
                  </div>
                </div>

                <div v-if="isDirectMode && item.is_use_serial_number" class="space-y-3">
                  <div class="flex items-center justify-between">
                    <div class="font-medium">{{ t('views.purchase.fields.serials') }}</div>
                    <Button type="button" variant="outline-primary" size="sm" @click="addSerial(index)">
                      <Lucide icon="Plus" class="mr-1 h-4 w-4" />
                      {{ t('components.buttons.add') }}
                    </Button>
                  </div>
                  <FormErrorMessages :messages="getFieldErrors(`items.${index}.serials`)" />
                  <div v-if="item.serials.length === 0" class="text-sm text-right text-slate-500">
                    {{ t('views.purchase.fields.serials_empty') }}
                  </div>
                  <div v-else class="space-y-3">
                    <div
                      v-for="(serial, serialIndex) in item.serials"
                      :key="`serial-${index}-${serialIndex}`"
                      class="grid grid-cols-12 gap-4 gap-y-3"
                    >
                      <div class="col-span-12 md:col-span-10">
                        <FormLabel>{{ t('views.product.fields.serial_number') }}</FormLabel>
                        <FormInput
                          v-model="serial.serial"
                          @change="validateField(`items.${index}.serials.${serialIndex}.serial`)"
                        />
                        <FormErrorMessages :messages="getFieldErrors(`items.${index}.serials.${serialIndex}.serial`)" />
                      </div>
                      <div class="col-span-12 md:col-span-2 flex items-end">
                        <Button
                          type="button"
                          variant="outline-secondary"
                          class="h-[38px] w-full min-w-0 flex items-center justify-center"
                          @click="removeSerial(index, serialIndex)"
                        >
                          <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                        </Button>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <div v-else-if="purchaseItemDetailsExpanded[index]" class="mt-4 grid grid-cols-12 gap-4">
              <div class="col-span-12 lg:col-span-6">
                <div class="rounded-md border border-slate-200/60 p-4 space-y-4 dark:border-darkmode-400">
                  <div class="font-medium text-sm">{{ t('views.purchase.fields.item_additional_details') }}</div>

                  <div class="grid grid-cols-12 gap-4 gap-y-3">
                    <div class="col-span-12 md:col-span-5">
                      <FormLabel>{{ t('views.purchase.fields.product_unit_is_price_include_vat') }}</FormLabel>
                      <FormSwitch>
                        <FormSwitch.Input v-model="item.product_unit_is_price_include_vat" type="checkbox" />
                      </FormSwitch>
                    </div>
                    <div class="col-span-12 md:col-span-7">
                      <FormLabel>{{ t('views.purchase.fields.vat_profile_id') }}</FormLabel>
                      <FormSelect
                        v-model="item.vat_profile_id"
                        @change="
                          updateVatProfileForItem(item);
                          validateField(`items.${index}.vat_profile_id`);
                        "
                      >
                        <option :value="null">{{ t('components.dropdown.placeholder') }}</option>
                        <option v-for="vatProfile in vatProfileOptions" :key="vatProfile.code" :value="vatProfile.code">
                          {{ vatProfile.name }}
                        </option>
                      </FormSelect>
                      <FormErrorMessages :messages="getFieldErrors(`items.${index}.vat_profile_id`)" />
                    </div>
                    <div class="col-span-12 md:col-span-4">
                      <FormLabel>{{ t('views.purchase.fields.vat_rate') }}</FormLabel>
                      <FormInputCurrency :model-value="item.vat_rate" readonly />
                    </div>
                    <div class="col-span-12 md:col-span-8">
                      <FormLabel>{{ t('views.purchase.fields.vat_base_fraction') }}</FormLabel>
                      <FormInput :model-value="`${item.vat_base_numerator}/${item.vat_base_denominator}`" readonly />
                    </div>
                    <div class="col-span-12">
                      <FormLabel>{{ t('views.purchase.fields.remarks') }}</FormLabel>
                      <FormTextarea v-model="item.remarks" @change="validateField(`items.${index}.remarks`)" />
                      <FormErrorMessages :messages="getFieldErrors(`items.${index}.remarks`)" />
                    </div>
                  </div>

                  <div v-if="isDirectMode && item.is_use_serial_number" class="space-y-3">
                    <div class="flex items-center justify-between">
                      <div class="font-medium">{{ t('views.purchase.fields.serials') }}</div>
                      <Button type="button" variant="outline-primary" size="sm" @click="addSerial(index)">
                        <Lucide icon="Plus" class="mr-1 h-4 w-4" />
                        {{ t('components.buttons.add') }}
                      </Button>
                    </div>
                    <FormErrorMessages :messages="getFieldErrors(`items.${index}.serials`)" />
                    <div v-if="item.serials.length === 0" class="text-sm text-right text-slate-500">
                      {{ t('views.purchase.fields.serials_empty') }}
                    </div>
                    <div v-else class="space-y-3">
                      <div
                        v-for="(serial, serialIndex) in item.serials"
                        :key="`${index}-serial-${serialIndex}`"
                        class="grid grid-cols-12 gap-4 gap-y-3"
                      >
                        <div class="col-span-12 md:col-span-10">
                          <FormLabel>{{ t('views.product.fields.serial_number') }}</FormLabel>
                          <FormInput
                            v-model="serial.serial"
                            @change="validateField(`items.${index}.serials.${serialIndex}.serial`)"
                          />
                          <FormErrorMessages :messages="getFieldErrors(`items.${index}.serials.${serialIndex}.serial`)" />
                        </div>
                        <div class="col-span-12 md:col-span-2 flex items-end">
                          <Button
                            type="button"
                            variant="outline-secondary"
                            class="h-[38px] w-full min-w-0 flex items-center justify-center"
                            @click="removeSerial(index, serialIndex)"
                          >
                            <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                          </Button>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-span-12 lg:col-span-6">
                <div class="rounded-md border border-slate-200/60 p-4 space-y-5 dark:border-darkmode-400">
                  <div class="font-medium text-sm">{{ t('views.purchase.fields.item_price_breakdown') }}</div>

                  <div class="space-y-3">
                    <div v-if="item.product_unit_price_discounts.length === 0" class="text-sm text-right text-slate-500">
                      {{ t('views.purchase.fields.product_unit_price_discounts_empty') }}
                    </div>
                    <div v-else class="space-y-3">
                      <div
                        v-for="(discount, discountIndex) in item.product_unit_price_discounts"
                        :key="`${index}-price-${discountIndex}`"
                        class="grid grid-cols-12 gap-4 gap-y-3"
                      >
                        <div class="col-span-12 md:col-span-2">
                          <FormLabel>{{ t('views.purchase.fields.sequence') }}</FormLabel>
                          <FormInput :model-value="discountIndex + 1" readonly />
                        </div>
                        <div class="col-span-12 md:col-span-4">
                          <FormLabel>{{ t('views.purchase.fields.discount_type') }}</FormLabel>
                          <FormSelect v-model="discount.discount_type">
                            <option v-for="option in discountTypeOptions" :key="option.value" :value="option.value">
                              {{ option.label }}
                            </option>
                          </FormSelect>
                        </div>
                        <div class="col-span-12 md:col-span-6">
                          <FormLabel>{{ t('views.purchase.fields.discount_value') }}</FormLabel>
                          <div class="flex items-start gap-2">
                            <div class="flex-1 min-w-0">
                              <FormInputCurrency v-model="discount.discount_value" :allow-negative="false" />
                            </div>
                            <div class="shrink-0">
                              <Button
                                type="button"
                                variant="outline-secondary"
                                class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                                @click="removeItemPriceDiscount(index, discountIndex)"
                              >
                                <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                              </Button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="flex justify-end">
                      <Button type="button" variant="outline-primary" @click="addItemPriceDiscount(index)">
                        <Lucide icon="Plus" class="mr-1 h-4 w-4" />
                        {{ t('components.buttons.add') }}
                      </Button>
                    </div>

                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase.fields.price_after_discount') }}
                      </div>
                      <div class="col-span-12 md:col-span-8">
                        <FormInputCurrency :model-value="getItemUnitPriceAfterDiscountPreview(item)" readonly />
                      </div>
                    </div>
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase.fields.subtotal') }}
                      </div>
                      <div class="col-span-12 md:col-span-8">
                        <FormInputCurrency :model-value="getItemUnitPriceSubtotalAfterDiscountPreview(item)" readonly />
                      </div>
                    </div>
                  </div>

                  <div class="space-y-3">
                    <div v-if="item.subtotal_discounts.length === 0" class="text-sm text-right text-slate-500">
                      {{ t('views.purchase.fields.subtotal_discounts_empty') }}
                    </div>
                    <div v-else class="space-y-3">
                      <div
                        v-for="(discount, discountIndex) in item.subtotal_discounts"
                        :key="`${index}-subtotal-${discountIndex}`"
                        class="grid grid-cols-12 gap-4 gap-y-3"
                      >
                        <div class="col-span-12 md:col-span-2">
                          <FormLabel>{{ t('views.purchase.fields.sequence') }}</FormLabel>
                          <FormInput :model-value="discountIndex + 1" readonly />
                        </div>
                        <div class="col-span-12 md:col-span-4">
                          <FormLabel>{{ t('views.purchase.fields.discount_type') }}</FormLabel>
                          <FormSelect v-model="discount.discount_type">
                            <option v-for="option in discountTypeOptions" :key="option.value" :value="option.value">
                              {{ option.label }}
                            </option>
                          </FormSelect>
                        </div>
                        <div class="col-span-12 md:col-span-6">
                          <FormLabel>{{ t('views.purchase.fields.discount_value') }}</FormLabel>
                          <div class="flex items-start gap-2">
                            <div class="flex-1 min-w-0">
                              <FormInputCurrency v-model="discount.discount_value" :allow-negative="false" />
                            </div>
                            <div class="shrink-0">
                              <Button
                                type="button"
                                variant="outline-secondary"
                                class="h-[38px] w-[38px] min-w-0 flex items-center justify-center"
                                @click="removeItemSubtotalDiscount(index, discountIndex)"
                              >
                                <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
                              </Button>
                            </div>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="flex justify-end">
                      <Button type="button" variant="outline-primary" @click="addItemSubtotalDiscount(index)">
                        <Lucide icon="Plus" class="mr-1 h-4 w-4" />
                        {{ t('components.buttons.add') }}
                      </Button>
                    </div>

                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase.fields.subtotal_after_discount') }}
                      </div>
                      <div class="col-span-12 md:col-span-8">
                        <FormInputCurrency :model-value="getItemSubtotalAfterDiscountPreview(item)" readonly />
                      </div>
                    </div>
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase.fields.global_discount') }}
                      </div>
                      <div class="col-span-12 md:col-span-8">
                        <FormInputCurrency :model-value="getItemGlobalDiscountPreview(item, index)" readonly />
                      </div>
                    </div>
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase.fields.vat') }}
                      </div>
                      <div class="col-span-12 md:col-span-8">
                        <FormInputCurrency :model-value="getItemVatPreview(item, index)" readonly />
                      </div>
                    </div>
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-4 flex items-center text-sm font-medium">
                        {{ t('views.purchase.fields.amount_payable') }}
                      </div>
                      <div class="col-span-12 md:col-span-8">
                        <FormInputCurrency :model-value="getItemAmountPayablePreview(item, index)" readonly />
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #card-items-3>
        <div class="space-y-4 p-5">
          <div class="flex items-center justify-between gap-3">
            <div class="text-sm text-slate-500">
              {{ t('views.purchase.fields.additional_costs_hint') }}
            </div>
            <Button type="button" variant="primary" @click="addAdditionalCost">
              <Lucide icon="Plus" class="mr-1 h-4 w-4" />
              {{ t('components.buttons.add') }}
            </Button>
          </div>

          <FormErrorMessages :messages="purchaseForm.errors.additional_costs" />

          <div v-if="purchaseAdditionalCostsForm.length === 0" class="rounded-md border border-dashed p-4 text-sm text-slate-500">
            {{ t('views.purchase.fields.additional_costs_empty') }}
          </div>

          <div
            v-for="(additionalCost, index) in purchaseAdditionalCostsForm"
            :key="`additional-cost-${index}`"
            class="rounded-md border border-slate-200/60 p-4 dark:border-darkmode-400"
          >
            <div class="mb-3 flex justify-end">
              <Button type="button" variant="outline-danger" @click="removeAdditionalCost(index)">
                <Lucide icon="Trash2" class="h-4 w-4" />
              </Button>
            </div>

            <div class="grid grid-cols-12 gap-4 gap-y-3">
              <div class="col-span-12 md:col-span-3">
                <FormLabel :class="{ 'text-danger': invalidField(`additional_costs.${index}.code`) }">
                  {{ t('views.purchase.fields.code') }}
                </FormLabel>
                <FormInputCode
                  v-model="additionalCost.code"
                  :class="{ 'border-danger': invalidField(`additional_costs.${index}.code`) }"
                  @set-auto="setAdditionalCostCode(index)"
                  @change="validateField(`additional_costs.${index}.code`)"
                />
                <FormErrorMessages :messages="getFieldErrors(`additional_costs.${index}.code`)" />
              </div>
              <div class="col-span-12 md:col-span-3">
                <FormLabel :class="{ 'text-danger': invalidField(`additional_costs.${index}.date`) }">
                  {{ t('views.purchase.fields.date') }}
                </FormLabel>
                <FormInputDateTimeAuto
                  v-model="additionalCost.date"
                  :class="{ 'border-danger': invalidField(`additional_costs.${index}.date`) }"
                  @change="validateField(`additional_costs.${index}.date`)"
                />
                <FormErrorMessages :messages="getFieldErrors(`additional_costs.${index}.date`)" />
              </div>
              <div class="col-span-12 md:col-span-3">
                <FormLabel :class="{ 'text-danger': invalidField(`additional_costs.${index}.due_days`) }">
                  {{ t('views.purchase.fields.due_days') }}
                </FormLabel>
                <FormInput
                  v-model="additionalCost.due_days"
                  type="number"
                  min="0"
                  :class="{ 'border-danger': invalidField(`additional_costs.${index}.due_days`) }"
                  @change="validateField(`additional_costs.${index}.due_days`)"
                />
                <FormErrorMessages :messages="getFieldErrors(`additional_costs.${index}.due_days`)" />
              </div>
              <div class="col-span-12 md:col-span-6">
                <FormLabel :class="{ 'text-danger': invalidField(`additional_costs.${index}.purchase_additional_cost_category_id`) }">
                  {{ t('views.purchase.fields.purchase_additional_cost_category_id') }}
                </FormLabel>
                <FormSelectSearch
                  v-model="additionalCost.purchase_additional_cost_category_id"
                  v-model:search="additionalCostCategorySearch"
                  :options="additionalCostCategoryOptions"
                  :placeholder="t('components.dropdown.placeholder')"
                  :class="{ 'border-danger': invalidField(`additional_costs.${index}.purchase_additional_cost_category_id`) }"
                  @search="loadAdditionalCostCategoryDDL"
                  @change="validateField(`additional_costs.${index}.purchase_additional_cost_category_id`)"
                />
                <FormErrorMessages :messages="getFieldErrors(`additional_costs.${index}.purchase_additional_cost_category_id`)" />
              </div>
              <div class="col-span-12 md:col-span-3">
                <FormLabel :class="{ 'text-danger': invalidField(`additional_costs.${index}.paid_immediately_cash_account_id`) }">
                  {{ t('views.purchase.fields.paid_immediately_cash_account_id') }}
                </FormLabel>
                <FormSelectSearch
                  v-model="additionalCost.paid_immediately_cash_account_id"
                  v-model:search="cashAccountSearch"
                  :options="cashAccountOptions"
                  :placeholder="t('components.dropdown.placeholder')"
                  :class="{ 'border-danger': invalidField(`additional_costs.${index}.paid_immediately_cash_account_id`) }"
                  @search="loadCashAccountDDL"
                  @change="validateField(`additional_costs.${index}.paid_immediately_cash_account_id`)"
                />
                <FormErrorMessages :messages="getFieldErrors(`additional_costs.${index}.paid_immediately_cash_account_id`)" />
              </div>
              <div class="col-span-12 md:col-span-3">
                <FormLabel :class="{ 'text-danger': invalidField(`additional_costs.${index}.amount_paid_immediately`) }">
                  {{ t('views.purchase.fields.amount_paid_immediately') }}
                </FormLabel>
                <FormInputCurrency
                  v-model="additionalCost.amount_paid_immediately"
                  :allow-negative="false"
                  :class="{ 'border-danger': invalidField(`additional_costs.${index}.amount_paid_immediately`) }"
                  @change="
                    validateField(`additional_costs.${index}.amount_paid_immediately`);
                    syncDerivedFields();
                  "
                />
                <FormErrorMessages :messages="getFieldErrors(`additional_costs.${index}.amount_paid_immediately`)" />
              </div>
              <div class="col-span-12 md:col-span-3">
                <FormLabel :class="{ 'text-danger': invalidField(`additional_costs.${index}.amount_payable`) }">
                  {{ t('views.purchase.fields.amount_payable') }}
                </FormLabel>
                <FormInputCurrency
                  v-model="additionalCost.amount_payable"
                  :allow-negative="false"
                  :class="{ 'border-danger': invalidField(`additional_costs.${index}.amount_payable`) }"
                  @change="
                    validateField(`additional_costs.${index}.amount_payable`);
                    syncDerivedFields();
                  "
                />
                <FormErrorMessages :messages="getFieldErrors(`additional_costs.${index}.amount_payable`)" />
              </div>
              <div class="col-span-12 md:col-span-12">
                <FormLabel>{{ t('views.purchase.fields.remarks') }}</FormLabel>
                <FormTextarea
                  v-model="additionalCost.remarks"
                  @change="validateField(`additional_costs.${index}.remarks`)"
                />
                <FormErrorMessages :messages="getFieldErrors(`additional_costs.${index}.remarks`)" />
              </div>
            </div>
          </div>
        </div>
      </template>

      <template #card-items-4>
        <div class="space-y-4 p-5">
          <div class="rounded-md border border-slate-200/60 p-4 dark:border-darkmode-400">
            <div class="mb-3 flex items-center justify-between">
              <div class="font-medium">{{ t('views.purchase.fields.global_discounts') }}</div>
              <Button type="button" variant="outline-primary" size="sm" @click="addGlobalDiscount">
                <Lucide icon="Plus" class="mr-1 h-4 w-4" />
                {{ t('components.buttons.add') }}
              </Button>
            </div>
            <div v-if="purchaseGlobalDiscountsForm.length === 0" class="text-sm text-slate-500">
              {{ t('views.purchase.fields.global_discounts_empty') }}
            </div>
            <div
              v-for="(discount, index) in purchaseGlobalDiscountsForm"
              :key="`global-discount-${index}`"
              class="mb-3 grid grid-cols-12 gap-3 last:mb-0"
            >
              <div class="col-span-12 md:col-span-3">
                <FormLabel>{{ t('views.purchase.fields.sequence') }}</FormLabel>
                <FormInput :model-value="discount.sequence" readonly />
              </div>
              <div class="col-span-12 md:col-span-3">
                <FormLabel>{{ t('views.purchase.fields.discount_type') }}</FormLabel>
                <FormSelect v-model="discount.discount_type">
                  <option v-for="option in discountTypeOptions" :key="option.value" :value="option.value">
                    {{ option.label }}
                  </option>
                </FormSelect>
              </div>
              <div class="col-span-12 md:col-span-4">
                <FormLabel>{{ t('views.purchase.fields.discount_value') }}</FormLabel>
                <FormInputCurrency v-model="discount.discount_value" :allow-negative="false" />
              </div>
              <div class="col-span-12 md:col-span-2 flex items-end">
                <Button type="button" variant="outline-danger" class="w-full" @click="removeGlobalDiscount(index)">
                  <Lucide icon="Trash2" class="h-4 w-4" />
                </Button>
              </div>
            </div>
          </div>

          <div class="grid grid-cols-12 gap-4 rounded-md border border-slate-200/60 p-4 dark:border-darkmode-400">
            <div class="col-span-12 md:col-span-4">
              <div class="text-xs text-slate-500">{{ t('views.purchase.fields.items_subtotal_after_discount') }}</div>
              <div class="text-lg font-medium">{{ formatCurrency(getItemsSubtotalAfterDiscountPreview()) }}</div>
            </div>
            <div class="col-span-12 md:col-span-4">
              <div class="text-xs text-slate-500">{{ t('views.purchase.fields.global_discount_total') }}</div>
              <div class="text-lg font-medium">{{ formatCurrency(getPurchaseGlobalDiscountPreview()) }}</div>
            </div>
            <div class="col-span-12 md:col-span-4">
              <div class="text-xs text-slate-500">{{ t('views.purchase.fields.item_total_after_global_discount') }}</div>
              <div class="text-lg font-medium">{{ formatCurrency(getPurchaseItemTotalAfterGlobalDiscountPreview()) }}</div>
            </div>
            <div class="col-span-12 md:col-span-4">
              <div class="text-xs text-slate-500">{{ t('views.purchase.fields.vat_base') }}</div>
              <div class="text-lg font-medium">{{ formatCurrency(getPurchaseVatBasePreview()) }}</div>
            </div>
            <div class="col-span-12 md:col-span-4">
              <div class="text-xs text-slate-500">{{ t('views.purchase.fields.vat') }}</div>
              <div class="text-lg font-medium">{{ formatCurrency(getPurchaseVatPreview()) }}</div>
            </div>
            <div class="col-span-12 md:col-span-4">
              <div class="text-xs text-slate-500">{{ t('views.purchase.fields.additional_cost') }}</div>
              <div class="text-lg font-medium">{{ formatCurrency(getAdditionalCostsTotalPreview()) }}</div>
            </div>
            <div class="col-span-12 md:col-span-6">
              <FormLabel :class="{ 'text-danger': invalidField('rounding') }">
                {{ t('views.purchase.fields.rounding') }}
              </FormLabel>
              <FormInputCurrency
                v-model="purchaseForm.rounding"
                :class="{ 'border-danger': invalidField('rounding') }"
                @change="purchaseForm.validate('rounding')"
              />
              <FormErrorMessages :messages="purchaseForm.errors.rounding" />
            </div>
            <div class="col-span-12 md:col-span-6">
              <FormLabel>{{ t('views.purchase.fields.amount_payable_preview') }}</FormLabel>
              <FormInputCurrency :model-value="getPurchaseAmountPayablePreview()" readonly />
            </div>
          </div>
        </div>
      </template>

      <template #card-items-button>
        <div class="flex justify-end gap-2 p-5">
          <Button type="submit" variant="primary" class="w-32 shadow-md" :disabled="purchaseForm.validating || purchaseForm.hasErrors">
            <Lucide v-if="purchaseForm.validating" icon="Loader" class="mr-2 h-4 w-4 animate-spin" />
            <Lucide v-else icon="Save" class="mr-2 h-4 w-4" />
            {{ t('components.buttons.update') }}
          </Button>
        </div>
      </template>
    </TwoColumnsLayout>
  </form>

  <ProductUnitPickerDialog
    v-if="selectedUserLocation"
    v-model:search-text="productSearchText"
    :open="showProductUnitModal"
    :title="t('views.purchase.fields.product_unit_id')"
    :is-searching="isSearchingProductUnit"
    :options="productUnitOptions"
    :columns="productUnitDialogColumns"
    @close="showProductUnitModal = false"
    @search="searchProductUnits"
    @select="selectProductUnit($event as ProductUnitOption)"
  />
</template>
