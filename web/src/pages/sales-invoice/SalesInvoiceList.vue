<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useI18n } from 'vue-i18n';
import { useRouter } from 'vue-router';
import { Dialog } from '@/components/Base/Headless';
import { FormInputDateTime, FormLabel, FormSelectSearch } from '@/components/Base/Form';
import { DataListFlex } from '@/components/DataList';
import Button from '@/components/Base/Button';
import Lucide from '@/components/Base/Lucide';
import CustomerService from '@/services/CustomerService';
import SalesInvoiceService from '@/services/SalesInvoiceService';
import { useSelectedUserLocationStore } from '@/stores/selected-user-location';
import { ErrorCode } from '@/types/enums/ErrorCode';
import { ViewMode } from '@/types/enums/ViewMode';
import { SalesInvoice } from '@/types/models/SalesInvoice';
import { DropDownOption } from '@/types/models/DropDownOption';
import { NotificationData } from '@/types/models/NotificationData';
import { Collection } from '@/types/resources/Collection';
import { DataListEmittedData } from '@/components/DataList/DataList.vue';
import { SalesInvoiceReadAnyPaginateRequest } from '@/types/services/sales-invoice/SalesInvoiceRequest';
import { ServiceResponse } from '@/types/services/ServiceResponse';
import type { AlertPlaceholderProps } from '@/components/AlertPlaceholder/AlertPlaceholder.vue';
import { formatCurrency, formatDate } from '@/utils/helper';

const { t } = useI18n();
const router = useRouter();
const salesInvoiceService = new SalesInvoiceService();
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
const selectedIsPaidOff = ref<string | null>(null);

const formatCurrencyRounded = (value: number | string, precision = 2) =>
  formatCurrency(Number(Number(value ?? 0).toFixed(precision)));

const formatQuantityValue = (value: number | string, precision = 4) =>
  new Intl.NumberFormat('id-ID', {
    minimumFractionDigits: 0,
    maximumFractionDigits: precision,
  }).format(Number(value ?? 0));

const getPaidOffBadgeClass = (isPaidOff: boolean | null | undefined) =>
  isPaidOff
    ? 'bg-success/15 text-success'
    : 'bg-warning/15 text-warning';

const salesInvoiceLists = ref<Collection<Array<SalesInvoice>> | null>({
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
const customerSearch = ref<string>('');
const customerOptions = computed(() =>
  (customerDDL.value ?? []).map((item) => ({
    value: item.code,
    label: item.name,
  })),
);

const isPaidOffOptions = computed(() => [
  { value: 'true', label: t('views.sales_invoice.filters.is_paid_off_yes') },
  { value: 'false', label: t('views.sales_invoice.filters.is_paid_off_no') },
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

  await loadCustomerDDL();
  await getSalesInvoices('', true, 1, 10);
});

const getSalesInvoices = async (search: string, refresh: boolean, page: number, perPage: number) => {
  emits('loading-state', true);
  searchText.value = search;

  const request: SalesInvoiceReadAnyPaginateRequest = {
    with_trashed: false,
    company_id: selectedUserLocation.value.company.id,
    branch_id: selectedUserLocation.value.branch.id,
    search,
    start_date: startDate.value,
    end_date: endDate.value,
    customer_id: selectedCustomerId.value,
    sales_order_id: null,
    is_posted: null,
    is_paid_off: selectedIsPaidOff.value === null ? null : selectedIsPaidOff.value === 'true',
    refresh,
    page,
    per_page: perPage,
  };

  const result = (await salesInvoiceService.readAnyPaginate(
    request,
  )) as ServiceResponse<Collection<Array<SalesInvoice>> | null>;

  if (result.success && result.data) {
    salesInvoiceLists.value = result.data;
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

const clearCustomerFilter = async () => {
  selectedCustomerId.value = null;
  await getSalesInvoices(searchText.value, true, 1, salesInvoiceLists.value?.meta.per_page ?? 10);
};

const clearIsPaidOffFilter = async () => {
  selectedIsPaidOff.value = null;
  await getSalesInvoices(searchText.value, true, 1, salesInvoiceLists.value?.meta.per_page ?? 10);
};

const handleDataListChange = async (emittedData: DataListEmittedData) => {
  await getSalesInvoices(
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
  if (!salesInvoiceLists.value?.data?.[index]) return;
  router.push({
    name: 'side-menu-sales-invoice-edit',
    params: { ulid: salesInvoiceLists.value.data[index].ulid },
  });
};

const deleteSelected = (index: number) => {
  if (!salesInvoiceLists.value?.data?.[index]) return;
  deleteUlid.value = salesInvoiceLists.value.data[index].ulid;
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

const getPaymentTypeLabel = (paymentType: string | null | undefined) => {
  switch (paymentType) {
    case 'cash':
      return t('views.sales_invoice.filters.payment_type_cash');
    case 'down_payment':
      return t('views.sales_invoice.filters.payment_type_down_payment');
    case 'return':
      return t('views.sales_invoice.filters.payment_type_return');
    default:
      return '-';
  }
};

const confirmDelete = async () => {
  emits('loading-state', true);
  const result = await salesInvoiceService.delete(deleteUlid.value);
  emits('loading-state', false);
  deleteModalShow.value = false;

  if (result.success) {
    showAlertPlaceholder('hidden', '', null);
    emits('update-profile');
    showNotification(t('views.sales_invoice.alert.delete.title'), t('views.sales_invoice.alert.delete.message'));
    await getSalesInvoices(searchText.value, true, salesInvoiceLists.value?.meta.current_page ?? 1, salesInvoiceLists.value?.meta.per_page ?? 10);
  } else {
    showAlertPlaceholder('danger', '', (result.errors as Record<string, Array<string>>) ?? null);
  }
};
</script>

<template>
  <div class="grid grid-cols-12 gap-6 mt-5">
    <div class="col-span-12">
      <div class="grid grid-cols-12 gap-4 mb-5">
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.sales_invoice.fields.start_date') }}</FormLabel>
          <FormInputDateTime v-model="startDate"
            @change="getSalesInvoices(searchText, true, 1, salesInvoiceLists?.meta.per_page ?? 10)" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.sales_invoice.fields.end_date') }}</FormLabel>
          <FormInputDateTime v-model="endDate"
            @change="getSalesInvoices(searchText, true, 1, salesInvoiceLists?.meta.per_page ?? 10)" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.sales_invoice.fields.customer_id') }}</FormLabel>
          <FormSelectSearch v-model="selectedCustomerId" v-model:search="customerSearch" :options="customerOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getSalesInvoices(searchText, true, 1, salesInvoiceLists?.meta.per_page ?? 10)"
            @search="loadCustomerDDL" @clear="clearCustomerFilter" />
        </div>
        <div class="col-span-12 md:col-span-6 lg:col-span-3">
          <FormLabel>{{ t('views.sales_invoice.filters.is_paid_off') }}</FormLabel>
          <FormSelectSearch v-model="selectedIsPaidOff" :options="isPaidOffOptions"
            :placeholder="t('components.dropdown.placeholder')"
            @change="getSalesInvoices(searchText, true, 1, salesInvoiceLists?.meta.per_page ?? 10)"
            @clear="clearIsPaidOffFilter" />
        </div>
      </div>

      <DataListFlex :data="salesInvoiceLists" :enable-search="true" :can-print="true" :can-export="true"
        :rows="salesInvoiceLists?.data ?? []" row-class="bg-white dark:bg-darkmode-600"
        :pagination="salesInvoiceLists ? salesInvoiceLists.meta : null" @dataListChanged="handleDataListChange">
        <template #row="{ item, index }">
          <div class="col-span-12 md:col-span-12 lg:col-span-4 self-start">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.sales_invoice.page_title') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-4 text-slate-500">{{ t('views.sales_invoice.fields.code') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ (item as SalesInvoice).code ?? '-' }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_invoice.fields.date') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as SalesInvoice).date ? formatDate((item as SalesInvoice).date, 'DD-MMM-YYYY HH:mm:ss') : '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_invoice.fields.customer_id') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as SalesInvoice).customer?.name ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_invoice.fields.sales_order_id') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as SalesInvoice).sales_order?.code ?? '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_invoice.fields.due_days') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200">{{ (item as SalesInvoice).due_days ?? 0 }}</div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_invoice.fields.tax_invoice_number') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as SalesInvoice).tax_invoice_number?.trim() || '-' }}
                </div>
                <div class="col-span-4 text-slate-500">{{ t('views.sales_invoice.fields.remarks') }}</div>
                <div class="col-span-8 text-slate-700 dark:text-slate-200 break-words">
                  {{ (item as SalesInvoice).remarks?.trim() || '-' }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-6 lg:col-span-4 self-start md:pr-3 lg:pr-4">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.sales_invoice.field_groups.summary') }}
              </div>
              <div class="grid grid-cols-12 items-center gap-x-3 gap-y-2 text-xs">
                <div class="col-span-7 text-slate-500">{{ t('views.sales_invoice.fields.item_total_before_global_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrency((item as SalesInvoice).item_total_before_global_discount ?? 0) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_invoice.fields.global_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrency((item as SalesInvoice).global_discount ?? 0) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_invoice.fields.item_total_after_global_discount') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrency((item as SalesInvoice).item_total_after_global_discount ?? 0) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_invoice.fields.vat_base') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrencyRounded((item as SalesInvoice).vat_base ?? 0) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_invoice.fields.vat') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrencyRounded((item as SalesInvoice).vat ?? 0) }}
                </div>
                <div class="col-span-7 text-slate-500">{{ t('views.sales_invoice.fields.rounding') }}</div>
                <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                  {{ formatCurrency((item as SalesInvoice).rounding ?? 0) }}
                </div>
                <div class="col-span-4 text-primary font-medium">{{ t('views.sales_invoice.fields.amount_payable') }}</div>
                <div class="col-span-8 text-right text-primary font-medium">
                  {{ formatCurrency((item as SalesInvoice).amount_payable ?? 0) }}
                </div>
              </div>
            </div>
          </div>

          <div class="col-span-12 md:col-span-5 lg:col-span-3 self-start md:pl-3 lg:pl-4">
            <div class="space-y-2">
              <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                {{ t('views.sales_invoice.field_groups.payments') }}
              </div>
              <div class="rounded-md border border-slate-200/60 dark:border-darkmode-400 px-3 py-3">
                <div class="flex items-center justify-between gap-2">
                  <span class="text-xs text-slate-500">{{ t('views.sales_invoice.fields.is_paid_off') }}</span>
                  <span class="inline-flex rounded-full px-2 py-1 text-[11px] font-medium"
                    :class="getPaidOffBadgeClass((item as SalesInvoice).is_paid_off)">
                    {{ (item as SalesInvoice).is_paid_off
                      ? t('views.sales_invoice.filters.is_paid_off_yes')
                      : t('views.sales_invoice.filters.is_paid_off_no') }}
                  </span>
                </div>
                <div class="mt-3 grid grid-cols-12 gap-x-3 gap-y-2 text-xs">
                  <div class="col-span-7 text-slate-500">{{ t('views.sales_invoice.fields.amount_paid_down_payment') }}</div>
                  <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                    {{ formatCurrency((item as SalesInvoice).amount_paid_down_payment ?? 0) }}
                  </div>
                  <div class="col-span-7 text-slate-500">{{ t('views.sales_invoice.fields.amount_paid_return') }}</div>
                  <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                    {{ formatCurrency((item as SalesInvoice).amount_paid_return ?? 0) }}
                  </div>
                  <div class="col-span-7 text-slate-500">{{ t('views.sales_invoice.fields.amount_paid_total') }}</div>
                  <div class="col-span-5 text-right text-slate-700 dark:text-slate-200">
                    {{ formatCurrency((item as SalesInvoice).amount_paid_total ?? 0) }}
                  </div>
                  <div class="col-span-7 text-primary font-medium">{{ t('views.sales_invoice.fields.amount_due') }}</div>
                  <div class="col-span-5 text-right text-primary font-medium">
                    {{ formatCurrency((item as SalesInvoice).amount_due ?? 0) }}
                  </div>
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
            <Button size="sm" variant="outline-secondary" class="flex items-center gap-1" @click="deleteSelected(index)">
              <Lucide icon="Trash2" class="w-4 h-4 text-danger" />
            </Button>
          </div>

          <div v-if="expandDetail === index"
            class="col-span-12 border-t border-slate-200 dark:border-darkmode-400 mt-2 pt-3">
            <div class="grid grid-cols-12 gap-4">
              <div class="col-span-12 lg:col-span-6 space-y-3">
                <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                  {{ t('views.sales_invoice.field_groups.items') }}
                </div>
                <div v-if="!(item as SalesInvoice).items?.length" class="text-xs text-slate-500">
                  {{ t('components.data-list.data_not_found') }}
                </div>
                <div v-else class="space-y-3 rounded-md border border-slate-200/60 dark:border-darkmode-400 p-3">
                  <div v-for="invoiceItem in (item as SalesInvoice).items" :key="invoiceItem.id"
                    class="border-b border-slate-200/60 pb-3 text-sm last:border-b-0 last:pb-0 dark:border-darkmode-400">
                    <div class="font-medium text-slate-700 dark:text-slate-200 break-words">
                      {{ invoiceItem.product_unit?.product?.name ?? '-' }}
                    </div>
                    <div class="mt-1 text-xs text-slate-500 break-words">
                      {{ invoiceItem.product_unit?.code ?? '-' }}
                    </div>
                    <div class="mt-2 flex flex-wrap items-center gap-x-2 gap-y-1 text-xs text-slate-600 dark:text-slate-300">
                      <span>
                        {{ t('views.sales_invoice.fields.qty') }}
                        <span class="text-slate-700 dark:text-slate-200">{{ formatQuantityValue(invoiceItem.qty ?? 0) }}</span>
                      </span>
                      <span class="text-slate-400">|</span>
                      <span>
                        {{ t('views.sales_invoice.fields.unit_name') }}
                        <span class="text-slate-700 dark:text-slate-200">{{ invoiceItem.product_unit?.unit?.name ?? '-' }}</span>
                      </span>
                      <span class="text-slate-400">|</span>
                      <span>
                        {{ t('views.sales_invoice.fields.product_unit_price') }}
                        <span class="text-slate-700 dark:text-slate-200">{{ formatCurrency(invoiceItem.product_unit_price ?? 0) }}</span>
                      </span>
                      <span class="text-slate-400">|</span>
                      <span>
                        {{ t('views.sales_invoice.fields.subtotal_after_discount') }}
                        <span class="font-medium text-slate-700 dark:text-slate-200">
                          {{ formatCurrency(invoiceItem.subtotal_after_discount ?? 0) }}
                        </span>
                      </span>
                    </div>
                    <div v-if="invoiceItem.remarks?.trim()" class="mt-2 text-xs text-slate-500 break-words">
                      {{ invoiceItem.remarks }}
                    </div>
                  </div>
                </div>
              </div>

              <div class="col-span-12 lg:col-span-6 space-y-3">
                <div class="text-primary text-xs font-semibold uppercase tracking-wide">
                  {{ t('views.sales_invoice.fields.payments') }}
                </div>
                <div v-if="!(item as SalesInvoice).payments?.length" class="text-xs text-slate-500">
                  {{ t('components.data-list.data_not_found') }}
                </div>
                <div v-else class="space-y-2">
                  <div v-for="payment in (item as SalesInvoice).payments" :key="payment.id"
                    class="rounded-md border border-slate-200/60 dark:border-darkmode-400 px-3 py-2 grid grid-cols-12 gap-3 text-xs">
                    <div class="col-span-3 text-slate-700 dark:text-slate-200">{{ payment.code }}</div>
                    <div class="col-span-3 text-slate-500">{{ getPaymentTypeLabel(payment.payment_type) }}</div>
                    <div class="col-span-3 text-slate-500 break-words">
                      {{ payment.cash_account?.name
                        ?? payment.sales_order_payment?.code
                        ?? payment.sales_return?.code
                        ?? '-' }}
                    </div>
                    <div class="col-span-3 text-right text-slate-700 dark:text-slate-200">
                      {{ formatCurrency(payment.amount ?? 0) }}
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
