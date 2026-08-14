<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { Dialog } from '@/components/Base/Headless';
import { FormInputDateTime, FormLabel, FormSelectSearch } from '@/components/Base/Form';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import SalesOrderService from '@/services/SalesOrderService';
import CustomerService from '@/services/CustomerService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import { SalesOrder } from '@/types/models/SalesOrder';
import { DropDownOption } from '@/types/models/DropDownOption';
import { NotificationData } from '@/types/models/NotificationData';
import { Collection } from '@/types/resources/Collection';
import { DataListEmittedData } from '@/components/DataList/DataList.vue';
import { SalesOrderReadAnyPaginateRequest } from '@/types/services/sales-order/SalesOrderRequest';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
const salesOrderService = new SalesOrderService();
const customerService = new CustomerService();
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
const selectedCustomerId = ref<string | null>(null);
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

const salesOrderLists = ref<Collection<Array<SalesOrder>> | null>({
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

const customerDDL = ref<Array<DropDownOption> | null>(null);
const progressStatusDDL = ref<Array<DropDownOption> | null>(null);
const customerSearch = ref<string>('');
const customerOptions = computed(() =>
  (customerDDL.value ?? []).map((item) => ({
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

  await Promise.all([loadCustomerDDL(), loadProgressStatusDDL()]);
  await getSalesOrders('', true, 1, 10);
});

const getSalesOrders = async (search: string, refresh: boolean, page: number, perPage: number) => {
  emits('loading-state', true);
  searchText.value = search;

  const request: SalesOrderReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: startDate.value,
    end_date: endDate.value,
    customer_id: selectedCustomerId.value,
    progress_status: selectedProgressStatus.value,
    refresh,
    page,
    per_page: perPage,
  };

  const result = (await salesOrderService.readAnyPaginate(
    request,
  )) as ServiceResponse<Collection<Array<SalesOrder>> | null>;

  if (result.success && result.data) {
    salesOrderLists.value = result.data;
  }

  emits('loading-state', false);
};

const loadCustomerDDL = async (search = '') => {
  if (!selectedUserLocation.value) return;

  const result = await customerService.readAnyGet({
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    search,
    status: undefined,
    include_id: selectedCustomerId.value ?? undefined,
    refresh: false,
    limit: 100,
  });

  if (result.success && result.data) {
    customerDDL.value = result.data.data.map((item: any) => ({
      code: item.id,
      name: item.name,
    }));
  }
};

const loadProgressStatusDDL = async () => {
  const result = await salesOrderService.readProgressStatuses();

  if (result.success && result.data) {
    progressStatusDDL.value = result.data;
  }
};

const clearCustomerFilter = async () => {
  selectedCustomerId.value = null;
  await getSalesOrders(searchText.value, true, 1, salesOrderLists.value?.meta.per_page ?? 10);
};

const clearProgressStatusFilter = async () => {
  selectedProgressStatus.value = null;
  await getSalesOrders(searchText.value, true, 1, salesOrderLists.value?.meta.per_page ?? 10);
};

const handleDataListChange = async (emittedData: DataListEmittedData) => {
  await getSalesOrders(
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
  if (!salesOrderLists.value?.data?.[index]) return;
  router.push({
    name: 'side-menu-sales-order-edit',
    params: { ulid: salesOrderLists.value.data[index].ulid },
  });
};

const deleteSelected = (index: number) => {
  if (!salesOrderLists.value?.data?.[index]) return;
  deleteUlid.value = salesOrderLists.value.data[index].ulid;
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
  const result = await salesOrderService.delete(deleteUlid.value);
  emits('loading-state', false);
  deleteModalShow.value = false;

  if (result.success) {
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(t('views.sales_order.alert.delete.title'), t('views.sales_order.alert.delete.message'));
    await getSalesOrders(searchText.value, true, salesOrderLists.value?.meta.current_page ?? 1, salesOrderLists.value?.meta.per_page ?? 10);
  } else {
    showAlertPlaceholder('danger', '', result.errors as Record<string, Array<string>>);
  }
};
</script>

<template>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12">
      <div class="grid grid-cols-12 gap-4 mb-5">
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.sales_order.fields.start_date') }}</FormLabel>
          <FormInputDateTime v-model="startDate"
            @change="getSalesOrders(searchText, true, 1, salesOrderLists?.meta.per_page ?? 10)" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.sales_order.fields.end_date') }}</FormLabel>
          <FormInputDateTime v-model="endDate"
            @change="getSalesOrders(searchText, true, 1, salesOrderLists?.meta.per_page ?? 10)" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.sales_order.fields.customer_id') }}</FormLabel>
          <FormSelectSearch v-model="selectedCustomerId" v-model:search="customerSearch" :options="customerOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getSalesOrders(searchText, true, 1, salesOrderLists?.meta.per_page ?? 10)"
            @search="loadCustomerDDL" @clear="clearCustomerFilter" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.sales_order.filters.progress_status') }}</FormLabel>
          <FormSelectSearch v-model="selectedProgressStatus" :options="progressStatusOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getSalesOrders(searchText, true, 1, salesOrderLists?.meta.per_page ?? 10)"
            @clear="clearProgressStatusFilter" />
        </div>
      </div>

      <DataListFlex :data="salesOrderLists" :enable-search="true" :can-print="true" :can-export="true"
        :rows="salesOrderLists?.data ?? []" row-class="bg-white dark:bg-darkmode-600"
        :pagination="salesOrderLists ? salesOrderLists.meta : null" @dataListChanged="handleDataListChange">
        <template #row="{ item, index }">
          <div class="col-span-12 md:col-span-12 lg:col-span-4 self-start">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.sales_order.page_title') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.code') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ (item as SalesOrder).code ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.date') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as SalesOrder).date ? formatDate((item as SalesOrder).date, 'DD-MMM-YYYY HH:mm:ss') :
                  '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.customer_id') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">{{ (item as
                  SalesOrder).customer?.name ?? '-' }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.due_days') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ (item as SalesOrder).due_days ?? 0 }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_order.fields.remarks') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">{{ (item as
                  SalesOrder).remarks?.trim() || '-' }}</div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-6 lg:col-span-4 self-start md:pr-3 lg:pr-4">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.sales_order.field_groups.summary') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-7 text-slate-500">{{
                  t('views.sales_order.fields.item_total_before_global_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as
                  SalesOrder).item_total_before_global_discount ?? 0) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.global_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as
                  SalesOrder).global_discount ?? 0) }}</div>
                <div class="col-span-7 text-slate-500">{{
                  t('views.sales_order.fields.item_total_after_global_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as
                  SalesOrder).item_total_after_global_discount ?? 0) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.vat_base') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrencyRounded((item as
                  SalesOrder).vat_base ?? 0) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.vat') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrencyRounded((item as
                  SalesOrder).vat ?? 0) }}</div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.rounding') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as
                  SalesOrder).rounding ?? 0) }}</div>
                <div class="col-span-4 text-primary font-medium">{{ t('views.sales_order.fields.amount_payable') }}
                </div>
                <div class="col-span-8 text-right text-primary font-medium">{{ formatCurrency((item as
                  SalesOrder).amount_payable ?? 0) }}</div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-5 lg:col-span-3 self-start md:pl-3 lg:pl-4">
            <div class="space-y-4">
              <div class="space-y-2">
                <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                  {{ t('views.sales_order.field_groups.progress') }}
                </div>
                <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 px-3 py-3">
                  <div class="flex items-center justify-between gap-2">
                    <span class="text-xs text-slate-500">{{ t('views.sales_order.fields.progress_status') }}</span>
                    <span
                      class="inline-flex rounded-full px-2 py-1 text-[11px] font-medium"
                      :class="getProgressStatusBadgeClass((item as SalesOrder).progress_status)"
                    >
                      {{ t(progressStatusLabelMap[(item as SalesOrder).progress_status ?? '']
                        ?? 'views.sales_order.filters.progress_status_unlinked') }}
                    </span>
                  </div>
                  <div class="mt-3 grid grid-cols-12 gap-x-3 gap-y-2 text-xs">
                    <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.item_total_count') }}</div>
                    <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{
                      formatQuantityValue((item as SalesOrder).item_total_count ?? 0, 0) }}</div>
                    <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.item_matched_count') }}</div>
                    <div class="col-span-5 text-right text-success">{{ formatQuantityValue((item as
                      SalesOrder).item_matched_count ?? 0, 0) }}</div>
                    <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.item_less_count') }}</div>
                    <div class="col-span-5 text-right text-warning">{{ formatQuantityValue((item as
                      SalesOrder).item_less_count ?? 0, 0) }}</div>
                    <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.item_more_count') }}</div>
                    <div class="col-span-5 text-right text-danger">{{ formatQuantityValue((item as
                      SalesOrder).item_more_count ?? 0, 0) }}</div>
                    <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.item_unlinked_count') }}
                    </div>
                    <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{
                      formatQuantityValue((item as SalesOrder).item_unlinked_count ?? 0, 0) }}</div>
                  </div>
                </div>
              </div>

              <div class="space-y-2">
                <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                  {{ t('views.sales_order.fields.down_payment') }}
                </div>
                <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                  <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.amount_paid_down_payment') }}
                  </div>
                  <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as
                    SalesOrder).amount_paid_down_payment ?? 0) }}</div>
                  <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.amount_allocated_down_payment')
                    }}</div>
                  <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as
                    SalesOrder).amount_allocated_down_payment ?? 0) }}</div>
                  <div class="col-span-7 text-slate-500">{{ t('views.sales_order.fields.amount_refunded_down_payment')
                    }}</div>
                  <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">{{ formatCurrency((item as
                    SalesOrder).amount_refunded_down_payment ?? 0) }}</div>
                  <div class="col-span-7 text-primary font-medium">{{
                    t('views.sales_order.fields.amount_available_down_payment') }}</div>
                  <div class="col-span-5 text-right text-primary font-medium">{{ formatCurrency((item as
                    SalesOrder).amount_available_down_payment ?? 0) }}</div>
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
                  {{ t('views.sales_order.field_groups.items') }}
                </div>
                <div v-if="!(item as SalesOrder).items?.length" class="text-xs text-slate-500">
                  {{ t('components.data-list.data_not_found') }}
                </div>
                <div v-else class="space-y-3 rounded-md border border-slate-200/60 dark:border-darkmode-400 p-3">
                  <div v-for="soItem in (item as SalesOrder).items" :key="soItem.id"
                    class="border-b border-slate-200/60 pb-3 text-sm last:border-b-0 last:pb-0 dark:border-darkmode-400">
                    <div class="font-medium text-slate-700 dark:text-slate-200 break-words">
                      {{ soItem.product_unit?.product?.name ?? '-' }}
                    </div>
                    <div class="mt-1 text-xs text-slate-500 break-words">
                      {{ soItem.product_unit?.code ?? '-' }}{{ soItem.product_unit?.unit?.name ? `
                      (${soItem.product_unit.unit.name})` : '' }}
                    </div>
                    <div
                      class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-600 dark:text-slate-300">
                      <span>
                        {{ t('views.sales_order.fields.qty') }}
                        <span class="text-slate-700 dark:text-slate-200">{{ formatQuantityValue(soItem.qty ?? 0)
                          }}</span>
                      </span>
                      <span class="text-slate-400">|</span>
                      <span>
                        {{ t('views.sales_order.fields.unit_name') }}
                        <span class="text-slate-700 dark:text-slate-200">{{ soItem.product_unit?.unit?.name ?? '-'
                          }}</span>
                      </span>
                      <span class="text-slate-400">|</span>
                      <span>
                        {{ t('views.sales_order.fields.product_unit_price') }}
                        <span class="text-slate-700 dark:text-slate-200">{{ formatCurrency(soItem.product_unit_price ??
                          0) }}</span>
                      </span>
                      <span class="text-slate-400">|</span>
                      <span>
                        {{ t('views.sales_order.fields.subtotal_after_discount') }}
                        <span class="font-medium text-slate-700 dark:text-slate-200">{{
                          formatCurrency(soItem.subtotal_after_discount ?? 0) }}</span>
                      </span>
                    </div>
                    <div class="mt-2 grid grid-cols-12 gap-x-3 gap-y-1 text-xs">
                      <div class="col-span-6 text-slate-500">{{ t('views.sales_order.fields.qty_delivered_base') }}
                      </div>
                      <div class="col-span-6 text-right text-slate-700 dark:text-slate-200">{{
                        formatQuantityValue(soItem.qty_delivered_base ?? 0) }}</div>
                      <div class="col-span-6 text-slate-500">{{ t('views.sales_order.fields.qty_invoiced_base') }}
                      </div>
                      <div class="col-span-6 text-right text-slate-700 dark:text-slate-200">{{
                        formatQuantityValue(soItem.qty_invoiced_base ?? 0) }}</div>
                      <div class="col-span-6 text-slate-500">{{ t('views.sales_order.fields.qty_outstanding_base') }}
                      </div>
                      <div class="col-span-6 text-right text-warning">{{ formatQuantityValue(
                        soItem.qty_outstanding_base ?? 0) }}</div>
                      <div class="col-span-6 text-slate-500">{{ t('views.sales_order.fields.qty_excess_base') }}</div>
                      <div class="col-span-6 text-right text-danger">{{ formatQuantityValue(
                        soItem.qty_excess_base ?? 0) }}</div>
                    </div>
                    <div v-if="soItem.remarks?.trim()" class="mt-2 text-xs text-slate-500 break-words">
                      {{ soItem.remarks }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-span-12 lg:col-span-6 space-y-4">
                <div class="space-y-3">
                  <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                    {{ t('views.sales_order.fields.payments') }}
                  </div>
                  <div v-if="!(item as SalesOrder).payments?.length" class="text-xs text-slate-500">
                    {{ t('components.data-list.data_not_found') }}
                  </div>
                  <div v-else class="space-y-2">
                    <div v-for="payment in (item as SalesOrder).payments" :key="payment.id"
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
                    {{ t('views.sales_order.fields.refunded_payments') }}
                  </div>
                  <div v-if="!(item as SalesOrder).refunded_payments?.length" class="text-xs text-slate-500">
                    {{ t('components.data-list.data_not_found') }}
                  </div>
                  <div v-else class="space-y-2">
                    <div v-for="refundedPayment in (item as SalesOrder).refunded_payments"
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
