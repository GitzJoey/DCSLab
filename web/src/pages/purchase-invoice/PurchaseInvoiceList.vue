<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { Dialog } from '@/components/Base/Headless';
import { FormInputDateTime, FormLabel, FormSelectSearch } from '@/components/Base/Form';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import PurchaseInvoiceService from '@/services/PurchaseInvoiceService';
import PurchaseOrderService from '@/services/PurchaseOrderService';
import SupplierService from '@/services/SupplierService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import type { DataListEmittedData } from '@/components/DataList/DataList.vue';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import type { DropDownOption } from '@/types/models/DropDownOption';
import type { NotificationData } from '@/types/models/NotificationData';
import type { PurchaseInvoice } from '@/types/models/PurchaseInvoice';
import type { Collection } from '@/types/resources/Collection';
import type { ServiceResponse } from '@/types/services/ServiceResponse';
import type { PurchaseInvoiceReadAnyPaginateRequest } from '@/types/services/purchase-invoice/PurchaseInvoiceRequest';
import { formatCurrency, formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();

const purchaseInvoiceService = new PurchaseInvoiceService();
const purchaseOrderService = new PurchaseOrderService();
const supplierService = new SupplierService();
const selectedUserLocationStore = useSelectedUserLocationStore();

const emits = defineEmits([
  'mode-state',
  'loading-state',
  'update-profile',
  'show-alertplaceholder',
  'show-notification',
]);

const deleteUlid = ref<string>('');
const deleteModalShow = ref<boolean>(false);
const expandDetail = ref<number | null>(null);
const startDate = ref<string | null>(null);
const endDate = ref<string | null>(null);
const searchText = ref<string>('');
const selectedSupplierId = ref<string | null>(null);
const selectedPurchaseOrderId = ref<string | null>(null);
const selectedIsPosted = ref<string | null>(null);
const selectedIsPaidOff = ref<string | null>(null);

const purchaseInvoiceLists = ref<Collection<Array<PurchaseInvoice>> | null>({
  data: [],
  meta: {
    current_page: 1,
    from: null,
    last_page: 0,
    path: '',
    per_page: 10,
    to: null,
    total: 0,
  },
  links: {
    first: '',
    last: '',
    prev: null,
    next: null,
  },
});

const isUserLocationSelected = computed(() => selectedUserLocationStore.isUserLocationSelected);
const selectedUserLocation = computed(() => selectedUserLocationStore.selectedUserLocation);

const supplierDDL = ref<Array<DropDownOption> | null>(null);
const supplierSearch = ref<string>('');
const supplierOptions = computed(() =>
  (supplierDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const purchaseOrderDDL = ref<Array<DropDownOption> | null>(null);
const purchaseOrderSearch = ref<string>('');
const purchaseOrderOptions = computed(() =>
  (purchaseOrderDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const booleanOptions = computed(() => [
  { value: 'true', label: t('components.buttons.yes') },
  { value: 'false', label: t('components.buttons.no') },
]);

onMounted(async () => {
  emits('mode-state', ViewMode.LIST);

  if (!isUserLocationSelected.value) {
    router.push({
      name: 'side-menu-error-code',
      params: { code: ErrorCode.USERLOCATION_REQUIRED },
    });
    return;
  }

  const now = new Date();
  const startOfMonth = new Date(now.getFullYear(), now.getMonth(), 1, 0, 0, 0);
  const endOfMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0, 23, 59, 59);
  startDate.value = formatDate(startOfMonth.toString(), 'YYYY-MM-DD HH:mm:ss');
  endDate.value = formatDate(endOfMonth.toString(), 'YYYY-MM-DD HH:mm:ss');

  await Promise.all([loadSupplierDDL(), loadPurchaseOrderDDL()]);
  await getPurchaseInvoices('', true, 1, 10);
});

const getPurchaseInvoices = async (search: string, refresh: boolean, page: number, perPage: number) => {
  emits('loading-state', true);
  searchText.value = search;

  const request: PurchaseInvoiceReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: startDate.value,
    end_date: endDate.value,
    supplier_id: selectedSupplierId.value,
    purchase_order_id: selectedPurchaseOrderId.value,
    is_posted: selectedIsPosted.value === null ? null : selectedIsPosted.value === 'true',
    is_paid_off: selectedIsPaidOff.value === null ? null : selectedIsPaidOff.value === 'true',
    refresh,
    page,
    per_page: perPage,
  };

  const result = (await purchaseInvoiceService.readAnyPaginate(request)) as ServiceResponse<Collection<
    Array<PurchaseInvoice>
  > | null>;

  if (result.success && result.data) {
    purchaseInvoiceLists.value = result.data;
    showAlertPlaceholder('hidden', '', null);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }

  emits('loading-state', false);
};

const loadSupplierDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await supplierService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    status: undefined,
    include_id: selectedSupplierId.value ?? undefined,
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

const loadPurchaseOrderDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await purchaseOrderService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: null,
    end_date: null,
    supplier_id: selectedSupplierId.value,
    progress_status: null,
    include_id: selectedPurchaseOrderId.value ?? undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    purchaseOrderDDL.value = result.data.data.map((item) => ({
      code: item.id,
      name: `${item.code} - ${item.supplier?.name ?? '-'}`,
    }));
  }
};

const reloadList = async () => {
  await getPurchaseInvoices(searchText.value, true, 1, purchaseInvoiceLists.value?.meta.per_page ?? 10);
};

const clearSupplierFilter = async () => {
  selectedSupplierId.value = null;
  await loadPurchaseOrderDDL();
  await reloadList();
};

const clearPurchaseOrderFilter = async () => {
  selectedPurchaseOrderId.value = null;
  await reloadList();
};

const handleDataListChange = async (emittedData: DataListEmittedData) => {
  await getPurchaseInvoices(
    emittedData.search.text,
    true,
    emittedData.pagination.page,
    emittedData.pagination.per_page,
  );
};

const viewSelected = (index: number) => {
  expandDetail.value = expandDetail.value === index ? null : index;
};

const editSelected = (index: number) => {
  if (!purchaseInvoiceLists.value?.data?.[index]) return;

  router.push({
    name: 'side-menu-purchase-invoice-edit',
    params: { ulid: purchaseInvoiceLists.value.data[index].ulid },
  });
};

const deleteSelected = (index: number) => {
  if (!purchaseInvoiceLists.value?.data?.[index]) return;

  deleteUlid.value = purchaseInvoiceLists.value.data[index].ulid;
  deleteModalShow.value = true;
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

const confirmDelete = async () => {
  emits('loading-state', true);
  const result = await purchaseInvoiceService.delete(deleteUlid.value);
  emits('loading-state', false);
  deleteModalShow.value = false;

  if (result.success) {
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(t('views.purchase_invoice.alert.delete.title'), t('views.purchase_invoice.alert.delete.message'));
    await getPurchaseInvoices(
      searchText.value,
      true,
      purchaseInvoiceLists.value?.meta.current_page ?? 1,
      purchaseInvoiceLists.value?.meta.per_page ?? 10,
    );
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }
};

const formatCurrencyRounded = (value: number | string | null | undefined, precision = 2) =>
  formatCurrency(Number(Number(value ?? 0).toFixed(precision)));

const formatQuantityValue = (value: number | string | null | undefined, precision = 4) =>
  new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: precision,
  }).format(Number(value ?? 0));

const paymentTypeLabel = (paymentType: string | null | undefined) => {
  switch (paymentType) {
    case 'cash':
      return t('views.purchase_invoice.filters.payment_type_cash');
    case 'down_payment':
      return t('views.purchase_invoice.filters.payment_type_down_payment');
    case 'return':
      return t('views.purchase_invoice.filters.payment_type_return');
    default:
      return '-';
  }
};
</script>

<template>
  <div class="mt-5 grid grid-cols-12 gap-6">
    <div class="col-span-12">
      <div class="mb-5 grid grid-cols-12 gap-4">
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_invoice.fields.start_date') }}</FormLabel>
          <FormInputDateTime v-model="startDate" @change="reloadList" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_invoice.fields.end_date') }}</FormLabel>
          <FormInputDateTime v-model="endDate" @change="reloadList" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_invoice.fields.supplier_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedSupplierId"
            v-model:search="supplierSearch"
            :options="supplierOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="
              async () => {
                await loadPurchaseOrderDDL();
                await reloadList();
              }
            "
            @search="loadSupplierDDL"
            @clear="clearSupplierFilter"
          />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_invoice.fields.purchase_order_id') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedPurchaseOrderId"
            v-model:search="purchaseOrderSearch"
            :options="purchaseOrderOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="reloadList"
            @search="loadPurchaseOrderDDL"
            @clear="clearPurchaseOrderFilter"
          />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_invoice.filters.is_posted') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedIsPosted"
            :options="booleanOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="reloadList"
            @clear="
              () => {
                selectedIsPosted = null;
                reloadList();
              }
            "
          />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_invoice.filters.is_paid_off') }}</FormLabel>
          <FormSelectSearch
            v-model="selectedIsPaidOff"
            :options="booleanOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="reloadList"
            @clear="
              () => {
                selectedIsPaidOff = null;
                reloadList();
              }
            "
          />
        </div>
      </div>

      <DataListFlex
        :data="purchaseInvoiceLists"
        :title="t('views.purchase_invoice.table.title')"
        :enable-search="true"
        :can-print="true"
        :can-export="true"
        :rows="purchaseInvoiceLists?.data ?? []"
        row-class="bg-white dark:bg-darkmode-600"
        :pagination="purchaseInvoiceLists ? purchaseInvoiceLists.meta : null"
        @dataListChanged="handleDataListChange"
      >
        <template #row="{ item, index }">
          <div class="col-span-12 self-start lg:col-span-4">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.purchase_invoice.page_title') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_invoice.fields.code') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseInvoice).code ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_invoice.fields.date') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{
                    (item as PurchaseInvoice).date
                      ? formatDate((item as PurchaseInvoice).date, 'DD-MMM-YYYY HH:mm:ss')
                      : '-'
                  }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_invoice.fields.supplier_id') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseInvoice).supplier?.name ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_invoice.fields.purchase_order_id') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseInvoice).purchase_order?.code ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_invoice.fields.tax_invoice_number') }}</div>
                <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseInvoice).tax_invoice_number || '-' }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 self-start lg:col-span-4">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.purchase_invoice.field_groups.summary') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_invoice.fields.amount_payable') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrencyRounded((item as PurchaseInvoice).amount_payable) }}
                </div>
                <div class="col-span-7 text-slate-500">
                  {{ t('views.purchase_invoice.fields.amount_paid_down_payment') }}
                </div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrencyRounded((item as PurchaseInvoice).amount_paid_down_payment) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_invoice.fields.amount_paid_return') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrencyRounded((item as PurchaseInvoice).amount_paid_return) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_invoice.fields.amount_paid_total') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrencyRounded((item as PurchaseInvoice).amount_paid_total) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_invoice.fields.amount_due') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrencyRounded((item as PurchaseInvoice).amount_due) }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 self-start lg:col-span-3">
            <div class="space-y-2">
              <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                {{ t('views.purchase_invoice.field_groups.status') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_invoice.fields.is_posted') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseInvoice).is_posted ? t('components.buttons.yes') : t('components.buttons.no') }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_invoice.fields.is_paid_off') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseInvoice).is_paid_off ? t('components.buttons.yes') : t('components.buttons.no') }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_invoice.fields.due_days') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseInvoice).due_days ?? 0 }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_invoice.fields.item_count') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ (item as PurchaseInvoice).items?.length ?? 0 }}
                </div>
              </div>
            </div>
          </div>

          <div
            class="col-span-12 flex items-center justify-end gap-2 self-center pt-2 md:col-span-1 md:flex-col lg:col-span-1 lg:flex-col"
          >
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="viewSelected(index)">
              <Lucide icon="Info" class="h-4 w-4" />
            </Button>
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="editSelected(index)">
              <Lucide icon="Pen" class="h-4 w-4" />
            </Button>
            <Button
              size="sm"
              variant="outline-secondary"
              class="flex items-center gap-1"
              @click="deleteSelected(index)"
            >
              <Lucide icon="Trash2" class="h-4 w-4 text-danger" />
            </Button>
          </div>

          <div
            v-if="expandDetail === index"
            class="col-span-12 mt-2 border-t border-slate-200 pt-3 dark:border-darkmode-400"
          >
            <div class="space-y-4">
              <div class="space-y-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                  {{ t('views.purchase_invoice.field_groups.items') }}
                </div>
                <div v-if="!(item as PurchaseInvoice).items?.length" class="text-xs text-slate-500">
                  {{ t('views.purchase_invoice.fields.items_empty') }}
                </div>
                <div
                  v-for="(invoiceItem, itemIndex) in (item as PurchaseInvoice).items ?? []"
                  :key="invoiceItem.ulid ?? `${(item as PurchaseInvoice).ulid}-item-${itemIndex}`"
                  class="rounded-md border border-slate-200/70 p-3 dark:border-darkmode-400"
                >
                  <div class="grid grid-cols-12 gap-x-3 gap-y-2 text-xs">
                    <div class="col-span-12 font-medium text-slate-700 dark:text-slate-200">
                      {{ invoiceItem.product_unit?.product?.name ?? invoiceItem.product_unit?.code ?? '-' }}
                    </div>
                    <div class="col-span-4 text-slate-500">{{ t('views.purchase_invoice.fields.qty') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">
                      {{ formatQuantityValue(invoiceItem.qty) }} {{ invoiceItem.product_unit?.unit?.name ?? '' }}
                    </div>
                    <div class="col-span-4 text-slate-500">
                      {{ t('views.purchase_invoice.fields.product_unit_price') }}
                    </div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">
                      {{ formatCurrencyRounded(invoiceItem.product_unit_price) }}
                    </div>
                    <div class="col-span-4 text-slate-500">{{ t('views.purchase_invoice.fields.vat') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">
                      {{ formatCurrencyRounded(invoiceItem.vat) }}
                    </div>
                    <div class="col-span-4 text-slate-500">
                      {{ t('views.purchase_invoice.fields.item_amount_payable') }}
                    </div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">
                      {{ formatCurrencyRounded(invoiceItem.amount_payable) }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="space-y-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                  {{ t('views.purchase_invoice.field_groups.payments') }}
                </div>
                <div v-if="!(item as PurchaseInvoice).payments?.length" class="text-xs text-slate-500">
                  {{ t('views.purchase_invoice.fields.payments_empty') }}
                </div>
                <div
                  v-for="(payment, paymentIndex) in (item as PurchaseInvoice).payments ?? []"
                  :key="payment.ulid ?? `${(item as PurchaseInvoice).ulid}-payment-${paymentIndex}`"
                  class="rounded-md border border-slate-200/70 p-3 dark:border-darkmode-400"
                >
                  <div class="grid grid-cols-12 gap-x-3 gap-y-2 text-xs">
                    <div class="col-span-4 text-slate-500">{{ t('views.purchase_invoice.fields.code') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ payment.code ?? '-' }}</div>
                    <div class="col-span-4 text-slate-500">{{ t('views.purchase_invoice.fields.date') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">
                      {{ payment.date ? formatDate(payment.date, 'DD-MMM-YYYY HH:mm:ss') : '-' }}
                    </div>
                    <div class="col-span-4 text-slate-500">{{ t('views.purchase_invoice.fields.payment_type') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">
                      {{ paymentTypeLabel(payment.payment_type) }}
                    </div>
                    <div class="col-span-4 text-slate-500">{{ t('views.purchase_invoice.fields.amount') }}</div>
                    <div class="col-span-8 text-slate-700 dark:text-slate-200">
                      {{ formatCurrencyRounded(payment.amount) }}
                    </div>
                    <div class="col-span-4 text-slate-500">{{ t('views.purchase_invoice.fields.remarks') }}</div>
                    <div class="col-span-8 break-words text-slate-700 dark:text-slate-200">
                      {{ payment.remarks?.trim() || '-' }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="space-y-2">
                <div class="text-xs font-semibold uppercase tracking-wide text-primary">
                  {{ t('views.purchase_invoice.field_groups.remarks') }}
                </div>
                <div
                  class="rounded-md border border-slate-200/70 p-3 text-xs text-slate-700 dark:border-darkmode-400 dark:text-slate-200"
                >
                  {{ (item as PurchaseInvoice).remarks?.trim() || '-' }}
                </div>
              </div>
            </div>
          </div>
        </template>
      </DataListFlex>
    </div>
  </div>

  <Dialog
    :open="deleteModalShow"
    @close="
      () => {
        deleteModalShow = false;
      }
    "
  >
    <Dialog.Panel>
      <div class="p-5 text-center">
        <Lucide icon="XCircle" class="mx-auto mt-3 h-16 w-16 text-danger" />
        <div class="mt-5 text-3xl">{{ t('components.delete-modal.title') }}</div>
        <div class="mt-2 text-slate-500">
          {{ t('components.delete-modal.desc_1') }}
          <br />
          {{ t('components.delete-modal.desc_2') }}
        </div>
      </div>
      <div class="px-5 pb-8 text-center">
        <Button variant="outline-secondary" type="button" class="mr-1 w-24" @click="deleteModalShow = false">
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button variant="danger" type="button" class="w-24" @click="confirmDelete">
          {{ t('components.buttons.delete') }}
        </Button>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
