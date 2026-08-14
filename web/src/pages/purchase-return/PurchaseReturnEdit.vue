<script setup lang="ts">
// #region Imports
import { computed, nextTick, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRoute, useRouter } from 'vue-router';
import { TwoColumnsLayout } from '@/components/Base/Form/FormLayout';
import { type TwoColumnsLayoutCards } from '@/components/Base/Form/FormLayout/TwoColumnsLayout.vue';
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
import ProductUnitPickerDialog from '@/components/Product/ProductUnitPickerDialog.vue';
import CashAccountService from '@/services/CashAccountService';
import ProductService from '@/services/ProductService';
import PurchaseInvoiceService from '@/services/PurchaseInvoiceService';
import PurchaseOrderReceiptService from '@/services/PurchaseOrderReceiptService';
import PurchaseReturnService from '@/services/PurchaseReturnService';
import SupplierService from '@/services/SupplierService';
import VatProfileService from '@/services/VatProfileService';
import WarehouseService from '@/services/WarehouseService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { CardState } from '@/types/enums/CardState';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { NotificationData } from '@/types/models/NotificationData';
import type { PurchaseInvoice } from '@/types/models/PurchaseInvoice';
import type { PurchaseReturn } from '@/types/models/PurchaseReturn';
import type {
  PurchaseReturnItemNestedUpdateRequest,
  PurchaseReturnRefundNestedUpdateRequest,
} from '@/types/services/purchase-return/PurchaseReturnRequest';
import { convertErrorTypeToAlertListType, formatDate } from '@/utils/helper';
// #endregion

// #region Declarations
type PurchaseInvoiceOption = DropDownOption & {
  supplier_id: string | null;
  supplier_name: string | null;
};

type PurchaseReturnItemFormItem = PurchaseReturnItemNestedUpdateRequest & {
  product_unit_product_code?: string | null;
  product_unit_product_name?: string | null;
  product_unit_product_image_url?: string | null;
  product_unit_unit_name?: string | null;
  product_unit_base_unit_name?: string | null;
  vat_profile_name?: string | null;
  is_use_serial_number?: boolean;
  receipt_item_label?: string | null;
  receipt_received_qty_base?: number | null;
};

type PurchaseReturnRefundFormItem = PurchaseReturnRefundNestedUpdateRequest;

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

type ReceiptItemOption = {
  purchase_order_receipt_item_id: string;
  product_unit_id: string;
  product_unit_code: string;
  product_name: string;
  product_image_url: string | null;
  unit_name: string;
  base_unit_name: string;
  conversion_value: number;
  price: number;
  received_qty_base: number;
  receipt_code: string;
  is_use_serial_number: boolean;
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
const route = useRoute();
const router = useRouter();
const selectedUserLocationStore = useSelectedUserLocationStore();

const purchaseReturnService = new PurchaseReturnService();
const purchaseOrderReceiptService = new PurchaseOrderReceiptService();
const purchaseInvoiceService = new PurchaseInvoiceService();
const supplierService = new SupplierService();
const warehouseService = new WarehouseService();
const cashAccountService = new CashAccountService();
const vatProfileService = new VatProfileService();
const productService = new ProductService();

const purchaseReturnForm = purchaseReturnService.usePurchaseReturnEditForm(route.params.ulid as string);

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const cards = ref<Array<TwoColumnsLayoutCards>>([
  { title: 'views.purchase_return.field_groups.company_info', state: CardState.Expanded },
  { title: 'views.purchase_return.field_groups.purchase_return_data', state: CardState.Expanded },
  { title: 'views.purchase_return.field_groups.items', state: CardState.Expanded },
  { title: 'views.purchase_return.field_groups.summary', state: CardState.Expanded },
  { title: '', state: CardState.Hidden, id: 'button' },
]);

const purchaseReturnData = ref<PurchaseReturn | null>(null);
const initializing = ref<boolean>(true);

const supplierDDL = ref<Array<DropDownOption>>([]);
const supplierSearch = ref<string>('');
const supplierOptions = computed(() =>
  supplierDDL.value.map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const purchaseInvoiceDDL = ref<Array<PurchaseInvoiceOption>>([]);
const purchaseInvoiceSearch = ref<string>('');
const purchaseInvoiceOptions = computed(() =>
  purchaseInvoiceDDL.value.map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const warehouseDDL = ref<Array<DropDownOption>>([]);
const warehouseSearch = ref<string>('');
const warehouseOptions = computed(() =>
  warehouseDDL.value.map((item) => ({
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

const vatProfileDDL = ref<Array<VatProfileOption>>([]);
const vatProfileSearch = ref<string>('');
const vatProfileOptions = computed(() =>
  vatProfileDDL.value.map((item) => ({
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

const showReceiptItemModal = ref<boolean>(false);
const receiptItemSearchText = ref<string>('');
const isSearchingReceiptItem = ref<boolean>(false);
const receiptItemOptions = ref<Array<ReceiptItemOption>>([]);
const editingReceiptItemIndex = ref<number | null>(null);

const isTotalsBreakdownExpanded = ref<boolean>(false);
const isRefundEditorExpanded = ref<boolean>(false);

const purchaseReturnItemsForm = computed<PurchaseReturnItemFormItem[]>(
  () => purchaseReturnForm.items as PurchaseReturnItemFormItem[],
);
const purchaseReturnRefundsForm = computed<PurchaseReturnRefundFormItem[]>(
  () => purchaseReturnForm.refunds as PurchaseReturnRefundFormItem[],
);

const isVatFree = computed(() => !purchaseReturnForm.purchase_invoice_id);

const productUnitDialogColumns = computed(() => [
  { key: 'unit_name', label: t('views.product.table.cols.unit') },
  {
    key: 'conversion_value',
    label: t('views.purchase_return.fields.product_unit_conversion_value'),
    align: 'right' as const,
    formatter: 'number' as const,
  },
  {
    key: 'price',
    label: t('views.purchase_return.fields.product_unit_price'),
    align: 'right' as const,
    formatter: 'number' as const,
  },
]);

const receiptItemDialogColumns = computed(() => [
  { key: 'receipt_code', label: t('views.purchase_return.fields.receipt_code') },
  { key: 'unit_name', label: t('views.product.table.cols.unit') },
  {
    key: 'received_qty_base',
    label: t('views.purchase_return.fields.received_qty'),
    align: 'right' as const,
    formatter: 'number' as const,
  },
]);
// #endregion

// #region Vue Core
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
    await Promise.all([
      loadSupplierDDL(),
      loadPurchaseInvoiceDDL(),
      loadWarehouseDDL(),
      loadCashAccountDDL(),
      loadVatProfileDDL(),
    ]);

    const loaded = await loadData();
    if (!loaded) return;

    await Promise.all([
      loadSupplierDDL(),
      loadPurchaseInvoiceDDL(),
      loadWarehouseDDL(),
      loadCashAccountDDL(),
      loadVatProfileDDL(),
    ]);

    initializing.value = false;
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
const invalidField = (field: string) => purchaseReturnForm.invalid(field as any);
const validateField = (field: string) => purchaseReturnForm.validate(field as any);
const getFieldErrors = (field: string) => (purchaseReturnForm.errors as Record<string, string | undefined>)[field];

const forgetErrorsWithPrefix = (prefix: string) => {
  Object.keys(purchaseReturnForm.errors).forEach((key) => {
    if (key.startsWith(prefix)) {
      purchaseReturnForm.forgetError(key as any);
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

const appendDropDownOption = (target: { value: Array<DropDownOption> }, option: DropDownOption | null | undefined) => {
  if (!option?.code) return;

  if (!target.value.some((item) => item.code === option.code)) {
    target.value = [...target.value, option];
  }
};

const appendPurchaseInvoiceOption = (purchaseInvoice: PurchaseInvoice | null | undefined) => {
  if (!purchaseInvoice?.id) return;

  if (!purchaseInvoiceDDL.value.some((item) => item.code === purchaseInvoice.id)) {
    purchaseInvoiceDDL.value = [
      ...purchaseInvoiceDDL.value,
      {
        code: purchaseInvoice.id,
        name: `${purchaseInvoice.code} - ${purchaseInvoice.supplier?.name ?? '-'}`,
        supplier_id: purchaseInvoice.supplier?.id ?? null,
        supplier_name: purchaseInvoice.supplier?.name ?? null,
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
// #endregion

// #region Methods - DDL
const loadSupplierDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await supplierService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    status: undefined,
    include_id: purchaseReturnForm.supplier_id ?? undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    supplierDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }

  if (purchaseReturnData.value?.supplier) {
    appendDropDownOption(supplierDDL, {
      code: purchaseReturnData.value.supplier.id,
      name: purchaseReturnData.value.supplier.name,
    });
  }
};

const loadPurchaseInvoiceDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await purchaseInvoiceService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    supplier_id: purchaseReturnForm.supplier_id ?? undefined,
    include_id: purchaseReturnForm.purchase_invoice_id ?? undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    purchaseInvoiceDDL.value = result.data.data.map((item) => ({
      code: item.id,
      name: `${item.code} - ${item.supplier?.name ?? '-'}`,
      supplier_id: item.supplier?.id ?? null,
      supplier_name: item.supplier?.name ?? null,
    }));
  } else {
    purchaseInvoiceDDL.value = [];
  }

  appendPurchaseInvoiceOption(purchaseReturnData.value?.purchase_invoice ?? null);
};

const loadWarehouseDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await warehouseService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    include_id: purchaseReturnForm.warehouse_id ?? undefined,
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

  if (purchaseReturnData.value?.warehouse) {
    appendDropDownOption(warehouseDDL, {
      code: purchaseReturnData.value.warehouse.id,
      name: purchaseReturnData.value.warehouse.name,
    });
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
      name: `${item.code} - ${item.name}`,
    }));
  }

  (purchaseReturnData.value?.refunds ?? []).forEach((refund) => {
    if (!refund.cash_account) return;
    appendDropDownOption(cashAccountDDL, {
      code: refund.cash_account.id,
      name: `${refund.cash_account.code} - ${refund.cash_account.name}`,
    });
  });
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

  purchaseReturnItemsForm.value.forEach((item) => {
    appendVatProfileOption({
      code: item.vat_profile_id ?? undefined,
      name: item.vat_profile_name ?? undefined,
      vat_rate: item.vat_rate,
      vat_base_numerator: item.vat_base_numerator,
      vat_base_denominator: item.vat_base_denominator,
    });
  });
};
// #endregion

// #region Methods - Load data
const loadData = async () => {
  const ulid = route.params.ulid as string | undefined;
  if (!ulid) return false;

  const result = await purchaseReturnService.read(ulid);

  if (!result.success || !result.data) {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
    return false;
  }

  const data = result.data;
  purchaseReturnData.value = data;

  const items: PurchaseReturnItemFormItem[] = (data.items ?? []).map((item: any) => {
    const conversionValue = Number(item.product_unit_conversion_value ?? 1) || 1;
    const product = item.product_unit?.product ?? item.product ?? null;
    const receiptItem = item.purchase_order_receipt_item ?? null;

    return {
      id: item.id ?? null,
      purchase_order_receipt_item_id: receiptItem?.id ?? null,
      qty: Number(item.qty ?? 0),
      product_unit_id: item.product_unit?.id ?? '',
      product_unit_conversion_value: conversionValue,
      product_unit_price: Number(item.product_unit_price ?? 0),
      product_unit_is_price_include_vat: Boolean(item.product_unit_is_price_include_vat),
      price_discount: Number(item.price_discount ?? 0),
      subtotal_discount: Number(item.subtotal_discount ?? 0),
      vat_profile_id: item.vat_profile?.id ?? null,
      vat_rate: Number(item.vat_rate ?? 0),
      vat_base_numerator: Number(item.vat_base_numerator ?? 1),
      vat_base_denominator: Number(item.vat_base_denominator ?? 1),
      remarks: item.remarks ?? '',
      delete_serial_ids: [],
      serials: (item.serials ?? []).map((serial: any) => ({
        id: serial.id ?? null,
        serial: serial.serial,
      })),
      product_unit_product_code: item.product_unit?.code ?? '',
      product_unit_product_name: product?.name ?? '-',
      product_unit_product_image_url: product?.main_product_image?.url ?? null,
      product_unit_unit_name: item.product_unit?.unit?.name ?? '',
      product_unit_base_unit_name: conversionValue !== 1 ? (product?.base_product_unit?.unit?.name ?? '') : '',
      vat_profile_name: item.vat_profile?.name ?? null,
      is_use_serial_number: Boolean(product?.is_use_serial_number),
      receipt_item_label: receiptItem
        ? `${receiptItem.purchase_order_receipt?.code ?? '-'} - [${receiptItem.product_unit?.code ?? ''}] ${
            product?.name ?? '-'
          }`
        : null,
      receipt_received_qty_base: receiptItem ? Number(receiptItem.product_unit_qty_base ?? 0) : null,
    };
  });

  const refunds: PurchaseReturnRefundFormItem[] = (data.refunds ?? []).map((refund: any) => ({
    id: refund.id ?? null,
    code: refund.code,
    date: formatDate(refund.date, 'YYYY-MM-DD HH:mm:ss'),
    cash_account_id: refund.cash_account?.id ?? null,
    amount: Number(refund.amount ?? 0),
    remarks: refund.remarks ?? '',
  }));

  purchaseReturnForm.setData({
    company_id: data.company?.id ?? '',
    branch_id: data.branch?.id ?? '',
    code: data.code,
    date: formatDate(data.date, 'YYYY-MM-DD HH:mm:ss'),
    supplier_id: data.supplier?.id ?? null,
    purchase_invoice_id: data.purchase_invoice?.id ?? null,
    warehouse_id: data.warehouse?.id ?? null,
    global_discount: Number(data.global_discount ?? 0),
    rounding: Number(data.rounding ?? 0),
    remarks: data.remarks ?? '',
    is_posted: Boolean(data.is_posted),
    delete_item_ids: [] as any,
    items: items as any,
    delete_refund_ids: [] as any,
    refunds: refunds as any,
  } as any);

  appendPurchaseInvoiceOption(data.purchase_invoice ?? null);
  isRefundEditorExpanded.value = refunds.length > 0;

  return true;
};
// #endregion

// #region Methods - Header
const setCode = () => {
  purchaseReturnForm.forgetError('code');
  purchaseReturnForm.setData({ code: purchaseReturnForm.code === '_AUTO_' ? '' : '_AUTO_' } as any);
};

const setRefundCode = (index: number) => {
  purchaseReturnForm.forgetError(`refunds.${index}.code` as any);
  const refund = purchaseReturnRefundsForm.value[index];
  if (!refund) return;
  refund.code = refund.code === '_AUTO_' ? '' : '_AUTO_';
};

const applyVatFreeToAllItems = () => {
  purchaseReturnItemsForm.value.forEach((item) => {
    item.vat_profile_id = null;
    item.vat_profile_name = null;
    item.vat_rate = 0;
    item.vat_base_numerator = 1;
    item.vat_base_denominator = 1;
  });
  forgetErrorsWithPrefix('items.');
};

const handleSupplierChanged = async () => {
  validateField('supplier_id');
  await loadPurchaseInvoiceDDL();
  receiptItemOptions.value = [];
};

const clearSupplier = async () => {
  purchaseReturnForm.setData({ supplier_id: null } as any);
  purchaseReturnForm.forgetError('supplier_id');
  receiptItemOptions.value = [];
  await loadPurchaseInvoiceDDL();
};

const handlePurchaseInvoiceChanged = async (purchaseInvoiceId: string | number | null) => {
  if (!purchaseInvoiceId) {
    applyVatFreeToAllItems();
    validateField('purchase_invoice_id');
    return;
  }

  const option = purchaseInvoiceDDL.value.find((item) => item.code === purchaseInvoiceId);
  if (option?.supplier_id) {
    purchaseReturnForm.setData({ supplier_id: option.supplier_id } as any);
    appendDropDownOption(supplierDDL, { code: option.supplier_id, name: option.supplier_name ?? option.supplier_id });
    purchaseReturnForm.forgetError('supplier_id');
    purchaseReturnForm.forgetError('branch_id');
  }

  validateField('purchase_invoice_id');
};

const clearPurchaseInvoice = async () => {
  purchaseReturnForm.setData({ purchase_invoice_id: null } as any);
  purchaseReturnForm.forgetError('purchase_invoice_id');
  applyVatFreeToAllItems();
  await loadPurchaseInvoiceDDL();
};

const clearWarehouse = () => {
  purchaseReturnForm.setData({ warehouse_id: null } as any);
  purchaseReturnForm.forgetError('warehouse_id');
};
// #endregion

// #region Methods - Item pickers
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

const searchReceiptItems = async () => {
  if (!selectedUserLocation.value) return;

  isSearchingReceiptItem.value = true;

  const result = await purchaseOrderReceiptService.readAnyPaginate({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search: receiptItemSearchText.value,
    supplier_id: purchaseReturnForm.supplier_id ?? undefined,
    warehouse_id: purchaseReturnForm.warehouse_id ?? undefined,
    refresh: true,
    page: 1,
    per_page: 25,
  });

  isSearchingReceiptItem.value = false;

  if (result.success && result.data) {
    receiptItemOptions.value = (result.data.data ?? []).flatMap((receipt) =>
      (receipt.items ?? []).map((receiptItem: any) => {
        const product = receiptItem.product_unit?.product ?? receiptItem.product ?? null;
        const units: any[] = product?.product_units ?? [];
        const baseUnitName = units.find((unit: any) => Number(unit.conversion_value ?? 1) === 1)?.unit?.name ?? '';

        return {
          purchase_order_receipt_item_id: receiptItem.id,
          product_unit_id: receiptItem.product_unit?.id ?? '',
          product_unit_code: receiptItem.product_unit?.code ?? '',
          product_name: product?.name ?? '-',
          product_image_url: product?.main_product_image?.url ?? null,
          unit_name: receiptItem.product_unit?.unit?.name ?? '',
          base_unit_name: baseUnitName,
          conversion_value: Number(receiptItem.product_unit_conversion_value ?? 1) || 1,
          price: Number(receiptItem.product_unit?.price ?? 0),
          received_qty_base: Number(receiptItem.product_unit_qty_base ?? 0),
          receipt_code: receipt.code ?? '-',
          is_use_serial_number: Boolean(product?.is_use_serial_number),
        };
      }),
    );
  } else {
    receiptItemOptions.value = [];
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

const openAddReceiptItem = () => {
  receiptItemSearchText.value = '';
  receiptItemOptions.value = [];
  editingReceiptItemIndex.value = null;
  showReceiptItemModal.value = true;
};

const openChangeReceiptItem = (index: number) => {
  receiptItemSearchText.value = '';
  receiptItemOptions.value = [];
  editingReceiptItemIndex.value = index;
  showReceiptItemModal.value = true;
};

const buildItemFromProductUnit = (option: ProductUnitOption): PurchaseReturnItemFormItem => {
  const useVat = !isVatFree.value && Boolean(option.vat_profile_id);

  if (useVat) {
    appendVatProfileOption({
      code: option.vat_profile_id ?? undefined,
      name: option.vat_profile_name ?? undefined,
      vat_rate: option.vat_rate,
      vat_base_numerator: option.vat_base_numerator,
      vat_base_denominator: option.vat_base_denominator,
    });
  }

  return {
    id: null,
    purchase_order_receipt_item_id: null,
    qty: 1,
    product_unit_id: option.product_unit_id,
    product_unit_conversion_value: option.conversion_value,
    product_unit_price: option.price,
    product_unit_is_price_include_vat: option.product_unit_is_price_include_vat,
    price_discount: 0,
    subtotal_discount: 0,
    vat_profile_id: useVat ? option.vat_profile_id : null,
    vat_rate: useVat ? option.vat_rate : 0,
    vat_base_numerator: useVat ? option.vat_base_numerator : 1,
    vat_base_denominator: useVat ? option.vat_base_denominator : 1,
    remarks: '',
    delete_serial_ids: [],
    serials: [],
    product_unit_product_code: option.product_unit_code,
    product_unit_product_name: option.product_name,
    product_unit_product_image_url: option.product_image_url,
    product_unit_unit_name: option.unit_name,
    product_unit_base_unit_name: option.base_unit_name,
    vat_profile_name: useVat ? option.vat_profile_name : null,
    is_use_serial_number: option.is_use_serial_number,
    receipt_item_label: null,
    receipt_received_qty_base: null,
  };
};

const buildItemFromReceiptItem = (option: ReceiptItemOption): PurchaseReturnItemFormItem => {
  const conversionValue = option.conversion_value || 1;
  const qty = option.received_qty_base > 0 ? option.received_qty_base / conversionValue : 0;

  return {
    id: null,
    purchase_order_receipt_item_id: option.purchase_order_receipt_item_id,
    qty,
    product_unit_id: option.product_unit_id,
    product_unit_conversion_value: conversionValue,
    product_unit_price: option.price,
    product_unit_is_price_include_vat: false,
    price_discount: 0,
    subtotal_discount: 0,
    vat_profile_id: null,
    vat_rate: 0,
    vat_base_numerator: 1,
    vat_base_denominator: 1,
    remarks: '',
    delete_serial_ids: [],
    serials: [],
    product_unit_product_code: option.product_unit_code,
    product_unit_product_name: option.product_name,
    product_unit_product_image_url: option.product_image_url,
    product_unit_unit_name: option.unit_name,
    product_unit_base_unit_name: option.base_unit_name,
    vat_profile_name: null,
    is_use_serial_number: option.is_use_serial_number,
    receipt_item_label: `${option.receipt_code} - [${option.product_unit_code}] ${option.product_name}`,
    receipt_received_qty_base: option.received_qty_base,
  };
};

const selectProductUnit = (option: ProductUnitOption) => {
  const itemData = buildItemFromProductUnit(option);
  let targetIndex: number;

  if (editingProductUnitIndex.value === null) {
    purchaseReturnForm.items.push(itemData as any);
    targetIndex = purchaseReturnForm.items.length - 1;
  } else {
    const currentItem = purchaseReturnItemsForm.value[editingProductUnitIndex.value];
    purchaseReturnItemsForm.value[editingProductUnitIndex.value] = {
      ...currentItem,
      ...itemData,
      id: currentItem?.id ?? null,
      qty: currentItem?.qty ?? 1,
      remarks: currentItem?.remarks ?? '',
      delete_serial_ids: currentItem?.delete_serial_ids ?? [],
      serials: itemData.is_use_serial_number ? (currentItem?.serials ?? []) : [],
      purchase_order_receipt_item_id: currentItem?.purchase_order_receipt_item_id ?? null,
      receipt_item_label: currentItem?.receipt_item_label ?? null,
      receipt_received_qty_base: currentItem?.receipt_received_qty_base ?? null,
    };
    targetIndex = editingProductUnitIndex.value;
  }

  showProductUnitModal.value = false;
  editingProductUnitIndex.value = null;
  productUnitQtyToFocus.value = targetIndex;
  forgetErrorsWithPrefix('items.');
};

const selectReceiptItem = (option: ReceiptItemOption) => {
  const itemData = buildItemFromReceiptItem(option);
  let targetIndex: number;

  if (editingReceiptItemIndex.value === null) {
    purchaseReturnForm.items.push(itemData as any);
    targetIndex = purchaseReturnForm.items.length - 1;
  } else {
    const currentItem = purchaseReturnItemsForm.value[editingReceiptItemIndex.value];
    purchaseReturnItemsForm.value[editingReceiptItemIndex.value] = {
      ...currentItem,
      ...itemData,
      id: currentItem?.id ?? null,
      remarks: currentItem?.remarks ?? '',
      delete_serial_ids: currentItem?.delete_serial_ids ?? [],
      serials: itemData.is_use_serial_number ? (currentItem?.serials ?? []) : [],
    };
    targetIndex = editingReceiptItemIndex.value;
  }

  showReceiptItemModal.value = false;
  editingReceiptItemIndex.value = null;
  productUnitQtyToFocus.value = targetIndex;
  forgetErrorsWithPrefix('items.');
};

const handleProductUnitModalAfterLeave = () => {
  const index = productUnitQtyToFocus.value;
  productUnitQtyToFocus.value = null;
  if (index === null) return;

  nextTick(() => {
    const el = document.getElementById(`purchase-return-item-qty-${index}`) as HTMLInputElement | null;
    el?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    el?.focus();
    el?.select();
  });
};

const removeItem = (index: number) => {
  const item = purchaseReturnItemsForm.value[index];
  if (item?.id) purchaseReturnForm.delete_item_ids.push(item.id);
  purchaseReturnItemsForm.value.splice(index, 1);
  forgetErrorsWithPrefix('items.');
};

const unlinkReceiptItem = (index: number) => {
  const item = purchaseReturnItemsForm.value[index];
  if (!item) return;
  item.purchase_order_receipt_item_id = null;
  item.receipt_item_label = null;
  item.receipt_received_qty_base = null;
  forgetErrorsWithPrefix(`items.${index}.`);
};

const applyVatProfileToItem = (item: PurchaseReturnItemFormItem, vatProfileId: string | null) => {
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
  const item = purchaseReturnItemsForm.value[index];
  if (!item) return;
  applyVatProfileToItem(item, isVatFree.value ? null : (item.vat_profile_id ?? null));
  validateField(`items.${index}.vat_profile_id`);
};

const clearVatProfile = (index: number) => {
  const item = purchaseReturnItemsForm.value[index];
  if (!item) return;
  applyVatProfileToItem(item, null);
  validateField(`items.${index}.vat_profile_id`);
};

const addSerial = (index: number) => {
  const item = purchaseReturnItemsForm.value[index];
  if (!item) return;
  item.serials.push({ id: null, serial: '' });
  validateField(`items.${index}.serials`);
};

const removeSerial = (index: number, serialIndex: number) => {
  const item = purchaseReturnItemsForm.value[index];
  if (!item) return;

  const serial = item.serials[serialIndex];
  if (serial?.id) item.delete_serial_ids.push(serial.id);

  item.serials.splice(serialIndex, 1);
  validateField(`items.${index}.serials`);
};

const getItemBaseQty = (item: PurchaseReturnItemFormItem) =>
  Math.max(Number(item.qty || 0), 0) * (Number(item.product_unit_conversion_value || 1) || 1);
// #endregion

// #region Methods - Refunds
const addRefund = () => {
  isRefundEditorExpanded.value = true;
  purchaseReturnForm.refunds.push({
    id: null,
    code: '_AUTO_',
    date: '_AUTO_',
    cash_account_id: null,
    amount: 0,
    remarks: '',
  } as any);
};

const removeRefund = (index: number) => {
  const refund = purchaseReturnRefundsForm.value[index];
  if (refund?.id) purchaseReturnForm.delete_refund_ids.push(refund.id);
  purchaseReturnRefundsForm.value.splice(index, 1);
  forgetErrorsWithPrefix('refunds.');
};
// #endregion

// #region Methods - Money previews
const getItemPriceAfterDiscountPreview = (item: PurchaseReturnItemFormItem) => {
  const price = Math.max(Number(item.product_unit_price || 0), 0);
  const priceDiscount = Math.min(Math.max(Number(item.price_discount || 0), 0), price);
  return Math.max(price - priceDiscount, 0);
};

const getItemSubtotalPreview = (item: PurchaseReturnItemFormItem) =>
  Math.max(Number(item.qty || 0), 0) * getItemPriceAfterDiscountPreview(item);

const getItemSubtotalAfterDiscountPreview = (item: PurchaseReturnItemFormItem) => {
  const subtotal = getItemSubtotalPreview(item);
  const subtotalDiscount = Math.min(Math.max(Number(item.subtotal_discount || 0), 0), subtotal);
  return Math.max(subtotal - subtotalDiscount, 0);
};

const getItemsSubtotalAfterDiscountPreview = () =>
  purchaseReturnItemsForm.value.reduce((total, item) => total + getItemSubtotalAfterDiscountPreview(item), 0);

const getGlobalDiscountPreview = () =>
  Math.min(Math.max(Number(purchaseReturnForm.global_discount || 0), 0), getItemsSubtotalAfterDiscountPreview());

const getItemGlobalDiscountPreview = (item: PurchaseReturnItemFormItem, itemIndex: number) => {
  const totalBeforeGlobalDiscount = getItemsSubtotalAfterDiscountPreview();
  const totalGlobalDiscount = getGlobalDiscountPreview();

  if (totalBeforeGlobalDiscount <= 0 || totalGlobalDiscount <= 0) return 0;

  const allocations = purchaseReturnItemsForm.value.map((currentItem, index) => {
    if (index === purchaseReturnItemsForm.value.length - 1) return 0;
    return totalGlobalDiscount * (getItemSubtotalAfterDiscountPreview(currentItem) / totalBeforeGlobalDiscount);
  });

  const allocatedBeforeCurrent = allocations.slice(0, itemIndex).reduce((total, allocation) => total + allocation, 0);

  if (itemIndex === purchaseReturnItemsForm.value.length - 1) {
    return Math.max(totalGlobalDiscount - allocatedBeforeCurrent, 0);
  }

  return Math.min(Math.max(allocations[itemIndex] || 0, 0), getItemSubtotalAfterDiscountPreview(item));
};

const getItemSubtotalAfterGlobalDiscountPreview = (item: PurchaseReturnItemFormItem, itemIndex: number) =>
  Math.max(getItemSubtotalAfterDiscountPreview(item) - getItemGlobalDiscountPreview(item, itemIndex), 0);

const getItemVatBasePreview = (item: PurchaseReturnItemFormItem, itemIndex: number) => {
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

const getItemVatPreview = (item: PurchaseReturnItemFormItem, itemIndex: number) => {
  const vatBase = getItemVatBasePreview(item, itemIndex);
  const vatRate = Number(item.vat_rate || 0);
  if (vatBase <= 0 || vatRate <= 0) return 0;
  return vatBase * (vatRate / 100);
};

const getItemTotalBeforeRoundingPreview = (item: PurchaseReturnItemFormItem, itemIndex: number) => {
  const subtotalAfterGlobalDiscount = getItemSubtotalAfterGlobalDiscountPreview(item, itemIndex);
  if (item.product_unit_is_price_include_vat) return subtotalAfterGlobalDiscount;
  return subtotalAfterGlobalDiscount + getItemVatPreview(item, itemIndex);
};

const getItemAmountPayablePreview = (item: PurchaseReturnItemFormItem) => {
  const itemIndex = purchaseReturnItemsForm.value.indexOf(item);
  if (itemIndex < 0) return 0;
  return getItemTotalBeforeRoundingPreview(item, itemIndex);
};

const getItemTotalAfterGlobalDiscountPreview = () =>
  purchaseReturnItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemSubtotalAfterGlobalDiscountPreview(item, itemIndex),
    0,
  );

const getVatBasePreview = () =>
  purchaseReturnItemsForm.value.reduce((total, item, itemIndex) => total + getItemVatBasePreview(item, itemIndex), 0);

const getVatPreview = () =>
  purchaseReturnItemsForm.value.reduce((total, item, itemIndex) => total + getItemVatPreview(item, itemIndex), 0);

const getAmountPayablePreview = () =>
  purchaseReturnItemsForm.value.reduce(
    (total, item, itemIndex) => total + getItemTotalBeforeRoundingPreview(item, itemIndex),
    0,
  ) + Number(purchaseReturnForm.rounding || 0);

const getRefundsTotalPreview = () =>
  purchaseReturnRefundsForm.value.reduce((total, refund) => total + Math.max(Number(refund.amount || 0), 0), 0);

/** Refunds may only take what is not already allocated to a purchase invoice. */
const getRefundCapPreview = () =>
  Math.max(getAmountPayablePreview() - Number(purchaseReturnData.value?.amount_allocated_to_invoice ?? 0), 0);

const getAmountAvailablePreview = () => Math.max(getRefundCapPreview() - getRefundsTotalPreview(), 0);

const clampRefundAmount = (index: number) => {
  const refund = purchaseReturnRefundsForm.value[index];
  if (!refund) return;

  const cap = getRefundCapPreview();
  const otherTotal = purchaseReturnRefundsForm.value.reduce(
    (total, current, currentIndex) =>
      currentIndex === index ? total : total + Math.max(Number(current.amount || 0), 0),
    0,
  );
  const allowed = Math.max(cap - otherTotal, 0);

  if (Number(refund.amount || 0) > allowed) {
    refund.amount = Number(allowed.toFixed(2));
  }

  validateField(`refunds.${index}.amount`);
};

const formatCurrencyPreviewValue = (value: number) => Number(value.toFixed(2));
const formatQuantityValue = (value: number | string | null | undefined, precision = 4) =>
  new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: precision,
  }).format(Number(value ?? 0));
// #endregion

// #region Actions
const onSubmit = async () => {
  if (purchaseReturnForm.hasErrors) {
    const firstErrorKey = Object.keys(purchaseReturnForm.errors)[0];
    if (firstErrorKey) scrollToError(firstErrorKey);
    return;
  }

  const backupItems = [...purchaseReturnItemsForm.value];
  const backupRefunds = [...purchaseReturnRefundsForm.value];

  const cleanedItems: PurchaseReturnItemNestedUpdateRequest[] = purchaseReturnItemsForm.value.map((item) => ({
    id: item.id ?? null,
    purchase_order_receipt_item_id: item.purchase_order_receipt_item_id ?? null,
    qty: Number(item.qty ?? 0),
    product_unit_id: item.product_unit_id,
    product_unit_conversion_value: Number(item.product_unit_conversion_value ?? 1),
    product_unit_price: Number(item.product_unit_price ?? 0),
    product_unit_is_price_include_vat: Boolean(item.product_unit_is_price_include_vat),
    price_discount: Number(item.price_discount ?? 0),
    subtotal_discount: Number(item.subtotal_discount ?? 0),
    vat_profile_id: isVatFree.value ? null : (item.vat_profile_id ?? null),
    vat_rate: isVatFree.value ? 0 : Number(item.vat_rate ?? 0),
    vat_base_numerator: isVatFree.value ? 1 : Number(item.vat_base_numerator ?? 1),
    vat_base_denominator: isVatFree.value ? 1 : Number(item.vat_base_denominator ?? 1),
    remarks: item.remarks ?? '',
    delete_serial_ids: [...(item.delete_serial_ids ?? [])],
    serials: (item.serials ?? []).map((serial) => ({ id: serial.id ?? null, serial: serial.serial })),
  }));

  const cleanedRefunds: PurchaseReturnRefundNestedUpdateRequest[] = purchaseReturnRefundsForm.value.map((refund) => ({
    id: refund.id ?? null,
    code: refund.code,
    date: refund.date,
    cash_account_id: refund.cash_account_id ?? null,
    amount: Number(refund.amount ?? 0),
    remarks: refund.remarks ?? '',
  }));

  purchaseReturnForm.items = cleanedItems as any;
  purchaseReturnForm.refunds = cleanedRefunds as any;

  emits('loading-state', true);

  try {
    await purchaseReturnForm.submit();
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(t('views.purchase_return.alert.update.title'), t('views.purchase_return.alert.update.message'));
    router.push({ name: 'side-menu-purchase-return-list' });
  } catch (error) {
    purchaseReturnForm.items = backupItems as any;
    purchaseReturnForm.refunds = backupRefunds as any;
    showAlertPlaceholder('danger', '', convertErrorTypeToAlertListType(error));
  } finally {
    emits('loading-state', false);
  }
};
// #endregion
</script>

<template>
  <form v-if="selectedUserLocation && !initializing" id="purchaseReturnForm" @submit.prevent="onSubmit">
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
              <FormInput v-model="purchaseReturnForm.company_id" type="hidden" />
            </div>
            <div class="col-span-12 md:col-span-6 lg:col-span-4">
              <FormLabel>
                {{ selectedUserLocation.branch.code }}
                <br />
                {{ selectedUserLocation.branch.name }}
              </FormLabel>
              <FormInput v-model="purchaseReturnForm.branch_id" type="hidden" />
            </div>
          </div>
        </div>
      </template>

      <!-- card: return header -->
      <template #card-items-1>
        <div class="p-5">
          <div class="grid grid-cols-12 gap-4 gap-y-3">
            <div class="col-span-12 md:col-span-6 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': invalidField('code') }">
                {{ t('views.purchase_return.fields.code') }}
              </FormLabel>
              <FormInputCode
                v-model="purchaseReturnForm.code"
                :class="{ 'border-danger': invalidField('code') }"
                :placeholder="t('views.purchase_return.fields.code')"
                @set-auto="setCode"
                @change="validateField('code')"
              />
              <FormErrorMessages :messages="purchaseReturnForm.errors.code" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': invalidField('date') }">
                {{ t('views.purchase_return.fields.date') }}
              </FormLabel>
              <FormInputDateTimeAuto
                v-model="purchaseReturnForm.date"
                :class="{ 'border-danger': invalidField('date') }"
                :placeholder="t('views.purchase_return.fields.date')"
                @change="validateField('date')"
              />
              <FormErrorMessages :messages="purchaseReturnForm.errors.date" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': invalidField('supplier_id') }">
                {{ t('views.purchase_return.fields.supplier_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="purchaseReturnForm.supplier_id"
                v-model:search="supplierSearch"
                :options="supplierOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :disabled="Boolean(purchaseReturnForm.purchase_invoice_id)"
                :class="{ 'border-danger': invalidField('supplier_id') }"
                @change="handleSupplierChanged"
                @search="loadSupplierDDL"
                @clear="clearSupplier"
              />
              <FormErrorMessages :messages="purchaseReturnForm.errors.supplier_id" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': invalidField('purchase_invoice_id') }">
                {{ t('views.purchase_return.fields.purchase_invoice_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="purchaseReturnForm.purchase_invoice_id"
                v-model:search="purchaseInvoiceSearch"
                :options="purchaseInvoiceOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': invalidField('purchase_invoice_id') }"
                @change="(value) => handlePurchaseInvoiceChanged(value as string | number | null)"
                @search="loadPurchaseInvoiceDDL"
                @clear="clearPurchaseInvoice"
              />
              <FormErrorMessages :messages="purchaseReturnForm.errors.purchase_invoice_id" />
            </div>

            <div class="col-span-12 md:col-span-6 lg:col-span-3">
              <FormLabel :class="{ 'text-danger': invalidField('warehouse_id') }">
                {{ t('views.purchase_return.fields.warehouse_id') }}
              </FormLabel>
              <FormSelectSearch
                v-model="purchaseReturnForm.warehouse_id"
                v-model:search="warehouseSearch"
                :options="warehouseOptions"
                :placeholder="t('components.dropdown.placeholder')"
                :class="{ 'border-danger': invalidField('warehouse_id') }"
                @change="validateField('warehouse_id')"
                @search="loadWarehouseDDL"
                @clear="clearWarehouse"
              />
              <FormErrorMessages :messages="purchaseReturnForm.errors.warehouse_id" />
            </div>

            <div class="col-span-12 flex flex-col justify-center md:col-span-6 lg:col-span-2">
              <FormLabel :class="{ 'text-danger': invalidField('is_posted') }">
                {{ t('views.purchase_return.fields.is_posted') }}
              </FormLabel>
              <FormSwitch>
                <FormSwitch.Input v-model="purchaseReturnForm.is_posted" type="checkbox" />
              </FormSwitch>
              <FormErrorMessages :messages="purchaseReturnForm.errors.is_posted" />
            </div>

            <div class="col-span-12">
              <FormLabel :class="{ 'text-danger': invalidField('remarks') }">
                {{ t('views.purchase_return.fields.remarks') }}
              </FormLabel>
              <FormTextarea
                v-model="purchaseReturnForm.remarks"
                :class="{ 'border-danger': invalidField('remarks') }"
                :placeholder="t('views.purchase_return.fields.remarks')"
                @change="validateField('remarks')"
              />
              <FormErrorMessages :messages="purchaseReturnForm.errors.remarks" />
            </div>
          </div>

          <div
            v-if="isVatFree"
            class="mt-4 rounded-md border border-warning/30 bg-warning/10 px-4 py-3 text-sm text-slate-700 dark:text-slate-200"
          >
            {{ t('views.purchase_return.fields.vat_free_hint') }}
          </div>
          <div
            v-else
            class="mt-4 rounded-md border border-primary/20 bg-primary/5 px-4 py-3 text-sm text-slate-700 dark:text-slate-200"
          >
            {{ t('views.purchase_return.fields.purchase_invoice_link_hint') }}
          </div>
        </div>
      </template>

      <!-- card: items -->
      <template #card-items-2>
        <div class="space-y-4 p-5">
          <div class="flex flex-wrap items-center justify-between gap-2">
            <div class="text-sm text-slate-500">
              {{ t('views.purchase_return.fields.item_count') }}: {{ purchaseReturnItemsForm.length }}
            </div>
            <div class="flex flex-wrap items-center gap-2">
              <Button
                type="button"
                variant="outline-secondary"
                :disabled="!purchaseReturnForm.supplier_id"
                @click="openAddReceiptItem"
              >
                <Lucide icon="PackageSearch" class="mr-1 h-4 w-4" />
                {{ t('views.purchase_return.fields.purchase_order_receipt_item_id') }}
              </Button>
              <Button type="button" variant="outline-primary" @click="openAddProductUnit">
                <Lucide icon="Plus" class="mr-1 h-4 w-4" />
                {{ t('components.buttons.add') }}
              </Button>
            </div>
          </div>

          <div v-if="!purchaseReturnForm.supplier_id" class="text-xs italic text-slate-500">
            {{ t('views.purchase_return.fields.supplier_required_hint') }}
          </div>

          <FormErrorMessages :messages="purchaseReturnForm.errors.items" />

          <div
            v-if="purchaseReturnItemsForm.length === 0"
            class="rounded-md border border-dashed border-slate-300 px-4 py-6 text-center text-sm text-slate-500 dark:border-darkmode-400"
          >
            {{ t('views.purchase_return.fields.items_empty') }}
          </div>

          <div
            v-for="(item, index) in purchaseReturnItemsForm"
            :key="`return-item-${item.id ?? item.product_unit_id}-${index}`"
            class="rounded-md border border-slate-200/70 p-4 dark:border-darkmode-400"
          >
            <div class="flex flex-wrap items-start justify-between gap-3">
              <div class="flex items-start gap-3">
                <ProductImagePreview
                  :image-url="item.product_unit_product_image_url"
                  wrapper-class="w-14 h-14 rounded-md overflow-hidden bg-slate-100 dark:bg-darkmode-600 flex items-center justify-center cursor-zoom-in shrink-0"
                  icon-class="w-5 h-5 text-slate-400"
                  :preview-title="item.product_unit_product_name || t('views.purchase_return.fields.product_unit_id')"
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
                  <div v-if="item.receipt_item_label" class="mt-1 text-xs text-primary">
                    {{ item.receipt_item_label }}
                  </div>
                  <div v-else class="mt-1 text-xs italic text-slate-500">
                    {{ t('views.purchase_return.fields.receipt_item_unlinked') }}
                  </div>
                  <div v-if="item.receipt_received_qty_base !== null" class="mt-1 text-xs text-slate-500">
                    {{ t('views.purchase_return.fields.received_qty') }}:
                    {{ formatQuantityValue(item.receipt_received_qty_base) }}
                    ({{ t('views.purchase_return.fields.product_unit_qty_base') }}:
                    {{ formatQuantityValue(getItemBaseQty(item)) }})
                  </div>
                </div>
              </div>
              <div class="flex items-center gap-2">
                <Button type="button" size="sm" variant="outline-secondary" @click="openChangeReceiptItem(index)">
                  <Lucide icon="PackageSearch" class="h-4 w-4" />
                </Button>
                <Button
                  v-if="item.purchase_order_receipt_item_id"
                  type="button"
                  size="sm"
                  variant="outline-secondary"
                  @click="unlinkReceiptItem(index)"
                >
                  <Lucide icon="Unlink" class="h-4 w-4" />
                </Button>
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
                  {{ t('views.purchase_return.fields.qty') }}
                </FormLabel>
                <FormInputCurrency
                  :id="`purchase-return-item-qty-${index}`"
                  v-model="item.qty"
                  :allow-negative="false"
                  :class="{
                    'border-danger':
                      invalidField(`items.${index}.qty`) ||
                      (item.receipt_received_qty_base !== null &&
                        getItemBaseQty(item) > (item.receipt_received_qty_base ?? 0)),
                  }"
                  @change="validateField(`items.${index}.qty`)"
                />
                <FormErrorMessages :messages="getFieldErrors(`items.${index}.qty`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel>{{ t('views.purchase_return.fields.product_unit_conversion_value') }}</FormLabel>
                <FormInputCurrency :model-value="item.product_unit_conversion_value" readonly />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel :class="{ 'text-danger': invalidField(`items.${index}.product_unit_price`) }">
                  {{ t('views.purchase_return.fields.product_unit_price') }}
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
                  {{ t('views.purchase_return.fields.price_discount') }}
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
                  {{ t('views.purchase_return.fields.subtotal_discount') }}
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
                <FormLabel>{{ t('views.purchase_return.fields.subtotal_after_discount') }}</FormLabel>
                <FormInputCurrency
                  :model-value="formatCurrencyPreviewValue(getItemSubtotalAfterDiscountPreview(item))"
                  readonly
                />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-3">
                <FormLabel :class="{ 'text-danger': invalidField(`items.${index}.vat_profile_id`) }">
                  {{ t('views.purchase_return.fields.vat_profile_id') }}
                </FormLabel>
                <FormSelectSearch
                  v-model="item.vat_profile_id"
                  v-model:search="vatProfileSearch"
                  :options="vatProfileOptions"
                  :placeholder="t('components.dropdown.placeholder')"
                  :disabled="isVatFree"
                  :class="{ 'border-danger': invalidField(`items.${index}.vat_profile_id`) }"
                  @change="syncVatProfile(index)"
                  @search="loadVatProfileDDL"
                  @clear="clearVatProfile(index)"
                />
                <FormErrorMessages :messages="getFieldErrors(`items.${index}.vat_profile_id`)" />
                <FormErrorMessages :messages="getFieldErrors(`items.${index}.vat_rate`)" />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel>{{ t('views.purchase_return.fields.vat_rate') }}</FormLabel>
                <FormInputCurrency :model-value="item.vat_rate" readonly />
              </div>

              <div class="col-span-12 flex flex-col justify-center md:col-span-6 lg:col-span-2">
                <FormLabel>{{ t('views.purchase_return.fields.product_unit_is_price_include_vat') }}</FormLabel>
                <FormSwitch>
                  <FormSwitch.Input
                    v-model="item.product_unit_is_price_include_vat"
                    type="checkbox"
                    :disabled="isVatFree"
                    @change="validateField(`items.${index}.product_unit_is_price_include_vat`)"
                  />
                </FormSwitch>
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-2">
                <FormLabel>{{ t('views.purchase_return.fields.vat') }}</FormLabel>
                <FormInputCurrency :model-value="formatCurrencyPreviewValue(getItemVatPreview(item, index))" readonly />
              </div>

              <div class="col-span-12 md:col-span-6 lg:col-span-3">
                <FormLabel>{{ t('views.purchase_return.fields.item_amount_payable') }}</FormLabel>
                <FormInputCurrency
                  :model-value="formatCurrencyPreviewValue(getItemAmountPayablePreview(item))"
                  readonly
                />
              </div>

              <div class="col-span-12">
                <FormLabel :class="{ 'text-danger': invalidField(`items.${index}.remarks`) }">
                  {{ t('views.purchase_return.fields.remarks') }}
                </FormLabel>
                <FormTextarea
                  v-model="item.remarks"
                  :class="{ 'border-danger': invalidField(`items.${index}.remarks`) }"
                  :placeholder="t('views.purchase_return.fields.remarks')"
                  @change="validateField(`items.${index}.remarks`)"
                />
                <FormErrorMessages :messages="getFieldErrors(`items.${index}.remarks`)" />
                <FormErrorMessages :messages="getFieldErrors(`items.${index}.product_unit_id`)" />
                <FormErrorMessages :messages="getFieldErrors(`items.${index}.purchase_order_receipt_item_id`)" />
              </div>

              <!-- serial numbers for serial-tracked products -->
              <div v-if="item.is_use_serial_number" class="col-span-12">
                <div class="mb-2 flex items-center justify-between">
                  <FormLabel :class="{ 'text-danger': invalidField(`items.${index}.serials`) }">
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
                  <div
                    v-for="(serial, serialIndex) in item.serials"
                    :key="`${index}-${serialIndex}`"
                    class="flex gap-2"
                  >
                    <FormInput
                      v-model="serial.serial"
                      :placeholder="t('views.product.fields.serial_number')"
                      :class="{ 'border-danger': invalidField(`items.${index}.serials.${serialIndex}.serial`) }"
                      @change="
                        validateField(`items.${index}.serials.${serialIndex}.serial`);
                        validateField(`items.${index}.serials`);
                      "
                    />
                    <Button type="button" variant="outline-secondary" @click="removeSerial(index, serialIndex)">
                      <Lucide icon="Trash2" class="h-4 w-4 text-danger" />
                    </Button>
                  </div>
                </div>

                <FormErrorMessages :messages="getFieldErrors(`items.${index}.serials`)" />
                <FormErrorMessages
                  v-for="(_, serialIndex) in item.serials"
                  :key="`serial-error-${index}-${serialIndex}`"
                  :messages="getFieldErrors(`items.${index}.serials.${serialIndex}.serial`)"
                />
              </div>
            </div>
          </div>
        </div>
      </template>

      <!-- card: summary and refunds -->
      <template #card-items-3>
        <div class="space-y-4 p-5">
          <div class="space-y-4 rounded-md border border-slate-200/60 p-4 dark:border-darkmode-400">
            <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
              <div class="col-span-12 lg:col-span-9"></div>
              <div class="col-span-12 lg:col-span-3">
                <FormLabel>{{ t('views.purchase_return.fields.items_subtotal_after_discount') }}</FormLabel>
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
                  {{ t('views.purchase_return.fields.global_discount') }}
                </FormLabel>
                <FormInputCurrency
                  v-model="purchaseReturnForm.global_discount"
                  :allow-negative="false"
                  :class="{ 'border-danger': invalidField('global_discount') }"
                  @change="validateField('global_discount')"
                />
                <FormErrorMessages :messages="purchaseReturnForm.errors.global_discount" />
              </div>
            </div>

            <template v-if="isTotalsBreakdownExpanded">
              <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.purchase_return.fields.item_total_after_global_discount') }}</FormLabel>
                  <FormInputCurrency
                    :model-value="formatCurrencyPreviewValue(getItemTotalAfterGlobalDiscountPreview())"
                    readonly
                  />
                </div>
              </div>

              <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.purchase_return.fields.vat_base') }}</FormLabel>
                  <FormInputCurrency :model-value="formatCurrencyPreviewValue(getVatBasePreview())" readonly />
                </div>
              </div>

              <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.purchase_return.fields.vat') }}</FormLabel>
                  <FormInputCurrency :model-value="formatCurrencyPreviewValue(getVatPreview())" readonly />
                </div>
              </div>

              <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel :class="{ 'text-danger': invalidField('rounding') }">
                    {{ t('views.purchase_return.fields.rounding') }}
                  </FormLabel>
                  <FormInputCurrency
                    v-model="purchaseReturnForm.rounding"
                    :class="{ 'border-danger': invalidField('rounding') }"
                    @change="validateField('rounding')"
                  />
                  <FormErrorMessages :messages="purchaseReturnForm.errors.rounding" />
                </div>
              </div>
            </template>

            <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
              <div class="col-span-12 lg:col-span-9"></div>
              <div class="col-span-12 lg:col-span-3">
                <FormLabel>{{ t('views.purchase_return.fields.amount_payable_preview') }}</FormLabel>
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

            <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
              <div class="col-span-12 lg:col-span-9"></div>
              <div class="col-span-12 lg:col-span-3">
                <FormLabel>{{ t('views.purchase_return.fields.amount_allocated_to_invoice') }}</FormLabel>
                <FormInputCurrency
                  :model-value="
                    formatCurrencyPreviewValue(Number(purchaseReturnData?.amount_allocated_to_invoice ?? 0))
                  "
                  readonly
                />
              </div>
            </div>

            <!-- refunds editor -->
            <div class="space-y-4">
              <div v-if="isRefundEditorExpanded" class="space-y-4">
                <FormErrorMessages :messages="purchaseReturnForm.errors.refunds" />

                <div v-if="purchaseReturnRefundsForm.length === 0" class="text-right text-sm text-slate-500">
                  {{ t('views.purchase_return.fields.refunds_empty') }}
                </div>

                <div v-else class="space-y-4">
                  <div
                    v-for="(refund, index) in purchaseReturnRefundsForm"
                    :key="`return-refund-${refund.id ?? 'new'}-${index}`"
                    class="rounded-md border border-slate-200/70 p-4 dark:border-darkmode-400"
                  >
                    <div class="grid grid-cols-12 gap-4 gap-y-3">
                      <div class="col-span-12 md:col-span-6 lg:col-span-2">
                        <FormLabel :class="{ 'text-danger': invalidField(`refunds.${index}.code`) }">
                          {{ t('views.purchase_return.fields.code') }}
                        </FormLabel>
                        <FormInputCode
                          v-model="refund.code"
                          :class="{ 'border-danger': invalidField(`refunds.${index}.code`) }"
                          :placeholder="t('views.purchase_return.fields.code')"
                          @set-auto="setRefundCode(index)"
                          @change="validateField(`refunds.${index}.code`)"
                        />
                        <FormErrorMessages :messages="getFieldErrors(`refunds.${index}.code`)" />
                      </div>

                      <div class="col-span-12 md:col-span-6 lg:col-span-3">
                        <FormLabel :class="{ 'text-danger': invalidField(`refunds.${index}.date`) }">
                          {{ t('views.purchase_return.fields.date') }}
                        </FormLabel>
                        <FormInputDateTimeAuto
                          v-model="refund.date"
                          :class="{ 'border-danger': invalidField(`refunds.${index}.date`) }"
                          :placeholder="t('views.purchase_return.fields.date')"
                          @change="validateField(`refunds.${index}.date`)"
                        />
                        <FormErrorMessages :messages="getFieldErrors(`refunds.${index}.date`)" />
                      </div>

                      <div class="col-span-12 md:col-span-6 lg:col-span-3">
                        <FormLabel :class="{ 'text-danger': invalidField(`refunds.${index}.cash_account_id`) }">
                          {{ t('views.purchase_return.fields.cash_account_id') }}
                        </FormLabel>
                        <FormSelectSearch
                          v-model="refund.cash_account_id"
                          v-model:search="cashAccountSearch"
                          :options="cashAccountOptions"
                          :placeholder="t('components.dropdown.placeholder')"
                          :class="{ 'border-danger': invalidField(`refunds.${index}.cash_account_id`) }"
                          @change="validateField(`refunds.${index}.cash_account_id`)"
                          @search="loadCashAccountDDL"
                          @clear="
                            () => {
                              refund.cash_account_id = null;
                              validateField(`refunds.${index}.cash_account_id`);
                            }
                          "
                        />
                        <FormErrorMessages :messages="getFieldErrors(`refunds.${index}.cash_account_id`)" />
                      </div>

                      <div class="col-span-12 md:col-span-6 lg:col-span-2">
                        <FormLabel :class="{ 'text-danger': invalidField(`refunds.${index}.amount`) }">
                          {{ t('views.purchase_return.fields.amount') }}
                        </FormLabel>
                        <FormInputCurrency
                          v-model="refund.amount"
                          :allow-negative="false"
                          :class="{ 'border-danger': invalidField(`refunds.${index}.amount`) }"
                          @change="clampRefundAmount(index)"
                        />
                        <FormErrorMessages :messages="getFieldErrors(`refunds.${index}.amount`)" />
                      </div>

                      <div class="col-span-12 flex items-end justify-end md:col-span-6 lg:col-span-2">
                        <Button
                          type="button"
                          variant="outline-secondary"
                          class="flex h-[38px] w-[38px] min-w-0 items-center justify-center"
                          @click="removeRefund(index)"
                        >
                          <Lucide icon="Trash2" class="h-4 w-4 text-danger" />
                        </Button>
                      </div>

                      <div class="col-span-12">
                        <FormLabel :class="{ 'text-danger': invalidField(`refunds.${index}.remarks`) }">
                          {{ t('views.purchase_return.fields.remarks') }}
                        </FormLabel>
                        <FormInput
                          v-model="refund.remarks"
                          :class="{ 'border-danger': invalidField(`refunds.${index}.remarks`) }"
                          :placeholder="t('views.purchase_return.fields.remarks')"
                          @change="validateField(`refunds.${index}.remarks`)"
                        />
                        <FormErrorMessages :messages="getFieldErrors(`refunds.${index}.remarks`)" />
                      </div>
                    </div>
                  </div>
                </div>

                <div class="flex items-center justify-between gap-2">
                  <div class="text-xs text-slate-500">
                    {{ t('views.purchase_return.fields.refund_cap_hint') }}
                  </div>
                  <Button type="button" variant="outline-primary" @click="addRefund">
                    <Lucide icon="Plus" class="mr-1 h-4 w-4" />
                    {{ t('components.buttons.create_new') }}
                  </Button>
                </div>
              </div>

              <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.purchase_return.fields.refunds_total') }}</FormLabel>
                  <div class="flex items-start gap-2">
                    <div class="shrink-0">
                      <Button
                        type="button"
                        variant="outline-secondary"
                        class="flex h-[38px] w-[38px] min-w-0 items-center justify-center"
                        @click="isRefundEditorExpanded = !isRefundEditorExpanded"
                      >
                        {{ isRefundEditorExpanded ? '▲' : '▼' }}
                      </Button>
                    </div>
                    <div class="min-w-0 flex-1">
                      <FormInputCurrency :model-value="formatCurrencyPreviewValue(getRefundsTotalPreview())" readonly />
                    </div>
                  </div>
                </div>
              </div>

              <div class="grid grid-cols-12 items-end gap-4 gap-y-3">
                <div class="col-span-12 lg:col-span-9"></div>
                <div class="col-span-12 lg:col-span-3">
                  <FormLabel>{{ t('views.purchase_return.fields.amount_available') }}</FormLabel>
                  <FormInputCurrency :model-value="formatCurrencyPreviewValue(getAmountAvailablePreview())" readonly />
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
            :disabled="purchaseReturnForm.validating || purchaseReturnForm.hasErrors"
          >
            <Lucide v-if="purchaseReturnForm.validating" icon="Loader" class="mr-2 h-4 w-4 animate-spin" />
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
    :title="t('views.purchase_return.fields.product_unit_id')"
    :is-searching="isSearchingProductUnit"
    :options="productUnitOptions"
    :columns="productUnitDialogColumns"
    @close="showProductUnitModal = false"
    @search="searchProductUnits"
    @select="selectProductUnit($event as ProductUnitOption)"
    @after-leave="handleProductUnitModalAfterLeave"
  />

  <ProductUnitPickerDialog
    v-if="selectedUserLocation"
    v-model:search-text="receiptItemSearchText"
    size="xl"
    panel-class="max-w-5xl"
    :open="showReceiptItemModal"
    :title="t('views.purchase_return.fields.receipt_item_picker_title')"
    :is-searching="isSearchingReceiptItem"
    :options="receiptItemOptions"
    :columns="receiptItemDialogColumns"
    @close="showReceiptItemModal = false"
    @search="searchReceiptItems"
    @select="selectReceiptItem($event as ReceiptItemOption)"
    @after-leave="handleProductUnitModalAfterLeave"
  />
</template>
