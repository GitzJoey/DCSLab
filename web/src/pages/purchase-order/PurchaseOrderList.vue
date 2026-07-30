<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { Dialog } from '@/components/Base/Headless';
import { FormInputDateTime, FormLabel, FormSelectSearch } from '@/components/Base/Form';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import PurchaseOrderService from '@/services/PurchaseOrderService';
import SupplierService from '@/services/SupplierService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import { PurchaseOrder } from '@/types/models/PurchaseOrder';
import { DropDownOption } from '@/types/models/DropDownOption';
import { NotificationData } from '@/types/models/NotificationData';
import { Collection } from '@/types/resources/Collection';
import { DataListEmittedData } from '@/components/DataList/DataList.vue';
import { PurchaseOrderReadAnyPaginateRequest } from '@/types/services/purchase-order/PurchaseOrderRequest';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
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
const selectedProgressStatus = ref<string | null>(null);

const formatCurrencyRounded = (value: number | string, precision = 2) =>
  formatCurrency(Number(Number(value ?? 0).toFixed(precision)));

const formatQuantityValue = (value: number | string, precision = 4) =>
  new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: precision,
  }).format(Number(value ?? 0));

const getProgressStatusBadgeClass = (status: string | null | undefined) => {
  switch (status) {
    case 'matched':
      return 'bg-success/15 text-success';
    case 'unmatched':
      return 'bg-warning/15 text-warning';
    case 'unlinked':
    default:
      return 'bg-slate-200/80 text-slate-700 dark:bg-darkmode-400 dark:text-slate-200';
  }
};

const purchaseOrderLists = ref<Collection<Array<PurchaseOrder>> | null>({
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
const progressStatusDDL = ref<Array<DropDownOption> | null>(null);
const supplierSearch = ref<string>('');
const supplierOptions = computed(() =>
  (supplierDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const progressStatusOptions = computed(() => [
  ...(progressStatusDDL.value ?? []).map((item) => ({
    value: item.code,
    label: t(item.name),
  })),
]);

const progressStatusLabelMap = computed<Record<string, string>>(() =>
  Object.fromEntries(
    (progressStatusDDL.value ?? []).map((item) => [String(item.code), item.name]),
  ),
);

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

  await Promise.all([loadSupplierDDL(), loadProgressStatusDDL()]);
  await getPurchaseOrders('', true, 1, 10);
});

const getPurchaseOrders = async (search: string, refresh: boolean, page: number, perPage: number) => {
  emits('loading-state', true);
  searchText.value = search;

  const request: PurchaseOrderReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: startDate.value,
    end_date: endDate.value,
    supplier_id: selectedSupplierId.value,
    progress_status: selectedProgressStatus.value,
    refresh,
    page,
    per_page: perPage,
  };

  const result = (await purchaseOrderService.readAnyPaginate(
    request,
  )) as ServiceResponse<Collection<Array<PurchaseOrder>> | null>;

  if (result.success && result.data) {
    purchaseOrderLists.value = result.data;
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

const loadProgressStatusDDL = async () => {
  const result = await purchaseOrderService.readProgressStatuses();

  if (result.success && result.data) {
    progressStatusDDL.value = result.data;
  }
};

const clearSupplierFilter = async () => {
  selectedSupplierId.value = null;
  await getPurchaseOrders(searchText.value, true, 1, purchaseOrderLists.value?.meta.per_page ?? 10);
};

const clearProgressStatusFilter = async () => {
  selectedProgressStatus.value = null;
  await getPurchaseOrders(searchText.value, true, 1, purchaseOrderLists.value?.meta.per_page ?? 10);
};

const handleDataListChange = async (emittedData: DataListEmittedData) => {
  await getPurchaseOrders(
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
  if (!purchaseOrderLists.value?.data?.[index]) return;
  router.push({
    name: 'side-menu-purchase-order-edit',
    params: { ulid: purchaseOrderLists.value.data[index].ulid },
  });
};

const deleteSelected = (index: number) => {
  if (!purchaseOrderLists.value?.data?.[index]) return;
  deleteUlid.value = purchaseOrderLists.value.data[index].ulid;
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
  const result = await purchaseOrderService.delete(deleteUlid.value);
  emits('loading-state', false);
  deleteModalShow.value = false;

  if (result.success) {
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(t('views.purchase_order.alert.delete.title'), t('views.purchase_order.alert.delete.message'));
    await getPurchaseOrders(searchText.value, true, purchaseOrderLists.value?.meta.current_page ?? 1, purchaseOrderLists.value?.meta.per_page ?? 10);
  }
};
</script>

<template>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12">
      <div class="grid grid-cols-12 gap-4 mb-5">
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_order.fields.start_date') }}</FormLabel>
          <FormInputDateTime v-model="startDate"
            @change="getPurchaseOrders(searchText, true, 1, purchaseOrderLists?.meta.per_page ?? 10)" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_order.fields.end_date') }}</FormLabel>
          <FormInputDateTime v-model="endDate"
            @change="getPurchaseOrders(searchText, true, 1, purchaseOrderLists?.meta.per_page ?? 10)" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_order.fields.supplier_id') }}</FormLabel>
          <FormSelectSearch v-model="selectedSupplierId" v-model:search="supplierSearch" :options="supplierOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getPurchaseOrders(searchText, true, 1, purchaseOrderLists?.meta.per_page ?? 10)"
            @search="loadSupplierDDL" @clear="clearSupplierFilter" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.purchase_order.filters.progress_status') }}</FormLabel>
          <FormSelectSearch v-model="selectedProgressStatus" :options="progressStatusOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getPurchaseOrders(searchText, true, 1, purchaseOrderLists?.meta.per_page ?? 10)"
            @clear="clearProgressStatusFilter" />
        </div>
      </div>

      <DataListFlex :data="purchaseOrderLists" :enable-search="true" :can-print="true" :can-export="true"
        :rows="purchaseOrderLists?.data ?? []" row-class="bg-white dark:bg-darkmode-600"
        :pagination="purchaseOrderLists ? purchaseOrderLists.meta : null" @dataListChanged="handleDataListChange">
        <template #row="{ item, index }">
          <div class="col-span-12 md:col-span-12 lg:col-span-4 self-start">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.purchase_order.page_title') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_order.fields.code') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ (item as PurchaseOrder).code ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_order.fields.date') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as PurchaseOrder).date ? formatDate((item as PurchaseOrder).date, 'DD-MMM-YYYY HH:mm:ss') :
                  '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_order.fields.supplier_id') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">{{ (item as
                  PurchaseOrder).supplier?.name ?? '-' }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_order.fields.due_days') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ (item as PurchaseOrder).due_days ?? 0 }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.purchase_order.fields.remarks') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">{{ (item as
                  PurchaseOrder).remarks?.trim() || '-' }}</div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-6 lg:col-span-4 self-start md:pr-3 lg:pr-4">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.purchase_order.field_groups.summary') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-7 text-slate-500">{{
                  t('views.purchase_order.fields.item_total_before_global_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as
                  PurchaseOrder).item_total_before_global_discount ?? 0) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_order.fields.global_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as
                  PurchaseOrder).global_discount ?? 0) }}</div>
                <div class="col-span-7 text-slate-500">{{
                  t('views.purchase_order.fields.item_total_after_global_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as
                  PurchaseOrder).item_total_after_global_discount ?? 0) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_order.fields.vat_base') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrencyRounded((item as
                  PurchaseOrder).vat_base ?? 0) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_order.fields.vat') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrencyRounded((item as
                  PurchaseOrder).vat ?? 0) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.purchase_order.fields.rounding') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as
                  PurchaseOrder).rounding ?? 0) }}</div>
                <div class="col-span-4 text-primary font-medium">{{ t('views.purchase_order.fields.amount_payable') }}
                </div>
                <div class="col-span-8 text-right text-primary font-medium">{{ formatCurrency((item as
                  PurchaseOrder).amount_payable ?? 0) }}</div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-5 lg:col-span-3 self-start md:pl-3 lg:pl-4">
            <div class="space-y-4">
              <div class="space-y-2">
                <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                  {{ t('views.purchase_order.field_groups.progress') }}
                </div>
                <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 px-3 py-3">
                  <div class="flex items-center justify-between gap-2">
                    <span class="text-xs text-slate-500">{{ t('views.purchase_order.fields.progress_status') }}</span>
                    <span
                      class="inline-flex rounded-full px-2 py-1 text-[11px] font-medium"
                      :class="getProgressStatusBadgeClass((item as PurchaseOrder).progress_status)"
                    >
                      {{ t(progressStatusLabelMap[(item as PurchaseOrder).progress_status ?? '']
                        ?? 'views.purchase_order.filters.progress_status_unlinked') }}
                    </span>
                  </div>
                  <div class="mt-3 grid grid-cols-12 gap-x-3 gap-y-2 text-xs">
                    <div class="col-span-7 text-slate-500">{{ t('views.purchase_order.fields.item_total_count') }}</div>
                    <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{
                      formatQuantityValue((item as PurchaseOrder).item_total_count ?? 0, 0) }}</div>
                    <div class="col-span-7 text-slate-500">{{ t('views.purchase_order.fields.item_matched_count') }}</div>
                    <div class="col-span-5 text-right text-success">{{ formatQuantityValue((item as
                      PurchaseOrder).item_matched_count ?? 0, 0) }}</div>
                    <div class="col-span-7 text-slate-500">{{ t('views.purchase_order.fields.item_less_count') }}</div>
                    <div class="col-span-5 text-right text-warning">{{ formatQuantityValue((item as
                      PurchaseOrder).item_less_count ?? 0, 0) }}</div>
                    <div class="col-span-7 text-slate-500">{{ t('views.purchase_order.fields.item_more_count') }}</div>
                    <div class="col-span-5 text-right text-danger">{{ formatQuantityValue((item as
                      PurchaseOrder).item_more_count ?? 0, 0) }}</div>
                    <div class="col-span-7 text-slate-500">{{ t('views.purchase_order.fields.item_unlinked_count') }}
                    </div>
                    <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{
                      formatQuantityValue((item as PurchaseOrder).item_unlinked_count ?? 0, 0) }}</div>
                  </div>
                </div>
              </div>

              <div class="space-y-2">
                <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                  {{ t('views.purchase_order.fields.payment') }}
                </div>
                <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                  <div class="col-span-7 text-slate-500">{{ t('views.purchase_order.fields.amount_paid_down_payment') }}
                  </div>
                  <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as
                    PurchaseOrder).amount_paid_down_payment ?? 0) }}</div>
                  <div class="col-span-7 text-slate-500">{{ t('views.purchase_order.fields.amount_allocated_down_payment')
                    }}</div>
                  <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as
                    PurchaseOrder).amount_allocated_down_payment ?? 0) }}</div>
                  <div class="col-span-7 text-slate-500">{{ t('views.purchase_order.fields.amount_refunded_down_payment')
                    }}</div>
                  <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as
                    PurchaseOrder).amount_refunded_down_payment ?? 0) }}</div>
                  <div class="col-span-7 text-primary font-medium">{{
                    t('views.purchase_order.fields.amount_available_down_payment') }}</div>
                  <div class="col-span-5 text-right text-primary font-medium">{{ formatCurrency((item as
                    PurchaseOrder).amount_available_down_payment ?? 0) }}</div>
                </div>
              </div>
            </div>
          </div>

          <div
            class="col-span-12 md:col-span-1 lg:col-span-1 self-center flex justify-end items-center gap-2 pt-2 md:flex-col lg:flex-col">
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="viewSelected(index)">
              <Lucide icon="Info" class="w-4 h-4" />
            </Button>
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="editSelected(index)">
              <Lucide icon="Pen" class="w-4 h-4" />
            </Button>
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1"
              @click="deleteSelected(index)">
              <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
            </Button>
          </div>

          <div v-if="expandDetail === index"
            class="col-span-12 border-t border-slate-200 dark:border-darkmode-400 mt-2 pt-3">
            <div class="grid grid-cols-12 gap-4">
              <div class="col-span-12 lg:col-span-6 space-y-3">
                <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                  {{ t('views.purchase_order.field_groups.items') }}
                </div>
                <div v-if="!(item as PurchaseOrder).items?.length" class="text-xs text-slate-500">
                  {{ t('components.data-list.data_not_found') }}
                </div>
                <div v-else class="space-y-3 rounded-md border border-slate-200/60 dark:border-darkmode-400 p-3">
                  <div v-for="poItem in (item as PurchaseOrder).items" :key="poItem.id"
                    class="border-b border-slate-200/60 pb-3 text-sm last:border-b-0 last:pb-0 dark:border-darkmode-400">
                    <div class="font-medium text-slate-700 dark:text-slate-200 break-words">
                      {{ poItem.product_unit?.product?.name ?? '-' }}
                    </div>
                    <div class="mt-1 text-xs text-slate-500 break-words">
                      {{ poItem.product_unit?.code ?? '-' }}{{ poItem.product_unit?.unit?.name ? `
                      (${poItem.product_unit.unit.name})` : '' }}
                    </div>
                    <div
                      class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-600 dark:text-slate-300">
                      <span>
                        {{ t('views.purchase_order.fields.qty') }}
                        <span class="text-slate-700 dark:text-slate-200">{{ formatQuantityValue(poItem.qty ?? 0)
                          }}</span>
                      </span>
                      <span class="text-slate-400">|</span>
                      <span>
                        {{ t('views.purchase_order.fields.unit_name') }}
                        <span class="text-slate-700 dark:text-slate-200">{{ poItem.product_unit?.unit?.name ?? '-'
                          }}</span>
                      </span>
                      <span class="text-slate-400">|</span>
                      <span>
                        {{ t('views.purchase_order.fields.product_unit_price') }}
                        <span class="text-slate-700 dark:text-slate-200">{{ formatCurrency(poItem.product_unit_price ??
                          0) }}</span>
                      </span>
                      <span class="text-slate-400">|</span>
                      <span>
                        {{ t('views.purchase_order.fields.subtotal_after_discount') }}
                        <span class="font-medium text-slate-700 dark:text-slate-200">{{
                          formatCurrency(poItem.subtotal_after_discount ?? 0) }}</span>
                      </span>
                    </div>
                    <div class="mt-2 grid grid-cols-12 gap-x-3 gap-y-1 text-xs">
                      <div class="col-span-6 text-slate-500">{{ t('views.purchase_order.fields.qty_received_base') }}
                      </div>
                      <div class="col-span-6 text-right text-slate-700 dark:text-slate-200">{{
                        formatQuantityValue(poItem.qty_received_base ?? 0) }}</div>
                      <div class="col-span-6 text-slate-500">{{ t('views.purchase_order.fields.qty_invoiced_base') }}
                      </div>
                      <div class="col-span-6 text-right text-slate-700 dark:text-slate-200">{{
                        formatQuantityValue(poItem.qty_invoiced_base ?? 0) }}</div>
                      <div class="col-span-6 text-slate-500">{{ t('views.purchase_order.fields.qty_outstanding_base') }}
                      </div>
                      <div class="col-span-6 text-right text-warning">{{ formatQuantityValue(
                        poItem.qty_outstanding_base ?? 0) }}</div>
                      <div class="col-span-6 text-slate-500">{{ t('views.purchase_order.fields.qty_excess_base') }}</div>
                      <div class="col-span-6 text-right text-danger">{{ formatQuantityValue(
                        poItem.qty_excess_base ?? 0) }}</div>
                    </div>
                    <div v-if="poItem.remarks?.trim()" class="mt-2 text-xs text-slate-500 break-words">
                      {{ poItem.remarks }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-span-12 lg:col-span-6 space-y-4">
                <div class="space-y-3">
                  <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                    {{ t('views.purchase_order.fields.payments') }}
                  </div>
                  <div v-if="!(item as PurchaseOrder).payments?.length" class="text-xs text-slate-500">
                    {{ t('components.data-list.data_not_found') }}
                  </div>
                  <div v-else class="space-y-2">
                    <div v-for="payment in (item as PurchaseOrder).payments" :key="payment.id"
                      class="rounded-md border border-slate-200/60 dark:border-darkmode-400 px-3 py-2 grid grid-cols-12 gap-3 text-xs">
                      <div class="col-span-4 text-slate-700 dark:text-slate-200">{{ payment.code }}</div>
                      <div class="col-span-4 text-slate-500">{{ payment.cash_account?.name ?? '-' }}</div>
                      <div class="col-span-4 text-right text-slate-700 dark:text-slate-200">{{
                        formatCurrency(payment.amount ?? 0) }}</div>
                    </div>
                  </div>
                </div>

                <div class="space-y-3">
                  <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                    {{ t('views.purchase_order.fields.refunded_payments') }}
                  </div>
                  <div v-if="!(item as PurchaseOrder).refunded_payments?.length" class="text-xs text-slate-500">
                    {{ t('components.data-list.data_not_found') }}
                  </div>
                  <div v-else class="space-y-2">
                    <div v-for="refundedPayment in (item as PurchaseOrder).refunded_payments"
                      :key="refundedPayment.id"
                      class="rounded-md border border-slate-200/60 dark:border-darkmode-400 px-3 py-2 grid grid-cols-12 gap-3 text-xs">
                      <div class="col-span-4 text-slate-700 dark:text-slate-200">{{ refundedPayment.code }}</div>
                      <div class="col-span-4 text-slate-500">{{ refundedPayment.cash_account?.name ?? '-' }}</div>
                      <div class="col-span-4 text-right text-slate-700 dark:text-slate-200">{{
                        formatCurrency(refundedPayment.amount ?? 0) }}</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </template>
      </DataListFlex>
    </div>
  </div>

  <Dialog :open="deleteModalShow" @close="() => { deleteModalShow = false; }">
    <Dialog.Panel>
      <div class="p-5 text-center">
        <Lucide icon="XCircle" class="w-16 h-16 mx-auto mt-3 text-danger" />
        <div class="mt-5 text-3xl">
          {{ t('components.delete-modal.title') }}
        </div>
        <div class="mt-2 text-slate-500">
          {{ t('components.delete-modal.desc_1') }}
          <br />
          {{ t('components.delete-modal.desc_2') }}
        </div>
      </div>
      <div class="px-5 pb-8 text-center">
        <Button type="button" variant="outline-secondary" class="w-24 mr-1" @click="deleteModalShow = false">
          {{ t('components.buttons.cancel') }}
        </Button>
        <Button type="button" variant="danger" class="w-24" @click="confirmDelete">
          {{ t('components.buttons.delete') }}
        </Button>
      </div>
    </Dialog.Panel>
  </Dialog>
</template>
